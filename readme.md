<img width="256" height="256" alt="logo" src="https://github.com/user-attachments/assets/06a9ad61-79a6-4bdb-bfb1-17b3e148fe60" />


# Development Build & Deploy Tool
This tool automates the process of building and deploying your **Advanced Web Tools** package (plugins, themes, or systems) from your local development environment to a remote server. It packages your project into a zip file and can push it to a running instance for rapid testing.
## Features
- **Automated Packaging**: Creates a zip archive of your project based on your `manifest.json`.
- **Remote Installation**: Deploys the package to a remote server for instant testing.
- **Fast Build Mode**: Uses a caching system to package only the files that have changed, significantly speeding up the build process.
- **Flexible Configuration**: Customize build paths, naming conventions, and remote server details.

## Prerequisites
- PHP installed on your system.
- The `php-curl` extension must be enabled for remote installation.
- The `php-zip` extension must be enabled for packaging.

## Setup & Configuration
All configuration is handled in the file in your project's root. `build/dev_env.json`
``` json
{
  "release_folder": "releases",
  "build_save_format": "<name>-<version>",
  "address": "http://localhost:3000",
  "remote_install_path": "/dev/install",
  "devSecret": "12345678"
}
```
#### Configuration Options:

| Key | Description |
| --- | --- |
| `release_folder` | The directory where your packaged `.zip` files will be saved. |
| `build_save_format` | A template for your zip file's name. Supported placeholders: `<name>`, `<version>`, `<description>`, `<author>`, `<license>`. These are pulled from your `manifest.json`. |
| `address` | The base URL of your remote test server (e.g., `http://my-test-site.com`). |
| `remote_install_path` | The specific endpoint on the remote server that handles the package installation (e.g., `/dev/install`). |
| `devSecret` | A secret key that must match the one on your remote server to authorize the installation. |
## Usage
Use the (Windows) or (Linux/macOS) scripts from your project's root directory to run the tool. `build.bat` `build.sh`
### Windows
``` bash
build.bat [options]
```
### Linux / macOS
First, make the script executable:
``` bash
chmod +x build.sh
```
Then, run it:
``` bash
./build.sh [options]
```
### Command-Line Options
You can pass the following options to the build scripts:
- `--install`: Automatically installs the package on the remote server after building. Without this flag, the script will only build the package and then ask for confirmation before installing.
- `--fast`: Enables "Fast Build" mode. In this mode, the script only includes files that have been modified since the last build, making the process much quicker.

### Examples
**1. Build the package only:**
``` bash
# Windows
build.bat

# Linux / macOS
./build.sh
```
**2. Build the package and automatically install it:**
``` bash
# Windows
build.bat --install

# Linux / macOS
./build.sh --install
```
**3. Perform a fast build and install it:**
``` bash
# Windows
build.bat --fast --install

# Linux / macOS
./build.sh --fast --install
```
## How "Fast Build" Works
When you use the `--fast` flag, the script creates a cache file at `build/.file_cache.json`. This file stores a hash of each file included in the zip. On subsequent fast builds, the script checks each file against its cached hash. If the file hasn't changed, it's skipped. If it has changed (or is new), it's included in the package and its new hash is saved.
To force a full rebuild, you can simply delete the `build/.file_cache.json` file.
## Enabling Remote Installation (Server-Side)

> **SECURITY WARNING**: This feature is for development purposes only. Ensure it is disabled in any production environment to prevent unauthorized code execution.
>

To allow the script to install packages, you must configure your remote server:
1. Open your server's `awt_config.php` file.
2. Set `DEBUG` and `REMOTE_INSTALL_FOR_DEVS` to `true`.
3. Ensure the `dev_secret` in `awt_config.php` matches the `devSecret` in your local file. `build/dev_env.json`

