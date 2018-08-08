<?php
chdir("../../../../../../../../");

// ToDo: check if we can replace Zend Classes with Guzzle/Psr7 classes
use XapiProxy\App;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\SapiEmitter;
use Zend\Diactoros\ServerRequestFactory;

require_once __DIR__.'/vendor/autoload.php';

$request = ServerRequestFactory::fromGlobals();
$response = (new App())->run($request, new Response);
(new SapiEmitter)->emit($response);
?>
