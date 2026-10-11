<?php

namespace URPC\service\contracts\provider;

use context\Context;
use URPC\api\contracts\IURPCDocument;

interface IURPCDocumentProvider
{
    /** Create a fresh document for this request; do not return shared mutable state. */
    public function createDocument(Context $context): IURPCDocument;
}
