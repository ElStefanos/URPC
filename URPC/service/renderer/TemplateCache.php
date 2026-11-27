<?php
/*
 * Copyright (c) 2026 Stefan Crkvenjakov.
 * Part of the URPC project.
 * Github: https://github.com/ElStefanos/URPC
 * File: TemplateCache.php
 */

declare(strict_types=1);

namespace URPC\service\renderer;

use URPC\api\contracts\IURPCBlock;
use URPC\api\contracts\IURPCDocument;
use URPC\api\URPCBlock;
use URPC\api\URPCBlockDescriptor;
use URPC\api\URPCDocument;
use vfs\cache\Cache;

final class TemplateCache
{
    private const POOL = 'urpc-templates';
    private const FORMAT_VERSION = 3;

    public static function isAvailable(): bool
    {
        return defined('DATA') && class_exists(Cache::class);
    }

    public static function invalidate(string $namespace): void
    {
        if (self::isAvailable() && !Cache::pool(self::POOL)->deleteCache($namespace, missingIsSuccess: true)) {
            throw new \RuntimeException("Cannot invalidate URPC template cache for {$namespace}.");
        }
    }

    public static function remember(string $namespace, array $revision, callable $build): IURPCDocument
    {
        $signature = hash('sha256', serialize([self::FORMAT_VERSION, $revision]));
        try {
            $entry = @Cache::get(self::POOL, $namespace);
        } catch (\RuntimeException | \ErrorException) {
            return $build();
        }

        if (is_array($entry) && ($entry['signature'] ?? null) === $signature && is_array($entry['sections'] ?? null)) {
            try {
                return self::hydrate($entry['sections']);
            } catch (\InvalidArgumentException | \UnexpectedValueException | \TypeError) {
                // Rebuild an obsolete or malformed tree.
            }
        }

        $document = $build();
        $sections = self::snapshot($document);
        if ($sections !== null) {
            try {
                @Cache::set(self::POOL, $namespace, ['signature' => $signature, 'sections' => $sections]);
            } catch (\RuntimeException | \ErrorException) {
                // The working document can still be rendered.
            }
        }
        return $document;
    }

    private static function snapshot(IURPCDocument $document): ?array
    {
        if ($document::class !== URPCDocument::class || get_object_vars($document) !== []) {
            return null;
        }

        $sections = [];
        foreach ($document->getSections() as $name => $blocks) {
            $sections[$name] = [];
            foreach ($blocks as $block) {
                $data = self::exportBlock($block);
                if ($data === null) {
                    return null;
                }
                $sections[$name][] = $data;
            }
        }
        return $sections;
    }

    private static function exportBlock(IURPCBlock $block): ?array
    {
        $descriptor = $block->getBlockDescriptor();
        if ($block::class !== URPCBlock::class || $descriptor::class !== URPCBlockDescriptor::class
            || get_object_vars($block) !== [] || get_object_vars($descriptor) !== []) {
            return null;
        }
        $props = $descriptor->getBlockProps();
        $attributes = $block->getBlockAttributes();
        if (!self::isPlainData($props) || !self::isPlainData($attributes)) {
            return null;
        }

        $children = [];
        foreach ($block->getBlockChildren() as $child) {
            $data = self::exportBlock($child);
            if ($data === null) {
                return null;
            }
            $children[] = $data;
        }
        return [
            $descriptor->getBlockUid(),
            $descriptor->getBlockName(),
            $props,
            $descriptor->getBlockRenderer(),
            $descriptor->getSourceKey(),
            $attributes,
            $block->getBlockContent(),
            $children,
        ];
    }

    private static function isPlainData(array $data, int $depth = 0): bool
    {
        if ($depth > 128) {
            return false;
        }
        foreach ($data as $value) {
            if (is_array($value)) {
                if (!self::isPlainData($value, $depth + 1)) {
                    return false;
                }
            } elseif ($value !== null && !is_scalar($value)) {
                return false;
            }
        }
        return true;
    }

    private static function hydrate(array $sections): URPCDocument
    {
        $document = new URPCDocument();
        $document->fromArray(array_map(
            static fn (array $blocks): array => array_map(self::hydrateBlock(...), $blocks),
            $sections
        ));
        return $document;
    }

    private static function hydrateBlock(array $data): URPCBlock
    {
        if (count($data) !== 8 || !array_is_list($data)) {
            throw new \UnexpectedValueException('Invalid cached URPC block.');
        }
        [$uid, $name, $props, $renderer, $source, $attributes, $content, $children] = $data;
        if (!is_array($children)) {
            throw new \UnexpectedValueException('Invalid children in a cached URPC block.');
        }

        $descriptor = new URPCBlockDescriptor($name, $props, $renderer, $source);
        $descriptor->setBlockUid($uid);
        $block = new URPCBlock($descriptor);
        $block->setBlockAttributes($attributes);
        $block->setBlockContent($content);
        foreach ($children as $child) {
            $block->addBlockChild(self::hydrateBlock($child));
        }
        return $block;
    }
}
