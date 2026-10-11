<?php
namespace MyTheme;

use context\Context;
use URPC\api\contracts\IURPCDocument;
use URPC\api\URPCDocument;
use URPC\service\contracts\provider\IURPCDocumentProvider;
use URPC\service\loader\JsonFileProvider;
use URPC\service\loader\URPCLoader;

final class DocumentProvider implements IURPCDocumentProvider
{
    public function __construct(private string $themeDirectory) {}

    public function createDocument(Context $context): IURPCDocument
    {
        $document = new URPCDocument();
        $loader = new URPCLoader();
        foreach (['header' => 'header.json'] as $section => $file) {
            $document->addSection($section);
            $provider = new JsonFileProvider(
                $this->themeDirectory . '/' . $file,
                'MyTheme/' . $file
            );
            foreach ($loader->load($provider) as $block) {
                $document->addBlock($section, $block);
            }
        }
        return $document;
    }
}
