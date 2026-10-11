<?php
/*
 * Copyright (c) 2026 Stefan Crkvenjakov.
 * Part of the URPC project.
 * Github: https://github.com/ElStefanos/URPC
 * File: URPCLoader.php
 */

namespace URPC\service\loader;

use InvalidArgumentException;
use URPC\api\URPCBlock;
use URPC\api\URPCBlockDescriptor;
use URPC\api\contracts\IURPCBlock;
use URPC\service\contracts\loader\IURPCLoader;
use URPC\service\contracts\loader\IURPCLoaderProvider;

class URPCLoader implements IURPCLoader
{
    /**
     * Input: {"blocks": [{"descriptor": {"name": "h1", "props": {},
     * "renderer": ""}, "attributes": {}, "content": "Hello", "children": []}]}.
     * Loads source blocks only. Editor overrides must retain their original UID.
     * @return list<IURPCBlock>
     */
    public function load(IURPCLoaderProvider $provider): array
    {
        $source = $provider->getSourceKey();
        if (trim($source) === '') {
            throw new InvalidArgumentException('Source key must not be empty.');
        }
        $data = json_decode($provider->getContent(), false, 512, JSON_THROW_ON_ERROR);
        if (!$data instanceof \stdClass || !isset($data->blocks) || !is_array($data->blocks)) {
            throw new InvalidArgumentException('Expected a JSON object with a blocks array.');
        }
        $blocks = [];
        foreach ($data->blocks as $index => $node) {
            $blocks[] = $this->loadBlock($node, $source, 'blocks/' . $index);
        }
        return array_values((new \URPC\service\BlockIdentity())->assignBlocks($blocks, $source));
    }

    private function loadBlock(mixed $node, string $source, string $path): IURPCBlock
    {
        if (!$node instanceof \stdClass || !isset($node->descriptor)
            || !$node->descriptor instanceof \stdClass) {
            throw new InvalidArgumentException("Missing descriptor at {$path}.");
        }
        $meta = $node->descriptor;
        if (!isset($meta->name) || !is_string($meta->name) || trim($meta->name) === '') {
            throw new InvalidArgumentException("Missing block name at {$path}.");
        }
        $props = $meta->props ?? new \stdClass();
        $attributes = $node->attributes ?? new \stdClass();
        $content = $node->content ?? '';
        $children = $node->children ?? [];
        $renderer = $meta->renderer ?? '';
        if (!$props instanceof \stdClass || !$attributes instanceof \stdClass
            || !is_string($content) || !is_string($renderer) || !is_array($children)) {
            throw new InvalidArgumentException("Invalid block fields at {$path}.");
        }

        $block = new URPCBlock();
        $block->setBlockAttributes($this->toArray($attributes));
        $block->setBlockContent($content);
        $block->setBlockChildren([]);
        $loadedChildren = [];
        foreach ($children as $index => $child) {
            $loadedChildren[] = $this->loadBlock($child, $source, $path . '/children/' . $index);
        }
        $descriptor = new URPCBlockDescriptor();
        $descriptor->setBlockUid($path); // Temporary key, replaced after the tree is loaded.
        $descriptor->setSourceKey($source);
        $descriptor->setBlockName($meta->name);
        $descriptor->setBlockProps($this->toArray($props));
        $descriptor->setBlockRenderer($renderer);
        $block->setBlockDescriptor($descriptor);
        foreach ($loadedChildren as $child) {
            $block->addBlockChild($child);
        }
        return $block;
    }

    private function toArray(\stdClass $value): array
    {
        return json_decode(json_encode($value, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
    }
}
