<?php
/*
 * Copyright (c) 2026 Stefan Crkvenjakov.
 * Part of the URPC project.
 * Github: https://github.com/ElStefanos/URPC
 * File: IURPCDocument.php
 */

namespace URPC\api\contracts;

interface IURPCDocument
{
    public function addSection(string $section): void;
    public function getSections() : array;
    public function removeSection(string $section): void;
    public function setSectionOrder(string $section, int $number): void;
    public function fromArray(array $data) : void;

    public function addBlock(string $section, IURPCBlock $block): void;
    public function getBlock(string $section, IURPCBlockDescriptor $descriptor) : ?IURPCBlock;
    public function removeBlock(string $section, IURPCBlockDescriptor $block): void;
    public function getBlocks(string $section) : array;
    public function replaceBlock(string $section, IURPCBlockDescriptor $old, IURPCBlock $new): void;
    public function insertBlock(string $section, IURPCBlock $block, int $position): void;
}