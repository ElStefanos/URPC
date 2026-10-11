<?php
/*
 * Copyright (c) 2026 Stefan Crkvenjakov.
 * Part of the URPC project.
 * Github: https://github.com/ElStefanos/URPC
 * File: CustomController.php
 */

namespace MyTheme;

use URPC\api\contracts\IURPCDocument;
use URPC\api\URPCBlock;
use URPC\api\URPCBlockDescriptor;
use URPC\service\renderer\URPC;

final class CustomController extends URPC
{
    protected function prepareDocument(IURPCDocument $document): IURPCDocument
    {
        $document->removeSection('side_left');
        $notice = new URPCBlock(new URPCBlockDescriptor('p', sourceKey: 'MyTheme/notices'));
        $notice->setBlockContent('Welcome to our website.');
        $document->addBlock('content', $notice);
        return $document;
    }
}
// The integration package injects its resolved shared provider and context:
// $controller->setDocumentProvider($provider);
// $controller->setContext($context);
// AWT invokes index(), receives this View-compatible instance and calls render().
