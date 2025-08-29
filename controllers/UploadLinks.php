<?php namespace Mercator\QrUploader\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class UploadLinks extends Controller
{
    public $implement = [
        \Backend\Behaviors\ListController::class,
        \Backend\Behaviors\FormController::class,
    ];

    // This tells the controller to look for these files
    // in its OWN directory (controllers/uploadlinks/)
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Mercator.QrUploader', 'qruploader', 'uploadlinks');
    }
}
