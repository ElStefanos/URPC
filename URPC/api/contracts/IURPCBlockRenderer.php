<?php

namespace URPC\api\contracts;

use URPC\service\renderer\URPC;

interface IURPCBlockRenderer
{
    /** Mutate structure only; content is text, not HTML. Context: $urpc->getContext(). */
    public function process(IURPCBlock $block, URPC $urpc): void;
}
