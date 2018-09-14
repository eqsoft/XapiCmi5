<?php

// don't do anything on request method OPTIONS (see .htaccess)
//if ($_SERVER['REQUEST_METHOD'] == "OPTIONS") {
//    exit;
//}
chdir("../../../../../../../../");

// ToDo: check if we can replace Zend Classes with Guzzle/Psr7 classes
use XapiProxy\App;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\SapiEmitter;
use Zend\Diactoros\ServerRequestFactory;

require_once __DIR__.'/vendor/autoload.php';

function handleRequest() {
	$request = ServerRequestFactory::fromGlobals();
	$response = (new App())->run($request, new Response);
	(new SapiEmitter)->emit($response);
}

handleRequest();

?>
