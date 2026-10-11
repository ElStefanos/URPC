<?php
/*
 * Copyright (c) 2026 Stefan Crkvenjakov.
 * Part of the URPC project.
 * Github: https://github.com/ElStefanos/URPC
 * File: IURPCDocumentProvider.php
 */

namespace URPC\service\contracts\provider;

use context\Context;
use URPC\api\contracts\IURPCDocument;

interface IURPCDocumentProvider
{
    /** Create a fresh document for this request; do not return shared mutable state. */
    public function createDocument(Context $context): IURPCDocument;
}
