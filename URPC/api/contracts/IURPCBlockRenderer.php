<?php
/*
 * Copyright (c) 2026 Stefan Crkvenjakov.
 * Part of the URPC project.
 * Github: https://github.com/ElStefanos/URPC
 * File: IURPCBlockRenderer.php
 */

namespace URPC\api\contracts;

use URPC\service\renderer\URPC;

interface IURPCBlockRenderer
{
    /** Mutate structure only; content is text, not HTML. Context: $urpc->getContext(). */
    public function process(IURPCBlock $block, URPC $urpc): void;
}
