<?php
/*
 * Copyright (c) 2026 Stefan Crkvenjakov.
 * Part of the URPC project.
 * Github: https://github.com/ElStefanos/URPC
 * File: JsonFileProvider.php
 */

namespace URPC\service\loader;

use URPC\service\contracts\loader\IURPCLoaderProvider;

final class JsonFileProvider implements IURPCLoaderProvider
{
    public function __construct(private string $filePath, private string $sourceKey)
    {
        if (trim($sourceKey) === '') {
            throw new \InvalidArgumentException('Provide a stable package/source namespace.');
        }
    }
    public function getSourceKey(): string { return $this->sourceKey; }
    public function getContent(): string
    {
        if (!is_file($this->filePath) || !is_readable($this->filePath)) {
            throw new \RuntimeException("JSON file is not readable: {$this->filePath}");
        }
        $content = file_get_contents($this->filePath);
        if ($content === false) {
            throw new \RuntimeException("Could not read JSON file: {$this->filePath}");
        }
        return $content;
    }
}
