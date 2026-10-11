<?php

namespace URPC\service\contracts\loader;

interface IURPCLoaderProvider
{
    public function getContent(): string;

    /** Stable logical key, e.g. "MyTheme/sections/header"; not an absolute path. */
    public function getSourceKey(): string;
}
