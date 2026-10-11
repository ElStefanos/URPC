<?php

function loadEnvConfig(): array {
    $devEnvPath = __DIR__ . '/../dev_env.json';
    if (!file_exists($devEnvPath)) {
        throw new RuntimeException("Error: dev_env.json not found.\n");
    }

    $devEnvContent = file_get_contents($devEnvPath);
    $devEnv = json_decode($devEnvContent, true);

    if (!$devEnv) {
        throw new RuntimeException("Error: Invalid JSON in dev_env.json.\n");
    }

    $options = getopt('', ['install', 'fast', 'no-install', 'action:']);
    $install = isset($options['install']);
    $fast = isset($options['fast']);
    $devEnv['build_mode'] = $fast ? "dev_fast" : "dev";
    if ($install && isset($options['no-install'])) throw new InvalidArgumentException('--install and --no-install are mutually exclusive.');
    $devEnv['install'] = $install;
    $devEnv['no_install'] = isset($options['no-install']);
    $devEnv['action'] = $options['action'] ?? $devEnv['action'] ?? 'install';
    if (!in_array($devEnv['action'], ['install', 'update'], true)) throw new InvalidArgumentException('Action must be install or update.');

    return $devEnv;
}

