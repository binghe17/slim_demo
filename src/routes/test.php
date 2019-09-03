<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app = new \Slim\App;
$app->get('/hello/{name}', function (Request $request, Response $response, array $args) {
    $name = $args['name'];
    $data = array('name' => 'Bob', 'age' => 40);
    $response->getBody()->write("Hello, $name");
    return $response;
});
/// GET http://myserver.net/api/test?id=1 where index.php is within api/
$app->get('/test', function ($request, $response) {
    $params = $request->getQueryParams();
    echo $response->write("Hello " . var_dump($params));
});

$app->get('/image/p/{data:\w+}', function($request, $response, $args) {
    $data = $args['data'];
    $image = @file_get_contents("http://localhost/main/media/image/p/$data");
    if($image === FALSE) {
        $handler = $this->notFoundHandler;
        return $handler($request, $response);    
    }
    
    $response->write($image);
    return $response->withHeader('Content-Type', FILEINFO_MIME_TYPE);
});