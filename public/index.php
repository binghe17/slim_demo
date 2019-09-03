<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';
require '../src/config/db.php';

//-----log
// use \Monolog\Logger;
// use \Monolog\Handler\StreamHandler;
// $log = new Logger('name');
// $log->pushHandler(new StreamHandler('../log/app.txt', Logger::WARNING));
// $log->addWarning('Foo'); //log기록



//-----
// $app = new \Slim\App;
$config = ['settings' => [
    'addContentLengthHeader' => false,
]];
$app = new \Slim\App($config);

//주의: require는 routes정의 하기전에 해야 함.
require '../src/routes/customers.php';
require '../src/routes/test.php'; 
require '../src/routes/upload.php'; 




//-------------middleware
// $app->add(function ($request, $response, $next) {
// 	$response->getBody()->write('BEFORE');
// 	$response = $next($request, $response);
// 	$response->getBody()->write('AFTER');
// 	return $response;
// });


//--------------routes
$app->get('/', function ($request, $response, $args) {
	$response->getBody()->write('Hello World');
	return $response;
});



$app->group('/utils', function () use ($app) {
    $app->get('/date', function ($request, $response) {
        return $response->getBody()->write(date('Y-m-d H:i:s'));
    });
    $app->get('/time', function ($request, $response) {
        return $response->getBody()->write(time());
    });
})->add(function ($request, $response, $next) {
    $response->getBody()->write('It is now ');
    $response = $next($request, $response);
    $response->getBody()->write('. Enjoy!');

    return $response;
});
//TEST: http://localhost/test/restfullPhp-master/public/utils/time






//get param
$app->get('/test2', function ($request, $response) {
    $params = $request->getQueryParams();
    $response->write("param:" . json_encode($params));
    // echo $response;
});
//TEST: http://localhost/test/restfullPhp-master/public/test2?a=1&b=2


//get header
$app->get('/test3', function ($request, $response) {
    // 모든 헤더 가져 오기
    $headers = $request->getHeaders();
    foreach ($headers as $name => $values) {
        echo $name . ": " . implode(", ", $values) .'<br>';
    }
    echo '<br><br>';
    // 하나의 헤더 얻기
    if ($request->hasHeader('Accept')) {
        // $headerValueArray = $request->getHeader('Accept');
        // print_r($headerValueArray[0]);
        $headerValueString = $request->getHeaderLine('Accept');
        print_r($headerValueString);
    }

});
$app->get('/test4', function ($request, $response) {
    $body = $request->getBody();
    echo '<pre>';
    print_r($body->getMetadata());
});


$app->get('/test5', function ($request, $response) {
    $params = $request->getQueryParams();

    // $response->write('<pre>');
    // $response->write(print_r( $GLOBALS ));
    // $response->write(print_r( $params ));
    
    // $response->write(print_r( $params ));
    include '../src/page/phpinfo.php';

});





$app->run();