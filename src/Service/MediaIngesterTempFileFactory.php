<?php
namespace ZipImporter\Service;

use ZipImporter\Media\Ingester\TempFile;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class MediaIngesterTempFileFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $tempDir = $services->get('Config')['temp_dir'];

        return new TempFile(
            $tempDir,
            $services->get('Omeka\File\TempFileFactory'),
            $services->get('Omeka\File\Validator')
        );
    }
}