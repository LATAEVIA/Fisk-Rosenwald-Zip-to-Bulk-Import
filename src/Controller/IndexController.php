<?php
namespace ZipImporter\Controller;

use CSVImport;
use ZipArchive;
use Omeka\Stdlib\Message;
use Laminas\View\Model\ViewModel;

class IndexController extends CSVImport\Controller\IndexController
{
    /**
     * The action triggered when you click "Zip Import," shows the first import
     * screen.
     *
     * Note: A lot of this module just modifies CSV Import's views and forms. It
     * does so so that (in theory) as that module gets updated, we get those
     * updates automatically in ZipImporter.
     * @return ViewModel
     */
    public function indexAction()
    {
        $view = new ViewModel;
        $form = $this->getForm(CSVImport\Form\ImportForm::class);

        // Modify the CSVImport Form to accept zipfiles only
        $form->setAttribute('action', 'zip-importer/upload'); // uploadAction
        $form->get('source')
            ->setOptions([
                'label' => 'Zip File', //@translate
                'info' => 'The archive containing a spreadsheet and a set of media to upload.', //@translate
            ])
            ->setAttribute('accept', 'application/zip');

        $view->form = $form;
        return $view;
    }

    /**
     * The action triggered when you submit the first zip import form.
     * 
     * 1. Extracts the Zip file to tmp
     * 2. parses the csv
     * 3. validates the zip structure
     * 4. shows the csvimport mapping form, allowing the user to map csv columns
     *    to actual omeka data fields.
     *
     * @return ViewModel|\Laminas\Http\Response
     */
    public function uploadAction()
    {
        $request = $this->getRequest();

        if (!$request->isPost()) {
            return $this->redirect()->toRoute('admin/zip-importer');
        }

        $post = $this->params()->fromPost();

        // Create a model of the formdata.
        $importForm = $this->getForm(CSVImport\Form\ImportForm::class);
        $post = array_merge_recursive(
            $request->getPost()->toArray(),
            $request->getFiles()->toArray()
        );

        // Validate the model.
        $importForm->setData($post);
        if (!$importForm->isValid()) {
            $this->messenger()->addFormErrors($importForm);
            return $this->redirect()->toRoute('admin/zip-importer');
        }

        // Unzip the archive to the tmp directory
        $zip = new ZipArchive;
        if (!$zip->open($post['source']['tmp_name'])) {
            $this->messenger()->addError('Must upload a zipfile.'); // @translate
            return $this->redirect()->toRoute('admin/zip-importer');
        }

        try {
            $tempPath = $this->getTempDir();
            $zip->extractTo($tempPath);
            $zip->close();
        } catch (\Error $e) {
            unlink($post['source']['tmp_name']); // Delete zip
            $this->messenger()->addError('Zipfile is invalid. It may be corrupted.'); // @translate
            return $this->redirect()->toRoute('admin/zip-importer');
        }
        $this->setTempPermissions($tempPath);
        unlink($post['source']['tmp_name']); // Delete zip

        // Find spreadsheet in archive
        $source = $this->getArchiveSource($tempPath);
        if (empty($source)) {
            // Clean up directory
            $this->rmTemp($tempPath);
            $this->messenger()->addError('No spreadsheet found in archive'); // @translate
            return $this->redirect()->toRoute('admin/zip-importer');
        }

        $filePath = $source['path'];
        $source = $source['source'];

        // @see CSVImport/Controller/IndexController::mappingAction()
        // This logic is pulled from there.
        $view = new ViewModel;
        $mappingOptions = array_intersect_key($post, array_flip([
            'resource_type',
            'delimiter',
            'enclosure',
            'automap_check_names_alone',
            'comment',
        ]));

        $resourceType = $post['resource_type'];
        $mediaType = $source->getMediaType();
        $post['media_type'] = $mediaType;
        $args = $this->cleanArgs($post);
        $source->setParameters($args);

        if (!$source->isValid()) {
            $message = $source->getErrorMessage() ?: 'The file is not valid.'; // @translate
            $this->messenger()->addError($message);
            return $this->redirect()->toRoute('admin/csvimport');
        }

        $columns = $source->getHeaders();
        if (empty($columns)) {
            $message = $source->getErrorMessage() ?: 'The file has no headers.'; // @translate
            $this->messenger()->addError($message);
            return $this->redirect()->toRoute('admin/csvimport');
        }

        $mappingOptions['columns'] = $columns;
        $form = $this->getForm(CSVImport\Form\MappingForm::class, $mappingOptions);

        // Replace csv action with zip-specific version
        $form->setAttribute('action', 'mapping');

        $automapOptions = [];
        $automapOptions['check_names_alone'] = $args['automap_check_names_alone'];
        $automapOptions['format'] = 'form';

        $autoMaps = $this->automapHeadersToMetadata($columns, $resourceType, $automapOptions);

        $view->setVariable('form', $form);
        $view->setVariable('resourceType', $resourceType);
        $view->setVariable('temppath', $tempPath);
        $view->setVariable('filepath', $filePath);
        $view->setVariable('filename', $post['source']['name']);
        $view->setVariable('filesize', $post['source']['size']);
        $view->setVariable('mediaType', $mediaType);
        $view->setVariable('columns', $columns);
        $view->setVariable('automaps', $autoMaps);
        $view->setVariable('mappings', $this->getMappingsForResource($resourceType));
        $view->setVariable('mediaForms', $this->getMediaForms());
        $view->setVariable('dataTypes', $this->getDataTypes());
        return $view;
    }

    /**
     * The action called when you submit the mapping form. This configures and
     * queues the actual import job.
     *
     * @return \Laminas\Http\Response
     */
    protected function mappingAction()
    {
        $request = $this->getRequest();

        if (!$request->isPost()) {
            return $this->redirect()->toRoute('admin/zip-importer');
        }

        $post = $this->params()->fromPost();
        $mappingOptions = array_intersect_key($post, array_flip([
            'resource_type',
            'delimiter',
            'enclosure',
            'automap_check_names_alone',
            'comment',
        ]));

        $form = $this->getForm(CSVImport\Form\MappingForm::class, $mappingOptions);
        $form->setData($post);

        if (!$form->isValid()) {
            $this->messenger()->addError('Invalid settings.'); // @translate
            $this->messenger()->addFormErrors($form);
            return $this->redirect()->toRoute('admin/zipimport');
        }
    
        if (isset($post['basic-settings']) || isset($post['advanced-settings'])) {
            // Flatten basic and advanced settings back into single level
            $post = array_merge($post, $post['basic-settings'], $post['advanced-settings']);
            unset($post['basic-settings'], $post['advanced-settings']);
        }

        $args = $this->cleanArgs($post);
        $this->saveUserSettings($args);
        $dispatcher = $this->jobDispatcher();
        $job = $dispatcher->dispatch('ZipImporter\Job\Import', $args);

        // The CsvImport record is created in the job, so it doesn't
        // happen until the job is done.
        $message = new Message(
            'Importing in background (%sjob #%d%s)', // @translate
            sprintf('<a href="%s">',
                htmlspecialchars($this->url()->fromRoute('admin/id', ['controller' => 'job', 'id' => $job->getId()]))
            ),
            $job->getId(),
            '</a>'
        );
        $message->setEscapeHtml(false);
        $this->messenger()->addSuccess($message);

        // Navigate over to csvimport's route. No reason to recreate this one.
        return $this->redirect()->toRoute('admin/csvimport/past-imports', ['action' => 'browse'], true);
    }

    /**
     * This snippet is a modification of getTempPath that creates a directory
     * instead of a file.
     * 
     * @throws \ErrorException
     * @return string
     */
    protected function getTempDir()
    {
        $tempPath = $this->getTempPath();
        unlink($tempPath);
        mkdir($tempPath);
        if (!is_dir($tempPath)) {
            throw new \ErrorException("Unable to create temporary directory");
        }
        return $tempPath;
    }

    /**
     * This is a recursive rmdir / rmfile comibination that does a depth-first
     * traversal of the directory structure, deleting everything within.
     *
     * @param string $path
     * @return void
     */
    protected function rmTemp($path)
    {
        $this->doRecursive(
            $path,
            fn($dir) => rmdir($dir),
            fn($file) => unlink($file)
        );
    }

    /**
     * Recursively set file permissions
     * @param mixed $path
     * @return void
     */
    protected function setTempPermissions($path) {
        $setPerms = fn($filepath) => chmod($filepath, 0777);
        $this->doRecursive($path, $setPerms, $setPerms);
    }

    /**
     * Recursively call callbacks on directories and files inside the
     * temp directory.
     *
     * @param string $path
     * @param callable $onDir
     * @param callable $onFile
     * @return void
     */
    protected function doRecursive(
        $path,
        $onDir = null,
        $onFile = null
    ) {
        if (!str_starts_with($path, $this->tempDir)) {
            // bail
            return;
        }

        $onDir = $onDir ?? function (...$args) { };
        $onFile = $onFile ?? function (...$args) { };

        // Handle file path
        if (!is_dir($path)) {
            $onFile($path);
            return;
        }

        // Handle directory path
        // First do children
        $dir = opendir($path);
        while(false !== ( $file = readdir($dir)) ) {
            if ($file == '.' || $file == '..') continue;
            // Recurse.
            $this->doRecursive(
                $path . DIRECTORY_SEPARATOR . $file,
                $onDir,
                $onFile
            );
        }
        closedir($dir);

        // Then do dir.
        $onDir($path);
    }

    /**
     * Traverse no more than one layer deep into an extracted zip directory,
     * searching for any file that satisfies as a "spreadsheet source" for
     * CSVImport, and return that source, along with it's path.
     *
     * @param string $path
     * @param bool $recurse
     *
     * @return array|null
     */
    protected function getArchiveSource($path, $recurse = true)
    {
        $children = array_diff(scandir($path), ['.', '..']);
        $dirs = [];

        foreach ($children as $child) {
            $child = "$path/$child";
            
            // Only interested in files, store off directories for recursion
            if (is_dir($child)) {
                $dirs[] = $child;
                continue;
            }

            $found = $this->getSource(['name' => $child, 'tmp_name' => $child]);

            // This is a valid archive source.
            if (!empty($found)) {
                $found->init($this->config);
                $found->setSource($child);
                return [
                    'source' => $found,
                    'path' => $child
                ];
            }
        }

        if (!$recurse) {
            // Not found
            return null;
        }

        // Recurse
        foreach ($dirs as $dir) {
            $found = $this->getArchiveSource($dir, false);
            if (!empty($found))
                return $found;
        }

        // Not found
        return null;
    }
}
