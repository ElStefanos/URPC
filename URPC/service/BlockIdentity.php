<?php
/*
 * Copyright (c) 2026 Stefan Crkvenjakov.
 * Part of the URPC project.
 * Github: https://github.com/ElStefanos/URPC
 * File: BlockIdentity.php
 */

namespace URPC\service;

use URPC\api\contracts\IURPCBlock;
use URPC\api\contracts\IURPCDocument;

final class BlockIdentity
{
    private array $counts = [];
    private \SplObjectStorage $active;
    public function __construct() { $this->active = new \SplObjectStorage(); }

    /** Rekey every section after assigning IDs, before the event is dispatched. */
    public function assignDocument(IURPCDocument $document, string $namespace): void
    {
        $this->counts = [];
        $sections = [];
        foreach ($document->getSections() as $name => $blocks) {
            $sections[$name] = $this->assignBlocks(array_values($blocks), $namespace, false);
        }
        $document->fromArray($sections);
    }
    public function assignBlocks(array $blocks, string $namespace, bool $reset = true): array
    {
        if ($reset) { $this->counts = []; }
        $result = [];
        foreach ($blocks as $block) {
            if (!$block instanceof IURPCBlock) {
                throw new \InvalidArgumentException('Expected IURPCBlock.');
            }
            $data = $this->structure($block);
            $source = $block->getBlockDescriptor()->getSourceKey() ?: $namespace;
            if (trim($source) === '') {
                throw new \InvalidArgumentException('Block source namespace is required.');
            }
            $base = json_encode([$source, $data], JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION);
            $counter = $this->counts[$base] ?? 0;
            $this->counts[$base] = $counter + 1;
            $id = md5(json_encode([$source, $data, $counter], JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION));
            $block->getBlockDescriptor()->setSourceKey($source);
            $block->getBlockDescriptor()->setBlockUid($id);
            $children = $this->assignBlocks(array_values($block->getBlockChildren()), $source, false);
            $block->setBlockChildren($children);
            if (isset($result[$id])) { throw new \RuntimeException('Block UID collision.'); }
            $result[$id] = $block;
        }
        return $result;
    }
    private function structure(IURPCBlock $block): array
    {
        if (count($this->active) > 128) { throw new \LogicException('Maximum source block depth exceeded.'); }
        if ($this->active->contains($block)) { throw new \LogicException('Cyclic block tree.'); }
        $this->active->attach($block);
        try {
            $d = $block->getBlockDescriptor();
            return [$d->getBlockName(), $d->getBlockRenderer(), $this->canonical($d->getBlockProps()),
                $this->canonical($block->getBlockAttributes()), $block->getBlockContent(),
                array_map(fn ($child) => $this->structure($child), array_values($block->getBlockChildren()))];
        } finally { $this->active->detach($block); }
    }
    private function canonical(mixed $value): mixed
    {
        if (is_array($value)) {
            if (!array_is_list($value)) { ksort($value, SORT_STRING); }
            foreach ($value as $key => $item) { $value[$key] = $this->canonical($item); }
        }
        return $value;
    }
}
