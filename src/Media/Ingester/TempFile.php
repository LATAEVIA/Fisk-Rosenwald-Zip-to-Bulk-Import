<?php
namespace ZipImporter\Media\Ingester;

use Omeka\Api\Request;
use Omeka\Entity\Media;
use Omeka\File\TempFileFactory;
use Omeka\File\Validator;
use Omeka\Media\Ingester\IngesterInterface;
use Omeka\Stdlib\ErrorStore;
use Laminas\View\Renderer\PhpRenderer;

class TempFile implements IngesterInterface
{
    /**
     * @var string
     */
    protected $directory;

    /**
     * @var bool
     */
    protected $deleteWhenDone = false;

    /**
     * @var TempFileFactory
     */
    protected $tempFileFactory;

    /**
     * @var Validator
     */
    protected $validator;

    /**
     * @param TempFileFactory $tempFileFactory
     * @param Validator $validator
     */
    public function __construct(
        $directory,
        TempFileFactory $tempFileFactory,
        Validator $validator
    ) {
        // Only work on the resolved real directory path.
        $this->directory = $directory ? realpath($directory) : '';
        $this->tempFileFactory = $tempFileFactory;
        $this->validator = $validator;
    }

    public function getLabel()
    {
        return 'Temp File'; // @translate
    }

    public function getRenderer()
    {
        return 'file';
    }

    /**
     * Ingest from a URL.
     *
     * Accepts the following non-prefixed keys:
     *
     * + filepath: (required) The filename to ingest.
     * + store_original: (optional, default true) Store the original file?
     *
     * {@inheritDoc}
     */
    public function ingest(Media $media, Request $request, ErrorStore $errorStore)
    {
        // TODO When i call a job that uses this, it never completes, and it doesn't throw an error. I guess do nothing?
        $data = $request->getContent();
        if (!isset($data['filepath'])) {
            $errorStore->addError('filepath', 'No ingest filename specified'); // @translate
            return;
        }

        $filepath = $data['filepath'];
        $fileinfo = new \SplFileInfo($filepath);
        $realPath = $this->verifyFile($fileinfo);
        if (false === $realPath) {
            $errorStore->addError('filepath', sprintf(
                'Cannot load file "%s". File does not exist or does not have sufficient permissions', // @translate
                $filepath
            ));
            return;
        }

        $tempFile = $this->tempFileFactory->build();
        $tempFile->setSourceName($fileinfo->getFilename());

        // Copy the file to a temp path, so it is managed as a real temp file (#14).
        copy($realPath, $tempFile->getTempPath());

        if (!$this->validator->validate($tempFile, $errorStore)) {
            return;
        }

        $media->setSource($fileinfo->getFilename());

        $storeOriginal = $data['store_original'] ?? true;
        $tempFile->mediaIngestFile($media, $request, $errorStore, $storeOriginal, true, true, true);

        if ($this->deleteWhenDone) {
            unlink($realPath);
        }
    }

    public function form(PhpRenderer $view, array $options = [])
    {
        return '<br/>This ingester is only meant to be used by modules that write to temp directories.';
    }

    /**
     * Verify the passed file.
     *
     * Working off the "real" base directory and "real" filepath: both must
     * exist and have sufficient permissions; the filepath must begin with the
     * base directory path to avoid problems with symlinks; the base directory
     * must be server-writable to delete the file; and the file must be a
     * readable regular file.
     *
     * @param \SplFileInfo $fileinfo
     * @return string|false The real file path or false if the file is invalid
     */
    public function verifyFile(\SplFileInfo $fileinfo)
    {
        if (false === $this->directory) {
            return false;
        }
        $realPath = $fileinfo->getRealPath();
        if (false === $realPath) {
            return false;
        }
        if (0 !== strpos($realPath, $this->directory)) {
            return false;
        }
        if ($this->deleteWhenDone && !$fileinfo->getPathInfo()->isWritable()) {
            return false;
        }
        if (!$fileinfo->isFile() || !$fileinfo->isReadable()) {
            return false;
        }
        return $realPath;
    }
}