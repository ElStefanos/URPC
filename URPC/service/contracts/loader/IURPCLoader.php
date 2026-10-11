<?php
/*
 * Copyright (c) 2026 Stefan Crkvenjakov.
 * Part of the URPC project.
 * Github: https://github.com/ElStefanos/URPC
 * File: IURPCLoader.php
 */

namespace URPC\service\contracts\loader;

use URPC\api\contracts\IURPCBlock;

interface IURPCLoader
{
    /** @return list<IURPCBlock> */
    public function load(IURPCLoaderProvider $provider): array;
}
