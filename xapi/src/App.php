<?php

namespace XapiProxy;

use DI\Container;
use DI\ContainerBuilder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class App
{
    /** @var Container */
    protected $container;
    public function __construct()
    {
        $builder = new ContainerBuilder;
        $builder->addDefinitions(__DIR__.'/config.php');
        $this->container = $builder->build();
    }
    public function run(ServerRequestInterface $request, ResponseInterface $response)
    {
        $runner = $this->container->get(Runner::class);
        return $runner($request, $response);
    }
}
