<?php

namespace XapiProxy\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use GuzzleHttp\Psr7;

class RequestFilterXapi
{
	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next)
	{
		// only sniff POST and PUT requests
		$method = $request->getMethod();
		if ($method !== "POST" && $method !== "PUT") {
			//$this->_log($method);
			//$this->_log($request->getUri());
			return $next($request, $response);	
		}
		try {
			$body = $request->getBody()->getContents();
			if (empty($body)) {
				return $next($request, $response);
			}
			$body = $this->modifyBody($body);
			//$this->_log($body);
			$changes = array (
				"body" => $body
			);
			$request = \GuzzleHttp\Psr7\modify_request($request, $changes);
			return $next($request, $response);
		}
		catch (Exception $e) { // ToDo: proper ExceptionHandling!
			$this->_log($e->getMessage());
			return $next($request, $response);
		}
		return $next($request, $response);
	}

	/**
	 * should be public function in Plugin: PLUGINCLASS->modifyBody() see notes
	 */ 
	private function modifyBody($body) {

		/** 
		 * The plugin class should provide a transformation function like PLUGINCLASS->modifyBody($body as string) (return string) 
		 * this could also be used by cronjob requests in case of immutable lrs endpoints! But maybe we could reuse proxy classes for cronjobs too?
		 * 
		 */
		$anonymous_user = false;
		$obj = json_decode($body, false);
		//$this->_log(var_export($obj,TRUE));
		if (is_object($obj)) {
			//$this->_log("is_object");
			if ($anonymous_user && isset($obj->actor) && isset($obj->actor->mbox)) {
				$obj = $this->setAnonymous($obj);
			}
			$this->setStatus($obj);
		}
		if (is_array($obj)) {
			//$this->_log("is_array");
			for ($i=0; $i<count($obj); $i++) {
				if ($anonymous_user && isset($obj[$i]->actor) && isset($obj[$i]->actor->mbox)) {
					$obj[$i] = $this->setAnonymous($obj[$i]);
				}
				$this->setStatus($obj[$i]);
			} 
		}
		return json_encode($obj); 
	}

	/**
	 * should be private function in Plugin
	 */ 
	private function setAnonymous($obj) {
		$user_map = array (
				"xapischneider@internetlehrer.de" => "b8e9c142b1514edaa5be30cc07657259@nomail.de",
				"schneider@hrz.uni-marburg.de" => "8973d97e824c4a6aaf9d31b7d5afdf00@nomail.de"
		);
		$actor = str_replace("mailto:","",$obj->actor->mbox);
		if (array_key_exists($actor,$user_map)) {
			$a_actor = $user_map[$actor];
			$obj->actor->mbox = "mailto:".$a_actor;
		}
		if ($obj->actor->name) {
			$obj->actor->name = "anonymous";
		}
		return $obj;
	}
	/**
	 * should be private function in Plugin
	 * no return value, just sets the learning status of actor
	 */ 
	private function setStatus($obj) {
		// is valid object!
		$sniff_verbs = array (
			"http://adlnet.gov/expapi/verbs/completed" => "completed",
			"http://adlnet.gov/expapi/verbs/passed" => "passed",
			"http://adlnet.gov/expapi/verbs/failed" => "failed",
			"http://adlnet.gov/expapi/verbs/terminated" =>	"terminated",
			"http://adlnet.gov/expapi/verbs/satisfied" =>	"satisfied"
		);
		$obj_id = "http://id.tincanapi.com/activity/tincan-prototypes/golf-example"; //ToDo: validate in xapi plugin classes
		if (isset($obj->verb) && isset($obj->actor) && isset($obj->object)) {
			$verb = $obj->verb->id;
			if (array_key_exists($verb, $sniff_verbs)) {
				// sniff verb?
				if ($obj->object->id === $obj_id) { // how to validate obj_id??
					//ToDo: set learning status in Plugin!
					//_log("id: " . $obj_id);
					//_log("verb: " . $verb);
				}
			}
		} 
	}
	// ToDo: Logging
	private function _log($txt) {
		file_put_contents("xapilog.txt",$txt."\n",FILE_APPEND);
	}
}
