<?php
namespace XapiProxy\Middleware;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Uri;
use GuzzleHttp\Psr7;

class ProxyMiddleware
{
	protected $guzzle_client;
	protected $upstream;
	protected $authorization;
    protected $request_options;

	public function __construct(Client $guzzle_client, $target, $request_options = [])
	{
		$this->guzzle_client = $guzzle_client;
		$this->upstream = $target["upstream"];
		$this->authorization = $target["authorization"];
		//$this->_log($this->upstream);
		//$this->_log($this->authorization);
		$this->request_options = $request_options;
	}
	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
	{
		$full_uri = $request->getUri();
		$serverParams = $request->getServerParams();
		$queryParams = $request->getQueryParams();
		//$this->_log(var_export($serverParams,TRUE));
		//$this->_log(var_export($queryParams,TRUE));
		$parts_reg = '/^(.*?xapiproxy\.php)(.+)/'; // ToDo: replace hard coded regex?
		preg_match($parts_reg,$full_uri,$cmd_parts);
        
		if (count($cmd_parts) === 3) { // should always
            try {
                $cmd = $cmd_parts[2];
                $upstream = $this->upstream.$cmd;
                $uri = new Uri($upstream);
                $changes = array(
                    'uri' => $uri,
                    'set_headers' => array('Cache-Control' => 'no-cache, no-store, must-revalidate', 'Authorization' => $this->authorization)
                );
                $request = \GuzzleHttp\Psr7\modify_request($request, $changes);
                if ($request->getMethod() === "GET" ) {
                    $this->_log($request->getUri());
                }
                $response = $this->guzzle_client->send($request,$this->request_options);
                return $next($request, $response);
            }
            catch (Exception $e) {
                return $next($request, $response);
            }
		}
		else {
			$this->_log("Wrong command parts!");
			header("HTTP/1.1 412 Wrong Request Parameter");
			echo "HTTP/1.1 412 Wrong Request Parameter";
			exit;
		}
	}
	// ToDo: Logging from Plugin
	private function _log($txt) {
		file_put_contents("xapilog.txt",$txt."\n",FILE_APPEND);
	}
}
