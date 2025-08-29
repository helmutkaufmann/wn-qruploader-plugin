<?php namespace Mercator\QrUploader\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * Users Back-end Controller
 */
class Users extends Controller
{
    public $implement = [
        \Backend\Behaviors\FormController::class,
        \Backend\Behaviors\ListController::class,
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public function __construct()
    {
        parent::__construct();
        // This is optional but good practice for setting the navigation context
        BackendMenu::setContext('Mercator.QrUploader', 'qruploader', 'users');
    }
}
