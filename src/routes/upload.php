<?php

require_once __DIR__ . '/../../vendor/autoload.php';
// require_once '../vendor/autoload.php';

use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\UploadedFile;


if(!isset($app)) $app = new \Slim\App();

$container = $app->getContainer();


//==================Twig-View 방식

$container['view'] = function ($container) {
    $conf = [];
    // $conf['cache'] =  __DIR__.'/../templates/cache'; //캐시함
    $view = new \Slim\Views\Twig( __DIR__.'/../templates/', $conf);
    $router = $container->get('router');
    $uri = \Slim\Http\Uri::createFromEnvironment(new \Slim\Http\Environment($_SERVER));
    $view->addExtension(new \Slim\Views\TwigExtension($router, $uri));
    return $view;
};

//---함수정의
$environ = $container->get('view')->getEnvironment();
$environ->addFunction(new Twig_SimpleFunction('shortest', function ($a, $b) {
    return strlen($a) <= strlen($b) ? $a : $b;
}));
$environ->addFunction(new Twig_SimpleFunction('length', function ($a) {
    return strlen($a);
}));


//---route

$app->get('/upload2/{name}', function ($request, $response, $args) {
    $args['other'] = 'other';
    return $this->view->render($response, 'upload2.html',  $args);
})->setName('profile');
//TEST http://localhost/test/restfullPhp-master/public/upload2/asdf





//==================PHP-View 방식

// $container['upload_directory'] =  __DIR__. '/../../public/uploads';
// $container['view'] = function ($container) {
//     return new \Slim\Views\PhpRenderer( __DIR__.'/../templates/');
// };
//-------

// $app->get('/upload/{name}', function ($request, $response, $args) {
//     $args['other'] = 'other';
//     return $this->view->render($response, 'upload.html',  $args);
// });//->setName('profile');
// //TEST http://localhost/test/restfullPhp-master/public/upload/test

// $app->get('/upload', function ($request, $response, $args) {
//     echo '<pre>'; // print_r($GLOBALS);
//     print_r($_GET);
//     $data['name'] = isset($_GET['name']) ? $_GET['name'] : '';
//     $data['other'] = isset($_GET['other']) ? $_GET['other'] : '';
//     return $this->view->render($response, 'upload.html',  $data);
// });
// //TEST http://localhost/test/restfullPhp-master/public/upload?name=file1&other=asdf


//======================
$app->post('/upload', function(Request $request, Response $response) {
    // $headers = $request->getHeaders();
    // foreach ($headers as $name => $values) {
    //     echo $name . ": " . implode(", ", $values) .'<br>';
    // }
    
    $directory = $this->get('upload_directory');
    $uploadedFiles = $request->getUploadedFiles();

    // handle single input with single file upload
    $uploadedFile = $uploadedFiles['example1'];
    if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
        $filename = moveUploadedFile($directory, $uploadedFile);
        $response->write('uploaded ' . $filename . '<br/>');
    }


    // handle multiple inputs with the same key
    foreach ($uploadedFiles['example2'] as $uploadedFile) {
        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            $filename = moveUploadedFile($directory, $uploadedFile);
            $response->write('uploaded ' . $filename . '<br/>');
        }
    }

    // handle single input with multiple file uploads
    foreach ($uploadedFiles['example3'] as $uploadedFile) {
        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            $filename = moveUploadedFile($directory, $uploadedFile);
            $response->write('uploaded ' . $filename . '<br/>');
        }
    }

});



/**
 * Moves the uploaded file to the upload directory and assigns it a unique name
 * to avoid overwriting an existing uploaded file.
 *
 * @param string $directory directory to which the file is moved
 * @param UploadedFile $uploadedFile file uploaded file to move
 * @return string filename of moved file
 */
function moveUploadedFile($directory, UploadedFile $uploadedFile)
{
    $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
    $basename = bin2hex(random_bytes(8)); // see http://php.net/manual/en/function.random-bytes.php
    $filename = sprintf('%s.%0.8s', $basename, $extension);

    $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

    return $filename;
}

// $app->run();