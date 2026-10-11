<?php
namespace MyTheme;

use event\interfaces\IEvent;
use event\interfaces\IEventListener;
use URPC\api\events\BeforeRenderEvent;

final class BeforeRenderListener implements IEventListener
{
    public function handle(IEvent $event): array
    {
        if (!$event instanceof BeforeRenderEvent) { return []; }
        $document = $event->getDocument();
        // Find persisted editor overrides by each block's generated UID.
        // Apply them with their original UID; do not rehash modified blocks.
        // Traverse children as well when edits target nested blocks.
        $event->setDocument($document);
        return [];
    }
}
