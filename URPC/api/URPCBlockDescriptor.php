<?php
/*
 * Copyright (c) 2026 Stefan Crkvenjakov.
 * Part of the URPC project.
 * Github: https://github.com/ElStefanos/URPC
 * File: URPCBlockDescriptor.php
 */

namespace URPC\api;

use URPC\api\contracts\IURPCBlockDescriptor;

class URPCBlockDescriptor implements IURPCBlockDescriptor
{

    private string $uid = '';
    private string $name = '';
    private array $props = [];
    private string $renderer = '';
    private string $sourceKey = '';

    public function __construct(string $name = '', array $props = [], string $renderer = '', string $sourceKey = '')
    {
        $this->name = $name;
        $this->props = $props;
        $this->renderer = $renderer;
        $this->sourceKey = $sourceKey;
    }

    public function setSourceKey(string $sourceKey): void { $this->sourceKey = $sourceKey; }
    public function getSourceKey(): string { return $this->sourceKey; }

    public function setBlockUid(string $uid): void
    {
        $this->uid = $uid;
    }

    public function setBlockName(string $name): void
    {
        $this->name = $name;
    }

    public function setBlockProps(array $props): void
    {
        $this->props = $props;
    }

    public function setBlockRenderer(string $renderer): void
    {
        $this->renderer = $renderer;
    }

    public function getBlockUid(): string
    {
        return $this->uid;
    }

    public function getBlockName(): string
    {
        return $this->name;
    }

    public function getBlockProps(): array
    {
        return $this->props;
    }

    public function getBlockRenderer(): string
    {
        return $this->renderer;
    }
}