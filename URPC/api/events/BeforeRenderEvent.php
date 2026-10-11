<?php

namespace URPC\api\events;

use event\interfaces\IEvent;
use URPC\api\contracts\IURPCDocument;
use URPC\service\renderer\URPC;

final class BeforeRenderEvent implements IEvent
{
    public const NAME = 'urpc.before_render';
    public function __construct(private IURPCDocument $document, private URPC $urpc) {}
    public function getName(): string { return self::NAME; }
    public function getDocument(): IURPCDocument { return $this->document; }
    public function setDocument(IURPCDocument $document): void { $this->document = $document; }
    public function getURPC(): URPC { return $this->urpc; }
    public function bundle(): array { return ['document' => $this->document, 'urpc' => $this->urpc]; }
}
