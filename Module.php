<?php
namespace ZipImporter;

use Omeka\Module\AbstractModule;
use Laminas\ModuleManager\ModuleManager;

class Module extends AbstractModule
{
    public function init(ModuleManager $moduleManager)
    {
        require_once __DIR__ . '/vendor/autoload.php';
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
}
