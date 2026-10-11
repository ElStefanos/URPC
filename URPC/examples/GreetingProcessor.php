<?php
namespace MyTheme;

use URPC\api\contracts\IURPCBlock;
use URPC\api\contracts\IURPCBlockRenderer;
use URPC\service\renderer\URPC;

final class GreetingProcessor implements IURPCBlockRenderer
{
    public function process(IURPCBlock $block, URPC $urpc): void
    {
        $props = $block->getBlockDescriptor()->getBlockProps();
        $block->setBlockContent((string) ($props['greeting'] ?? 'Welcome'));
        // Request/package context is available through $urpc->getContext().
    }
}
