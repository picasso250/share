<?php
/**
 * @file    todo
 * @author  ryan <cumt.xiaochi@gmail.com>
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

define('UP_DOMAIN', 'xc222');
define('APP_ROOT', __DIR__.'/');
define('FILE_ROOT', APP_ROOT.'data');

require_once 'function.php';

ob_start();
session_start();
date_default_timezone_set('PRC');

require_once 'lib/klein.php';
require_once 'file.model.php';

// index
respond('GET', '/', function ($request, $response) {
    $response->render('view/index.phtml');
});

// upload
respond('POST', '/', function ($request, $response) {
    if (isset($_FILES['file'])) {
        echo upload_file($_FILES['file']);
    }
});

// get file
respond('/[:code]', function ($request, $response) {
    $uri = get_uri($request->code);
    $response->redirect($uri);
});
dispatch();
