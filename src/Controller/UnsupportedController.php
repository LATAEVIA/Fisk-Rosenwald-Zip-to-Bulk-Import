<?php
namespace ZipImporter\Controller;
use Laminas\View\Model\ViewModel;
use Laminas\Mvc\Controller\AbstractActionController;

class UnsupportedController extends AbstractActionController
{
    protected $error;

    public function __construct($error) {
        $this->error = $error;
    }

    protected function allActions() {
        $view = new ViewModel;
        $view->setTemplate('zip-importer/index/unsupported');
        $this->messenger()->addError($this->error);
        return $view;
    }

    public function indexAction()
    {
        return $this->allActions();
    }

    public function uploadAction()
    {
        return $this->allActions();
    }

    protected function mappingAction()
    {
        return $this->allActions();
    }
}
