<?php

namespace URPC\service\contracts\loader;

use URPC\api\contracts\IURPCBlock;

interface IURPCLoader
{
    /** @return list<IURPCBlock> */
    public function load(IURPCLoaderProvider $provider): array;
}
