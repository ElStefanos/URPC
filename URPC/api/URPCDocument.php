<?php
/*
 * Copyright (c) 2026 Stefan Crkvenjakov.
 * Part of the URPC project.
 * Github: https://github.com/ElStefanos/URPC
 * File: URPCDocument.php
 */

namespace URPC\api;

use URPC\api\contracts\IURPCBlock;
use URPC\api\contracts\IURPCBlockDescriptor;
use URPC\api\contracts\IURPCDocument;

class URPCDocument implements IURPCDocument
{

    private array $sections = [];


    public function addSection(string $section): void
    {

        if (array_key_exists($section, $this->sections)) {
            return;
        }

        $this->sections[$section] = [];
    }

    public function getSections(): array
    {
        return $this->sections;
    }

    public function removeSection(string $section): void
    {
        unset($this->sections[$section]);
    }

    public function setSectionOrder(string $section, int $number): void
    {
        if (!array_key_exists($section, $this->sections)) {
            return;
        }

        $this->sections = $this->insertAtPosition(
            $this->sections,
            $section,
            $this->sections[$section],
            $number
        );
    }

    public function fromArray(array $data): void
    {
        $sections = [];
        foreach ($data as $section => $blocks) {
            if (!is_string($section) || !is_array($blocks)) {
                throw new \InvalidArgumentException('Expected named sections containing blocks.');
            }
            $sections[$section] = [];
            foreach ($blocks as $block) {
                if (!$block instanceof IURPCBlock) { throw new \InvalidArgumentException('Expected IURPCBlock.'); }
                $uid = $block->getBlockDescriptor()->getBlockUid();
                if ($uid === '' || isset($sections[$section][$uid])) { $sections[$section][] = $block; }
                else { $sections[$section][$uid] = $block; }
            }
        }
        $this->sections = $sections;
    }

    public function addBlock(string $section, IURPCBlock $block): void
    {
        $this->addSection($section);
        $uid = $block->getBlockDescriptor()->getBlockUid();
        if ($uid === '' || isset($this->sections[$section][$uid])) { $this->sections[$section][] = $block; }
        else { $this->sections[$section][$uid] = $block; }
    }

    public function getBlock(string $section, IURPCBlockDescriptor $descriptor): ?IURPCBlock
    {
        return $this->sections[$section][$descriptor->getBlockUid()] ?? null;
    }

    public function removeBlock(string $section, IURPCBlockDescriptor $block): void
    {
        unset($this->sections[$section][$block->getBlockUid()]);
    }

    public function getBlocks(string $section): array
    {
        return $this->sections[$section] ?? [];
    }

    public function replaceBlock(string $section, IURPCBlockDescriptor $old, IURPCBlock $new): void
    {
        if ($new->getBlockDescriptor()->getBlockUid() !== $old->getBlockUid()) {
            throw new \InvalidArgumentException('An override must retain the original block UID.');
        }
        if (!isset($this->sections[$section][$old->getBlockUid()])) {
            throw new \OutOfBoundsException('Block to replace does not exist.');
        }
        $this->sections[$section][$old->getBlockUid()] = $new;
    }

    public function insertBlock(string $section, IURPCBlock $block, int $position): void
    {
        $this->sections[$section] = $this->insertAtPosition(
            $this->getBlocks($section),
            $block->getBlockDescriptor()->getBlockUid(),
            $block,
            $position
        );
    }

    /**
     * Insert or move an entry at a zero-based position while preserving keys.
     */
    private function insertAtPosition(array $items, string $key, mixed $value, int $position): array
    {
        unset($items[$key]);
        $position = max(0, min($position, count($items)));

        return array_slice($items, 0, $position, true)
            + [$key => $value]
            + array_slice($items, $position, null, true);
    }
}
