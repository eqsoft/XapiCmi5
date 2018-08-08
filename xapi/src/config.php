<?php

// DI needed?

// ToDo: check if we can replace Zend Classes with Guzzle/Psr7 classes
require_once './Customizing/global/plugins/Services/Repository/RepositoryObject/ExternalContent/classes/class.ilObjExternalContent.php';

use function DI\get;
use function DI\object;
use Zend\Diactoros\ServerRequestFactory;
use GuzzleHttp\RequestOptions;

// should all be moved to proxy or plugin class!
function isTestMode() {
	return false;
}

function getTarget() {
	//$target = 'https://ll.aptum.net/data/xAPI';
	getExternalContentObject();
	$target = array(  // needs validation of request before!
		"upstream" => 'http://ll.aptum.net/data/xAPI',
		"authorization" => 'Basic MzNlNDY5Nzk3OGRhMWU3MWI4ZjI5ODczN2YwZThmYTg1NGM0MzExNDphZWI2OTNiNDAwOTc3YmQxNTdlYWQzNjBmYzI5NDk1MGU5MjNlYjdi'
	);
	if (isTestMode()) {
		$request = ServerRequestFactory::fromGlobals();
		$target = str_replace('xapiproxy.php','lrs.php',$request->getUri());
	}
	return $target;
}

function getRequestOptions() {
	$ret = array();
	if (isTestMode()) {
		$ret = array(
			RequestOptions::VERIFY => false,
			RequestOptions::SYNCHRONOUS => true,
			RequestOptions::CONNECT_TIMEOUT => 10
		);
	}
	else {
		$ret = array(
			RequestOptions::VERIFY => false,
			RequestOptions::SYNCHRONOUS => true,
			RequestOptions::CONNECT_TIMEOUT => 10
		);
	}
	return $ret;
}

function getMiddleware() {
	$middleware = [
			\XapiProxy\Middleware\RequestFilterXapi::class,
			\XapiProxy\Middleware\ProxyMiddleware::class
	];
	return $middleware;
}

function getExternalContentObject() { // do authentication and return xxco
	if (isset($_GET['token'])) { // initial content call 
		$token = base64_decode(trim($_GET['token']));
		preg_match('/^(\d+)\_(.*?)\:(.*)$/',$token,$matches);
		//_log(var_export($matches,TRUE));
		//return new ilObjExternalContent($_GET["ref_id"]);
	}
	else { // async calls with basic auth
		//$request = ServerRequestFactory::fromGlobals();
		//$serverParams = $request->getServerParams();
		//Will Stefan weghaben _log(var_export($_SERVER,TRUE));
	}
}

function _log($txt) {
	file_put_contents("xapilog.txt",$txt."\n",FILE_APPEND);
}

return [
    'middleware' => getMiddleware(),
    \XapiProxy\Runner::class => object()
        ->constructorParameter('stack', get('middleware')),
    \XapiProxy\Middleware\ProxyMiddleware::class => object()
        ->constructorParameter('target', getTarget())
        ->constructorParameter('request_options', getRequestOptions())
];
?>
