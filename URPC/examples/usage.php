<?php
/*
 * Copyright (c) 2026 Stefan Crkvenjakov.
 * Part of the URPC project.
 * Github: https://github.com/ElStefanos/URPC
 * File: usage.php
 */

// Run inside AWT; make the example MyTheme classes autoloadable first.
use URPC\api\URPCDocument;
use URPC\api\events\BeforeRenderEvent;
use URPC\service\loader\JsonFileProvider;
use URPC\service\loader\URPCLoader;
use URPC\service\renderer\URPC;

$provider = new JsonFileProvider(__DIR__ . '/header.json', 'MyTheme/sections/header');
$document = new URPCDocument();
foreach ((new URPCLoader())->load($provider) as $block) {
    $document->addBlock('header', $block);
}

$eventDispatcher->addListener(BeforeRenderEvent::NAME, new \MyTheme\BeforeRenderListener());
$urpc = new URPC();
$urpc->setDocument($document);
// $urpc->setContext($context); // Forward the calling controller's context.
echo $urpc->render();
// A controller action can return $urpc directly as a View instance.
