<?php
namespace ZipImporter\Service\Controller;

use ZipImporter\Controller\IndexController;
use ZipImporter\Controller\UnsupportedController;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class IndexControllerFactory implements FactoryInterface
{
    protected $services;

    public function __invoke(ContainerInterface $serviceLocator, $requestedName, array $options = null)
    {
        $this->services = $serviceLocator;
        
        $mediaIngesterManager = $this->services->get('Omeka\Media\Ingester\Manager');

        if (!$this->csvImportIsInstalled()) {
            return new UnsupportedController(
                'ZipImporter requires the CSVImport Omeka module to function.'
                . ' Please install it.');
        }

        if (!extension_loaded('zip')) {
            return new UnsupportedController(
                'ZipImporter requires the PHP zip extension to function.'
                . ' Your server does not have it installed.');
        }

        $config = $this->services->get('CSVImport\Config');
        $userSettings = $this->services->get('Omeka\Settings\User');
        $tempDir = $this->services->get('Config')['temp_dir'];

        $indexController = new IndexController($config, $mediaIngesterManager, $userSettings, $tempDir);
        return $indexController;
    }

    protected function csvImportIsInstalled() {
        if (!$this->services->has('CSVImport\Config'))
            return false;

        if (!class_exists('\CSVImport\Controller\IndexController'))
            return false;

        return true;
    }
}
