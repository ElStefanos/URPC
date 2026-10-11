<?php

namespace URPC\service\renderer;

use controller\Controller;
use URPC\service\contracts\provider\IURPCDocumentProvider;
use object\ObjectFactory;
use URPC\api\contracts\IURPCBlock;
use URPC\api\contracts\IURPCBlockRenderer;
use URPC\api\contracts\IURPCDocument;
use URPC\api\events\BeforeRenderEvent;
use URPC\service\BlockIdentity;
use URPC\service\contracts\renderer\IURPCRenderer;

class URPC extends Controller implements IURPCRenderer
{
    private ?IURPCDocument $document = null;
    private ?IURPCDocumentProvider $documentProvider = null;
    private ?IURPCDocument $renderedDocument = null;
    private string $sourceNamespace = '';
    private array $factories = [];
    private bool $rendering = false;
    private \SplObjectStorage $active;

    public function setDocument(IURPCDocument $document): void { $this->document = $document; $this->renderedDocument = null; }
    public function setDocumentProvider(IURPCDocumentProvider $provider): self
    {
        $this->documentProvider = $provider;
        $this->renderedDocument = null;
        return $this;
    }
    /** Resume using the configured provider after a direct document was supplied. */
    public function clearDocument(): self
    {
        $this->document = null;
        $this->renderedDocument = null;
        return $this;
    }
    /** Runs on a working copy before IDs/events. Override to customize the layout/content. */
    protected function prepareDocument(IURPCDocument $document): IURPCDocument
    {
        return $document;
    }
    public function getDocument(): ?IURPCDocument { return $this->renderedDocument ?? $this->document; }
    /** Fallback namespace for blocks created in PHP; file-loaded blocks retain their source. */
    public function setSourceNamespace(string $namespace): self
    {
        if (trim($namespace) === '') { throw new \InvalidArgumentException('Namespace must not be empty.'); }
        $this->sourceNamespace = $namespace;
        return $this;
    }
    /** Optional factory configuration for processors with constructor dependencies. */
    public function setRendererFactory(string $class, ObjectFactory $factory): self
    {
        $this->factories[ltrim($class, '\\')] = $factory;
        return $this;
    }
    public function index(array|string $params = []): self { return $this; }

    public function render(): string
    {
        global $eventDispatcher;
        if ($this->rendering) { throw new \LogicException('Recursive document rendering is not supported.'); }
        if ($this->document === null && $this->documentProvider === null) {
            throw new \LogicException('Set a URPC document or document provider before rendering.');
        }
        if (!$eventDispatcher instanceof \event\EventDispatcher) {
            throw new \LogicException('Global $eventDispatcher must be an AWT EventDispatcher.');
        }
        $this->rendering = true;
        $this->active = new \SplObjectStorage();
        try {
            // Work on a separate tree: repeated rendering must not rehash editor/dynamic changes.
            $source = $this->document;
            if ($source === null) {
                $context = $this->getContext();
                if ($context === null) {
                    throw new \LogicException('A document provider requires an AWT Context.');
                }
                $source = $this->documentProvider->createDocument($context);
            }
            $working = $this->prepareDocument($this->copyDocument($source));
            (new BlockIdentity())->assignDocument($working, $this->sourceNamespace);
            $this->renderedDocument = $working;
            $event = new BeforeRenderEvent($working, $this);
            $eventDispatcher->dispatch($event);
            $this->renderedDocument = $event->getDocument();
            $html = '';
            foreach ($this->renderedDocument->getSections() as $blocks) {
                foreach ($blocks as $block) { $html .= $this->renderBlock($block); }
            }
            return $html;
        } finally { $this->rendering = false; }
    }
    private function renderBlock(IURPCBlock $block, int $depth = 0): string
    {
        if ($depth > 128) { throw new \LogicException('Maximum rendered block depth exceeded.'); }
        if ($this->active->contains($block)) { throw new \LogicException('Cyclic rendered block tree.'); }
        $this->active->attach($block);
        try {
            $class = ltrim($block->getBlockDescriptor()->getBlockRenderer(), '\\');
            if ($class !== '') {
                if (!is_a($class, IURPCBlockRenderer::class, true)) {
                    throw new \InvalidArgumentException("Renderer {$class} must implement IURPCBlockRenderer.");
                }
                $factory = isset($this->factories[$class]) ? clone $this->factories[$class] : new ObjectFactory();
                $processor = $factory->setClassName($class)->setType(IURPCBlockRenderer::class)->create();
                if (!$processor instanceof IURPCBlockRenderer) { throw new \RuntimeException("Cannot create {$class}."); }
                $processor->process($block, $this);
            }
            $tag = $block->getBlockDescriptor()->getBlockName();
            if (!preg_match('/^[A-Za-z][A-Za-z0-9-]*$/D', $tag)) {
                throw new \InvalidArgumentException("Invalid HTML tag: {$tag}");
            }
            // Text-only content is not suitable for raw-text HTML elements.
            if (in_array(strtolower($tag), ['script', 'style'], true)
                && ($block->getBlockContent() !== '' || $block->getBlockChildren() !== [])) {
                throw new \InvalidArgumentException('script/style content is not supported by text blocks.');
            }
            $attributes = '';
            foreach ($block->getBlockAttributes() as $name => $value) {
                if (!is_string($name) || !preg_match('/^[A-Za-z_:][A-Za-z0-9_.:-]*$/D', $name)) {
                    throw new \InvalidArgumentException('Invalid HTML attribute name.');
                }
                if ($value === false || $value === null) { continue; }
                if ($value === true) { $attributes .= ' ' . $name; continue; }
                if (!is_scalar($value)) { throw new \InvalidArgumentException("Attribute {$name} must be scalar."); }
                $attributes .= ' ' . $name . '="' . $this->escape((string) $value) . '"';
            }
            $open = '<' . $tag . $attributes . '>';
            if (in_array(strtolower($tag), ['area','base','br','col','embed','hr','img','input','link','meta','param','source','track','wbr'], true)) {
                if ($block->getBlockContent() !== '' || $block->getBlockChildren() !== []) {
                    throw new \InvalidArgumentException("Void element {$tag} cannot contain content or children.");
                }
                return $open;
            }
            $content = $this->escape($block->getBlockContent());
            foreach ($block->getBlockChildren() as $child) { $content .= $this->renderBlock($child, $depth + 1); }
            return $open . $content . '</' . $tag . '>';
        } finally { $this->active->detach($block); }
    }
    private function escape(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
    }
    private function copyDocument(IURPCDocument $document): IURPCDocument
    {
        $copy = clone $document;
        $sections = [];
        $active = new \SplObjectStorage();
        foreach ($document->getSections() as $name => $blocks) {
            $sections[$name] = array_map(fn ($block) => $this->copyBlock($block, $active), $blocks);
        }
        $copy->fromArray($sections);
        return $copy;
    }
    private function copyBlock(IURPCBlock $block, \SplObjectStorage $active): IURPCBlock
    {
        if (count($active) > 128) { throw new \LogicException('Maximum source block depth exceeded.'); }
        if ($active->contains($block)) { throw new \LogicException('Cyclic source block tree.'); }
        $active->attach($block);
        try {
            $copy = clone $block;
            $copy->setBlockDescriptor(clone $block->getBlockDescriptor());
            $copy->setBlockChildren(array_map(fn ($child) => $this->copyBlock($child, $active), $block->getBlockChildren()));
            return $copy;
        } finally { $active->detach($block); }
    }
}
