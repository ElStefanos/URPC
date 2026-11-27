<?php
/*
 * Copyright (c) 2026 Stefan Crkvenjakov.
 * Part of the URPC project.
 * Github: https://github.com/ElStefanos/URPC
 * File: TemplateCache.php
 */

namespace URPC\service\renderer;

use URPC\api\contracts\IURPCBlock;
use vfs\cache\Cache;

final class TemplateCache
{
    private const POOL = 'urpc-templates';
    private const FORMAT_VERSION = 1;

    public static function invalidate(string $namespace): void
    {
        if (!defined('DATA') || !class_exists(Cache::class)) {
            return;
        }

        if (!Cache::pool(self::POOL)->deleteCache($namespace, missingIsSuccess: true)) {
            throw new \RuntimeException("Cannot invalidate URPC template cache for {$namespace}.");
        }
    }

    public static function remember(string $namespace, array $sections, callable $generate): string
    {
        if (!defined('DATA') || !class_exists(Cache::class)) {
            return $generate();
        }

        $template = [];
        foreach ($sections as $blocks) {
            foreach ($blocks as $block) {
                $input = self::staticInput($block);
                if ($input === null) {
                    return $generate();
                }
                $template[] = $input;
            }
        }
        $signature = hash('sha256', serialize([self::FORMAT_VERSION, $template]));

        try {
            $entry = @Cache::get(self::POOL, $namespace);
        } catch (\RuntimeException | \ErrorException) {
            return $generate();
        }
        if (is_array($entry) && ($entry['signature'] ?? null) === $signature && is_string($entry['template'] ?? null)) {
            return $entry['template'];
        }

        $markup = $generate();
        try {
            @Cache::set(self::POOL, $namespace, ['signature' => $signature, 'template' => $markup]);
        } catch (\RuntimeException | \ErrorException) {
            // Rendering can proceed if the transient storage is unavailable.
        }
        return $markup;
    }

    /** Capture the ordered inputs that affect markup, without executing processors. */
    private static function staticInput(IURPCBlock $block, int $depth = 0): ?array
    {
        if ($depth > 128) {
            return null;
        }

        $descriptor = $block->getBlockDescriptor();
        if (ltrim($descriptor->getBlockRenderer(), '\\') !== '') {
            return null;
        }

        $attributes = [];
        foreach ($block->getBlockAttributes() as $name => $value) {
            if ($value !== null && !is_scalar($value)) {
                return null;
            }
            $attributes[$name] = is_int($value) || is_float($value) ? (string) $value : $value;
        }

        $children = [];
        foreach ($block->getBlockChildren() as $child) {
            $input = self::staticInput($child, $depth + 1);
            if ($input === null) {
                return null;
            }
            $children[] = $input;
        }

        return [$descriptor->getBlockName(), $attributes, $block->getBlockContent(), $children];
    }
}
