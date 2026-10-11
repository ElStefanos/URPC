<?php
/*
 * Copyright (c) 2026 Stefan Crkvenjakov.
 * Part of the URPC project.
 * Github: https://github.com/ElStefanos/URPC
 * File: install.php
 */

use database\creator\ColumnCreator;
use database\creator\TableWizard;
use installer\interfaces\package\actions\IPostInstall;

class URPCInstall implements IPostInstall
{
    private TableWizard $dbWizard;
    private ColumnCreator $columnCreator;

    public function postInstall(int $packageId, string $packageName): bool
    {
        // Post install logic here
        return true;
    }
}
