<?php

require_once 'function.php';

ob_start();
session_start();
date_default_timezone_set('PRC');

require_once __DIR__ . '/vendor/autoload.php';

$klein = new \Klein\Klein();

require_once 'file.model.php';

$klein->respond(function ($request, $response, $service) {
    $service->layout('view/master.phtml');
});

// index
$klein->respond('GET', '/', function ($request, $response, $service) {
    if ($request->code) {
        $response->redirect('/'.$request->code);
    }
    $service->render('view/index.phtml');
});

$klein->respond('POST', '/', function ($request, $response) {
    if (isset($_FILES['file'])) {
        $code = upload_file($_FILES['file']);
        $response->redirect('/'.$code);
    }
    $response->redirect('/');
});

// get file
$klein->respond('/[:code]', function ($request, $response, $service) {
    $response->code = $request->code;
    $response->share_link = 'http://'.$_SERVER['HTTP_HOST'].'/'.$response->code;
    $response->download_uri = get_uri($request->code);

    $service->render('view/download.phtml');
});

$klein->dispatch();
