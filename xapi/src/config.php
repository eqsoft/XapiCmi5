<?php

require_once './Customizing/global/plugins/Services/Repository/RepositoryObject/XapiCmi5/classes/class.ilXapiCmi5Type.php';
//require_once './Customizing/global/plugins/Services/Repository/RepositoryObject/XapiCmi5/classes/class.ilObjXapiCmi5.php';

use Zend\Diactoros\ServerRequestFactory;
use GuzzleHttp\RequestOptions;

//"MzNlNDY5Nzk3OGRhMWU3MWI4ZjI5ODczN2YwZThmYTg1NGM0MzExNDphZWI2OTNiNDAwOTc3YmQxNTdlYWQzNjBmYzI5NDk1MGU5MjNlYjdi"

function getTarget() { // are there calls without credentials???
    $no_credentials = (empty($_SERVER['PHP_AUTH_USER']) && empty($_SERVER['PHP_AUTH_PW']));
    if ($no_credentials) {
		_log("no credentials: " . $_SERVER["REQUEST_METHOD"]);
		header('HTTP/1.1 401 Authorization Required');
		exit;
	}
    $client = $_SERVER['PHP_AUTH_USER'];
    $token = $_SERVER['PHP_AUTH_PW'];

    \XapiProxy\DataService::initIlias($client,$token);

    $types_data = ilXapiCmi5Type::_getTypesData()[0];
    $endpoint = $types_data['lrs_endpoint'];
    $auth = 'Basic ' . base64_encode($types_data['lrs_key'] . ':' . $types_data['lrs_secret']);
	$target = array(  // needs validation of request before!
		"upstream" =>  $endpoint,
		"authorization" => $auth,
        "client" => $client,
        "token" => $token
	);
	return $target;
}

function getRequestOptions() {
	$ret = array(
			RequestOptions::VERIFY => false,
			RequestOptions::SYNCHRONOUS => true,
			RequestOptions::CONNECT_TIMEOUT => 5
			);
	return $ret;
}

function getMiddleware() {
	$middleware = [
			\XapiProxy\Middleware\RequestFilterXapi::class,
			\XapiProxy\Middleware\ProxyMiddleware::class,
            \XapiProxy\Middleware\ResponseFilterXapi::class
	];
	return $middleware;
}

function _log($txt) {
	file_put_contents("xapilog.txt",$txt."\n",FILE_APPEND);
}

return [
    'middleware' => getMiddleware(),
    'target' => getTarget(),
    'request_options' => getRequestOptions(),
    \XapiProxy\Runner::class => DI\object()
        ->constructorParameter('stack', DI\get('middleware')),
    \XapiProxy\Middleware\ProxyMiddleware::class => DI\object()
        ->constructorParameter('target', DI\get('target'))
        ->constructorParameter('request_options', DI\get('request_options')),
    \XapiProxy\Middleware\RequestFilterXapi::class => DI\object()
        ->constructorParameter('target', DI\get('target')),
    \XapiProxy\Middleware\ResponseFilterXapi::class => DI\object()
        ->constructorParameter('target', DI\get('target'))
        ->constructorParameter('request_options', DI\get('request_options'))
];
?>
