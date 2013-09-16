<?php

namespace Bangpound\Guzzle\Proxy;

use Guzzle\Common\Collection;
use Guzzle\Http\Client;
use Guzzle\Http\Exception\BadResponseException;
use Guzzle\Http\Message\RequestFactory;
use Guzzle\Http\Url;
use Guzzle\Parser\ParserRegistry;
use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guzzle Proxy Provider.
 */
class GuzzleProxyProvider implements ServiceProviderInterface, ControllerProviderInterface
{

    /**
     * Registers services on the given app.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Application $app An Application instance
     */
    public function register(Application $app)
    {
        $app['proxy.mount_prefix'] = '/proxy';
        $app['proxy.endpoints'] = array();
    }

    /**
     * Bootstraps the application.
     *
     * This method is called after all services are registered
     * and should be used for "dynamic" configuration (whenever
     * a service must be requested).
     */
    public function boot(Application $app)
    {
        $app->mount($app['proxy.mount_prefix'], $this->connect($app));
    }

    /**
     * Returns routes to connect to the given application.
     *
     * @param Application $app An Application instance
     *
     * @return ControllerCollection A ControllerCollection instance
     */
    public function connect(Application $app)
    {
        /** @var $controllers \Silex\ControllerCollection */
        $controllers = $app['controllers_factory'];

        $controllers->match('/{endpoint}/{path}', function (Request $request, $endpoint, $path) use ($app) {

            // URL of the proxied service is extracted from the options. The requested path
            // and query string are attached.
            $url = Url::factory($endpoint['host']);
            $url->addPath($path)
                ->setQuery($request->getQueryString());

            $client = new Client();
            $response = $client->createRequest($request->getMethod(), $url, null, $request->getContent())->send();
            return new Response($response->getBody(true), $response->getStatusCode(), $response->getHeaders()->toArray());
        })
            ->assert('endpoint', implode('|', array_keys($app['proxy.endpoints'])))
            ->assert('path', '.*?')

            ->convert('endpoint', function ($endpoint) use ($app) {
                return $app['proxy.endpoints'][$endpoint];
            })
            ->bind('proxy')
        ;

        return $controllers;
    }
}
