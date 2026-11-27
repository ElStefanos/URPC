<?php
/*
 * Copyright (c) 2026 Stefan Crkvenjakov.
 * Part of the URPC project.
 * Github: https://github.com/ElStefanos/URPC
 * File: main.php
 */

namespace URPC;

use runtime\api\RuntimeAPI;
use URPC\api\events\InvalidateTemplateCacheEvent;
use URPC\service\renderer\TemplateCacheInvalidationListener;

/**  @noinspection  **/
final class URPCMain extends RuntimeAPI
{
    public function main(): void
    {
        $this->eventDispatcher->addListener(
            InvalidateTemplateCacheEvent::NAME,
            new TemplateCacheInvalidationListener()
        );
    }
}
