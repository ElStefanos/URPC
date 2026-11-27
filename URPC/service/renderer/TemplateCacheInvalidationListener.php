<?php
/*
 * Copyright (c) 2026 Stefan Crkvenjakov.
 * Part of the URPC project.
 * Github: https://github.com/ElStefanos/URPC
 * File: TemplateCacheInvalidationListener.php
 */

namespace URPC\service\renderer;

use event\interfaces\IEvent;
use event\interfaces\IEventListener;
use URPC\api\events\InvalidateTemplateCacheEvent;

final class TemplateCacheInvalidationListener implements IEventListener
{
    public function handle(IEvent $event): array
    {
        if ($event instanceof InvalidateTemplateCacheEvent) {
            TemplateCache::invalidate($event->getNamespace());
        }

        return [];
    }
}
