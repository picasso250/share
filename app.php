<?php

require_once 'function.php';

ob_start();
session_start();
date_default_timezone_set('PRC');

require_once 'lib/klein.php';
require_once 'file.model.php';

respond(function ($request, $response) {
    $response->layout('view/master.phtml');
});

// index
respond('GET', '/', function ($request, $response) {
    if ($request->code) {
        $response->redirect('/'.$request->code);
    }
    $response->render('view/index.phtml');
});

respond('POST', '/', function ($request, $response) {
    if (isset($_FILES['file'])) {
        $code = upload_file($_FILES['file']);
        $response->redirect('/'.$code);
    }
    $response->redirect('/');
});

// get file
respond('/[:code]', function ($request, $response) {
    $response->code = $request->code;
    $response->share_link = 'http://'.$_SERVER['HTTP_HOST'].'/'.$response->code;
    $response->download_uri = get_uri($request->code);

    $response->render('view/download.phtml');
});
dispatch();
