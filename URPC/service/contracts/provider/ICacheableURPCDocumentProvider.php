<?php

namespace URPC\service\contracts\provider;

use context\Context;

interface ICacheableURPCDocumentProvider extends IURPCDocumentProvider
{
    /**
     * Return a revision for the shared static tree, or null to build it for this request.
     * The revision must change when any input to createDocument() changes its output.
     * Keep request-specific values in before-render listeners or block processors.
     */
    public function getDocumentCacheRevision(Context $context): ?string;
}
