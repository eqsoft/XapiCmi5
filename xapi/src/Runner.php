<?php

namespace XapiProxy;

use DI\InvokerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Runner
{
    /** @var InvokerInterface */
    protected $invoker;
    protected $stack = [];
    protected $current = 0;
    public function __construct(InvokerInterface $invoker, $stack)
    {
        $this->invoker = $invoker;
        $this->stack = $stack;
    }
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response)
    {
        if (!isset($this->stack[$this->current]))
        {
            return $response;
        }
        $middleware = $this->stack[$this->current];
        $this->current++;
        return $this->invoker->call($middleware, [
            'request' => $request,
            'response' => $response,
            'next' => $this
        ]);
    }
}
