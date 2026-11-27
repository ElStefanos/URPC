<?php

namespace URPC\service\loader;

use context\Context;
use URPC\api\URPCDocument;
use URPC\service\contracts\provider\ICacheableURPCDocumentProvider;

final class JsonDocumentProvider implements ICacheableURPCDocumentProvider
{
    /** @param array<string, JsonFileProvider> $sections */
    public function __construct(private readonly array $sections)
    {
        foreach ($sections as $name => $file) {
            if (!is_string($name) || !$file instanceof JsonFileProvider) {
                throw new \InvalidArgumentException('Expected named JSON file providers.');
            }
        }
    }

    public function getDocumentCacheRevision(Context $context): string
    {
        $sources = [];
        foreach ($this->sections as $name => $file) {
            $sources[$name] = [$file->getSourceKey(), hash('sha256', $file->getContent())];
        }
        return hash('sha256', serialize($sources));
    }

    public function createDocument(Context $context): URPCDocument
    {
        $document = new URPCDocument();
        $loader = new URPCLoader();
        foreach ($this->sections as $name => $file) {
            $document->addSection($name);
            foreach ($loader->load($file) as $block) {
                $document->addBlock($name, $block);
            }
        }
        return $document;
    }
}
