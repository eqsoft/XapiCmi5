<?php

require_once './Customizing/global/plugins/Services/Repository/RepositoryObject/XapiCmi5/classes/class.ilXapiCmi5Type.php';

use Zend\Diactoros\ServerRequestFactory;
use GuzzleHttp\RequestOptions;
//"MzNlNDY5Nzk3OGRhMWU3MWI4ZjI5ODczN2YwZThmYTg1NGM0MzExNDphZWI2OTNiNDAwOTc3YmQxNTdlYWQzNjBmYzI5NDk1MGU5MjNlYjdi"
function getTarget() {
    $types_data = ilXapiCmi5Type::_getTypesData()[0];
    $endpoint = $types_data['lrs_endpoint'];
    $auth = 'Basic ' . base64_encode($types_data['lrs_key'] . ':' . $types_data['lrs_secret']);
	$target = array(  // needs validation of request before!
		"upstream" =>  $endpoint, 
		"authorization" => $auth
	);
    //_log(var_export($target,TRUE));
	return $target;
}

function getRequestOptions() {
	$ret = array(
			RequestOptions::VERIFY => false,
			RequestOptions::SYNCHRONOUS => true,
			RequestOptions::CONNECT_TIMEOUT => 10
			);
	return $ret;
}

function getMiddleware() {
	$middleware = [
			\XapiProxy\Middleware\RequestFilterXapi::class,
			\XapiProxy\Middleware\ProxyMiddleware::class
	];
	return $middleware;
}

function _log($txt) {
	file_put_contents("xapilog.txt",$txt."\n",FILE_APPEND);
}

return [
    'middleware' => getMiddleware(),
    \XapiProxy\Runner::class => DI\object()
        ->constructorParameter('stack', DI\get('middleware')),
    \XapiProxy\Middleware\ProxyMiddleware::class => DI\object()
        ->constructorParameter('target', getTarget())
        ->constructorParameter('request_options', getRequestOptions())
];
?>
