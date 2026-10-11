<img width="256" height="256" alt="logo" src="https://github.com/user-attachments/assets/06a9ad61-79a6-4bdb-bfb1-17b3e148fe60" />

# URPC
### Universal Rendering Pipeline Controller

**One rendering pipeline. Connected themes, editors, and packages.**

URPC brings structured content and extensible rendering together for **Advanced Web Tools**. It gives theme engines, visual editors, and other packages a common document model—so they can work together without depending on each other's internal implementation.

Build the experience your way. Let a provider compose the page, let an editor customize its content, and let dynamic blocks adapt it to the current request. URPC turns the resulting document into server-rendered HTML.

[Explore the developer documentation](URPC/readme.md)

## Built to connect your ecosystem

### Themes with freedom

A theme engine can provide a complete document and layout. Theme developers can use that document as it is, or customize it through a dedicated hook before rendering. URPC remains independent of the engine supplying it.

### Editors that work alongside themes

A before-render event exposes the prepared document with its generated block identifiers. Editors and integration packages can apply saved changes or replace the document before HTML is produced.

### Dynamic content, shared structure

Package-defined processors can update block text, attributes, and children using their own logic and the controller's context. The final structure passes through the same HTML renderer.

### AWT integration from the start

URPC extends the AWT controller, making it usable in the existing request lifecycle. Document providers can be connected through AWT shared resources by the package integrating them.

## What you get

- **Composable documents:** organize blocks into sections and nested structures.
- **Flexible document providers:** supply a document from another package or pass one directly.
- **Optional theme customization:** adjust the document without rebuilding the rendering pipeline.
- **Editor integration:** apply overrides through the before-render event.
- **Automatic block identifiers:** identify source structures without manually assigning every block an ID.
- **Extensible dynamic blocks:** use PHP processors for request-specific content and structure.
- **Server-rendered HTML:** recursively render blocks with escaped text and attribute values.

## From content to page

A provider supplies the document. Optional theme customization prepares it. URPC assigns block identifiers, exposes the document to integration listeners, processes dynamic blocks, and renders the HTML.

Each package owns its part of the experience. URPC provides the pipeline that brings those parts together.

## Developer documentation

For setup, JSON formats, document providers, customization hooks, events, dynamic processors, and examples, see **[URPC/readme.md](URPC/readme.md)**.

## Build & Deploy

The included development scripts package URPC and can deploy it to an AWT test instance.

**Requirements:** PHP with `php-zip`; `php-curl` for remote deployment. Configure the destination and matching development secret in `build/dev_env.json`.

**Linux / macOS**

```bash
chmod +x build.sh
./build.sh                  # Build, then prompt for installation
./build.sh --install        # Build and deploy
./build.sh --fast --install # Build changed files and deploy
```

**Windows**

```bat
build.bat
build.bat --install
build.bat --fast --install
```

Remote deployment requires `DEBUG` and `REMOTE_INSTALL_FOR_DEVS` enabled on the test server, with matching local `devSecret` and server `dev_secret`. Enable remote installation only in a development environment.
