<?php

namespace URPC\service\contracts\renderer;

use URPC\api\contracts\IURPCDocument;

interface IURPCRenderer
{
    public function setDocument(IURPCDocument $document): void;

    public function render(): string;
}