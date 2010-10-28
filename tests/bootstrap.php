<?php
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../app'));

// Define path to data directory
defined('DATA_PATH')
    || define('DATA_PATH', realpath(dirname(__FILE__) . '/data'));

// Create application, bootstrap, and run
require_once 'ControllerTestCase.php';