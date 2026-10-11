<?php
/*
 * Copyright (c) 2026 Stefan Crkvenjakov.
 * Part of the URPC project.
 * Github: https://github.com/ElStefanos/URPC
 * File: URPCBlock.php
 */

namespace URPC\api;

use URPC\api\contracts\IURPCBlock;
use URPC\api\contracts\IURPCBlockDescriptor;

class URPCBlock implements IURPCBlock
{

    private IURPCBlockDescriptor $descriptor;
    private array $attributes = [];
    private string $content = '';
    private array $children = [];

    public function __construct(?IURPCBlockDescriptor $descriptor = null)
    {
        $this->descriptor = $descriptor ?? new URPCBlockDescriptor();
    }

    public function setBlockDescriptor(IURPCBlockDescriptor $descriptor): void
    {
        $this->descriptor = $descriptor;
    }

    public function getBlockDescriptor(): IURPCBlockDescriptor
    {
        return $this->descriptor;
    }

    public function setBlockAttributes(array $attributes): void
    {
        $this->attributes = $attributes;
    }

    public function getBlockAttributes(): array
    {
        return $this->attributes;
    }

    public function setBlockContent(string $content): void
    {
        $this->content = $content;
    }

    public function getBlockContent(): string
    {
        return $this->content;
    }

    public function setBlockChildren(array $children): void
    {
        foreach ($children as $child) {
            if (!$child instanceof IURPCBlock) {
                throw new \InvalidArgumentException('Children must implement IURPCBlock.');
            }
        }
        $this->children = [];
        foreach ($children as $child) { $this->addBlockChild($child); }
    }

    public function getBlockChildren(): array
    {
        return $this->children;
    }

    public function addBlockChild(IURPCBlock $child): void
    {
        $uid = $child->getBlockDescriptor()->getBlockUid();
        if ($uid === '' || isset($this->children[$uid])) { $this->children[] = $child; }
        else { $this->children[$uid] = $child; }
    }

    public function removeBlockChild(IURPCBlockDescriptor $child): void
    {
        unset($this->children[$child->getBlockUid()]);
    }
}
