<?php

namespace URPC\api\contracts;

interface IURPCBlock
{
    public function setBlockDescriptor(IURPCBlockDescriptor $descriptor): void;
    public function getBlockDescriptor(): IURPCBlockDescriptor;

    public function setBlockAttributes(array $attributes): void;
    public function getBlockAttributes(): array;

    public function setBlockContent(string $content): void;
    public function getBlockContent(): string;

    public function setBlockChildren(array $children): void;
    public function getBlockChildren(): array;

    public function addBlockChild(IURPCBlock $child): void;
    public function removeBlockChild(IURPCBlockDescriptor $child): void;
}