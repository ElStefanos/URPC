<?php
require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/env_loader.php';
require_once __DIR__ . '/manifest.php';
require_once __DIR__ . '/zip_creator.php';
require_once __DIR__ . '/installer.php';
try {
    if (!chdir(dirname(__DIR__, 2))) throw new RuntimeException('Cannot enter project directory.');
    if (NAME === '') throw new RuntimeException('Set NAME in build/runtime/constants.php to the package folder name.');
    $devEnv = loadEnvConfig();
    $manifest = readBuildManifest(MANIFEST);
    if (!is_file(NAME . '/main.php')) throw new RuntimeException('Package main.php not found.');
    echo color("Package: {$manifest['name']}, type: {$manifest['type']}, version: {$manifest['version']}\n", COLOR_GREEN);
    $zipFile = createZip($manifest, $manifest['version']);
    installPackage($zipFile, $devEnv);
} catch (Throwable $error) {
    fwrite(STDERR, color('Error: ' . $error->getMessage() . "\n", COLOR_RED));
    exit(1);
}

