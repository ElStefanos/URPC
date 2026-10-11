<?php
/*
 * Copyright (c) 2026 Stefan Crkvenjakov.
 * Part of the URPC project.
 * Github: https://github.com/ElStefanos/URPC
 * File: IURPCLoaderProvider.php
 */

namespace URPC\service\contracts\loader;

interface IURPCLoaderProvider
{
    public function getContent(): string;

    /** Stable logical key, e.g. "MyTheme/sections/header"; not an absolute path. */
    public function getSourceKey(): string;
}
