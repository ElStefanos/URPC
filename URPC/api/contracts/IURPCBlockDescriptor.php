<?php
/*
 * Copyright (c) 2026 Stefan Crkvenjakov.
 * Part of the URPC project.
 * Github: https://github.com/ElStefanos/URPC
 * File: IURPCBlockDescriptor.php
 */

namespace URPC\api\contracts;

interface IURPCBlockDescriptor
{
    public function setSourceKey(string $sourceKey): void;
    public function getSourceKey(): string;

    public function setBlockUid(string $uid): void;
    public function setBlockName(string $name): void;
    public function setBlockProps(array $props): void;
    public function setBlockRenderer(string $renderer): void;

    public function getBlockUid(): string;
    public function getBlockName(): string;
    public function getBlockProps(): array;
    public function getBlockRenderer(): string;

}