<?php
function installPackage(string $zipFile, array $devEnv): void
{
    if ($devEnv['no_install'] ?? false) {
        echo color("Installation skipped.\n", COLOR_YELLOW);
        return;
    }
    if (!$devEnv['install']) {
        echo color("Install package now? (y/N): ", COLOR_GREEN);
        $answer = fgets(STDIN);
        if ($answer === false || strtolower(trim($answer)) !== 'y') {
            echo color("Installation skipped.\n", COLOR_YELLOW);
            return;
        }
    }
    if (!extension_loaded('curl')) throw new RuntimeException('PHP cURL extension is required for deployment.');
    foreach (['address', 'remote_install_path', 'devSecret'] as $key) {
        if (!isset($devEnv[$key]) || !is_string($devEnv[$key]) || $devEnv[$key] === '') {
            throw new InvalidArgumentException('Missing deployment setting: ' . $key);
        }
    }
    if (!is_file($zipFile)) throw new RuntimeException('ZIP file not found.');
    $url = rtrim($devEnv['address'], '/') . '/' . ltrim($devEnv['remote_install_path'], '/');
    if (!in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true)) {
        throw new InvalidArgumentException('Deployment address must use HTTP or HTTPS.');
    }
    $curl = curl_init($url);
    if ($curl === false) throw new RuntimeException('Cannot initialize cURL.');
    try {
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 300,
            CURLOPT_POSTFIELDS => [
                'package' => new CURLFile(realpath($zipFile), 'application/zip', basename($zipFile)),
                'devSecret' => $devEnv['devSecret'],
                'action' => $devEnv['action'] ?? 'install',
            ],
        ]);
        $response = curl_exec($curl);
        if ($response === false) throw new RuntimeException('Deployment transport error: ' . curl_error($curl));
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        // AWT 27 currently returns plain text, including some failures with HTTP 200.
        $body = trim($response);
        if ($status < 200 || $status >= 300 || !str_starts_with($body, 'Installed on ')) {
            throw new RuntimeException("Deployment failed (HTTP {$status}): " . $body);
        }
        echo color($body . "\n", COLOR_GREEN);
    } finally {
        curl_close($curl);
    }
}

