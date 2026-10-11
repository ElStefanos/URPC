<?php
/*
 * Copyright (c) 2026 Stefan Crkvenjakov.
 * Part of the URPC project.
 * Github: https://github.com/ElStefanos/URPC
 * File: IURPCRenderer.php
 */

namespace URPC\service\contracts\renderer;

use URPC\api\contracts\IURPCDocument;

interface IURPCRenderer
{
    public function setDocument(IURPCDocument $document): void;

    public function render(): string;
}