<?php
namespace XapiProxy\Middleware;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Uri;
use GuzzleHttp\Psr7;

class ResponseFilterXapi
{
    protected $guzzle_client;
	public function __construct(Client $guzzle_client, $target, $request_options = [])
	{
		$this->guzzle_client = $guzzle_client;
	}
    
	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
	{
        try {
            $headers = $response->getHeaders();
            if (array_key_exists('Transfer-Encoding', $headers) && $headers['Transfer-Encoding'][0] == "chunked") {
                $body = $response->getBody();
                $status = $response->getStatusCode();
                unset($headers['Transfer-Encoding']);
                $headers['Content-Length'] = array(strlen($body));
                $response2 = new \GuzzleHttp\Psr7\Response($status,$headers,$body);
                return $next($request, $response2);
            }
            else {
                return $next($request, $response);
            }
        }
        catch (Exception $e) {
            return $next($request, $response);
        }
    }
	// ToDo: Logging from Plugin
	private function _log($txt) {
		file_put_contents("xapilog.txt",$txt."\n",FILE_APPEND);
	}
}
