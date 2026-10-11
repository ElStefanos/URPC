<?php

define('NAME', 'URPC');
define('MANIFEST', NAME . '/manifest.json');

define('COLOR_RESET', "\033[0m");
define('COLOR_RED', "\033[31m");
define('COLOR_GREEN', "\033[32m");
define('COLOR_YELLOW', "\033[33m");

function color(string $text, string $color): string {
    return $color . $text . COLOR_RESET;
}

