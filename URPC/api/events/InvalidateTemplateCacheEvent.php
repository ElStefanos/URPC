<?php
/*
 * Copyright (c) 2026 Stefan Crkvenjakov.
 * Part of the URPC project.
 * Github: https://github.com/ElStefanos/URPC
 * File: InvalidateTemplateCacheEvent.php
 */

namespace URPC\api\events;

use event\interfaces\IEvent;

final class InvalidateTemplateCacheEvent implements IEvent
{
    public const NAME = 'urpc.template_cache.invalidate';

    public function __construct(private readonly string $namespace)
    {
        if (trim($namespace) === '') {
            throw new \InvalidArgumentException('Template cache namespace must not be empty.');
        }
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function bundle(): array
    {
        return ['namespace' => $this->namespace];
    }
}
