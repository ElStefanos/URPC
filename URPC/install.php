<?php

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
