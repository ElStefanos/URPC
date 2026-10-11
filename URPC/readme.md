# URPC — Universal Rendering Pipeline Controller

URPC is an AWT library for composing structured blocks into HTML. Other packages select content and sections. URPC does not infer routes, sections or page content from filenames.

## Quick start

Requires PHP 8.1+ and AWT 27 with its package autoloader and global `$eventDispatcher` initialized.

```php
use URPC\api\URPCDocument;
use URPC\service\loader\JsonFileProvider;
use URPC\service\loader\URPCLoader;
use URPC\service\renderer\URPC;

$provider = new JsonFileProvider(__DIR__ . '/header.json', 'MyTheme/sections/header');
$document = new URPCDocument();
foreach ((new URPCLoader())->load($provider) as $block) {
    $document->addBlock('header', $block);
}
$urpc = new URPC();
$urpc->setDocument($document);
if (($context = $this->getContext()) !== null) { $urpc->setContext($context); }
return $urpc; // Controller action returns a View-compatible object.
```

Outside a controller, call `$urpc->render()` to obtain HTML. A standalone `new URPC()` has no context until `setContext()` is called. For manually composed blocks, call `setSourceNamespace('MyTheme/page')` or set each descriptor's source key. File-loaded blocks retain the provider's explicit source key.

## JSON format

```json
{
  "blocks": [
    {
      "descriptor": {
        "name": "div",
        "renderer": "MyTheme\\GreetingProcessor",
        "props": {"greeting": "Hello"}
      },
      "attributes": {"class": "greeting"},
      "content": "Fallback text",
      "children": []
    }
  ]
}
```

Only `descriptor.name` is mandatory. `props` and `attributes` are JSON objects, not arrays. Children are ordered blocks. The name is an HTML element, not a block type registry. Props are processor metadata and never become HTML attributes. Renderer is an autoloadable fully qualified class name; an empty string means ordinary rendering.

The file provider takes the physical path and a stable logical namespace separately. Deploying the same file to a different directory therefore does not change its IDs. Missing/unreadable files and malformed JSON throw exceptions.

## Sections and rendering

Sections are ordered groups, not implicit HTML wrappers. Other packages add blocks to sections and choose section order. URPC concatenates every section in document order, preserving block/child order. Layout containers belong in the block tree. URPC adds neither a doctype nor html/head/body unless the calling package supplies those elements.

Content is always plain text, emitted before children. Text and attribute values are escaped; boolean true attributes are emitted without a value, false/null attributes are omitted. Other attribute values must be scalar. Void elements such as img/br/input cannot contain content or children. Nonempty script/style content is rejected because these elements cannot represent escaped plain text normally; an empty external script element can use a src attribute. Escaping is not an HTML sanitizer: trusted packages still select element names, attribute names and URL values.

## Identity and overrides

Before dispatch, URPC assigns every source block an MD5 ID from its complete normalized structure, explicit source namespace and occurrence counter. IDs and generated HTML are excluded from hashing. Object-property ordering and whitespace outside JSON strings do not affect IDs. Child order, text, attributes, processor class and props are significant. Complete structure includes descendants, so changing a child changes ancestor IDs.

The counter distinguishes identical structures within the same namespace. Adding a different structure does not shift their IDs. Adding/reordering identical structures can change which occurrence an editor override targets; identical occurrences have no persistent identity beyond their order. MD5 is used for fast non-security identification, not authorization or integrity validation.

The final document is the authoritative ID scope: load() assigns preliminary IDs, and render() recomputes IDs across all sections before the event. Overrides should be associated with IDs seen in the event, particularly when combining repeated sources. Adding repeated blocks preserves both instances using temporary collection keys until preparation assigns distinct IDs. Use replaceBlock() for an explicit editor override. Occurrence counters distinguish repeated sources within the final document.

URPC renders a deep copy of the source block tree. Repeated renders start from the source and rerun the event/processors. `getDocument()` exposes the last working document; `setDocument()` replaces the source. Overrides keep the source UID. `replaceBlock()` rejects a replacement with a different UID. Custom metadata objects inside props should be immutable; normal file-loaded props are arrays/scalars.

## Before-render event

1. Copy source document and assign/rekey all source block IDs.
2. Dispatch `urpc.before_render` using global `$eventDispatcher`.
3. Adopt `$event->getDocument()` after all listeners finish.
4. Process dynamic blocks and render HTML recursively.

`URPC\api\events\BeforeRenderEvent` implements AWT IEvent and provides `getDocument()`, `setDocument()` and `getURPC()`. Register listeners against `BeforeRenderEvent::NAME`. Listeners implement AWT IEventListener; their handle() returns an array as required by AWT, but document replacement uses the event setter, not dispatch's result array. Later listeners see earlier document replacements. With no listeners, the prepared document is rendered unchanged.

No IDs are recalculated after the event or a dynamic processor. Persist editor changes outside URPC and reconnect them by source UID. New runtime-only blocks may be inserted after the event without persistent IDs. Listener exceptions propagate.

## Dynamic processors

```php
final class GreetingProcessor implements \URPC\api\contracts\IURPCBlockRenderer
{
    public function process(
        \URPC\api\contracts\IURPCBlock $block,
        \URPC\service\renderer\URPC $urpc
    ): void {
        $props = $block->getBlockDescriptor()->getBlockProps();
        $block->setBlockContent((string) ($props['greeting'] ?? 'Hello'));
        // Access request/package context with $urpc->getContext().
    }
}
```

The processor changes attributes, plain-text content or children and returns no HTML. It runs before rendering its own element and before processing its current children. Thus newly added children are processed too, and removed children are not rendered. Mutating renderer/name does not trigger another processor on the same block in that render. All classes must implement the interface; missing/invalid processors throw.

A fresh processor is constructed for each invocation through AWT ObjectFactory. For dependencies, configure a factory in PHP:

```php
$factory = (new \object\ObjectFactory())->setConstructorArgs([$repository]);
$urpc->setRendererFactory(\MyTheme\ProductProcessor::class, $factory);
```

The JSON still names the class. Constructor dependencies stay in PHP, not JSON. Factories are cloned before use. Blocks cannot directly contain HTML returned from a processor.

## Changes and migration

- URPC now extends AWT Controller; `index()` returns the configured instance.
- Dynamic renderers now implement `process(IURPCBlock, URPC): void` instead of `render(): string`.
- Descriptors expose getSourceKey()/setSourceKey(); custom descriptor implementations must implement these methods.
- MD5 + structural occurrence counter replaces SHA-256 + positional path. Existing persisted IDs require migration/reset; this update does not automatically migrate editor overrides.
- Block defaults/constructors support manual composition. ID-less blocks are retained until preparation assigns their IDs.
- fromArray() validates already-instantiated blocks; JSON hydration remains the loader's responsibility.
- Duplicate addSection() preserves content. Editor replacement enforces matching UIDs.
- URPC render() bypasses Blade/template loading and returns its own HTML. Inherited DOM/template helpers are not part of this pipeline; no AWT RenderReadyEvent is dispatched by this implementation.

Examples: examples/usage.php, examples/header.json, examples/GreetingProcessor.php and examples/BeforeRenderListener.php.

## Checks

```sh
php tests/run.php /path/to/framework/awt_src/classes
```

Checks cover canonical IDs, namespace separation, duplicate counters, unrelated insertion stability, escaping, void elements, event replacement, dynamic processing, repeat rendering, source isolation, JSON provider and invalid JSON. Execution requires an installed PHP runtime and AWT classes.

## Document providers and optional customization

`URPC\service\contracts\provider\IURPCDocumentProvider` declares
`createDocument(context\Context $context): IURPCDocument`. Any package may implement it.
The integration package publishes/resolves that provider through AWT shared resources
and injects it using `$urpc->setDocumentProvider($provider)`. URPC does not resolve
shared resources itself or know the implementing package or theme engine.

The provider is called once on every render that has no directly supplied document.
It should return a fresh request-specific document. AWT context must be set when
using a provider; otherwise rendering throws an explicit LogicException. Direct
`setDocument()` takes precedence and needs no context. `clearDocument()` removes
that override and resumes using the provider.

Subclass `prepareDocument(IURPCDocument $document): IURPCDocument` to optionally
customize content or layout. The hook receives a deep working copy in both direct
and provider modes. It runs before ID generation and before the editor event.
Returning a different document is supported. The default hook is a no-op, so a theme
without PHP customization can use the provider-defined layout unchanged. Providers
should set source keys for their blocks; setSourceNamespace() supplies a fallback
for manually added blocks. Changes in this hook become part of source identities.

Final order: direct document or provider → working copy → prepareDocument hook →
IDs → before-render event → dynamic processors → HTML. See
examples/DocumentProvider.php and examples/CustomController.php.
