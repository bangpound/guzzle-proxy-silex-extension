Guzzle Proxy Silex Service Provider
===================================

The GuzzleProxyProvider provides a web proxy service to Silex to defined endpoints. Use
with [SecurityServiceProvider][1] to grant access to authenticated users.

Configuration
-------------

    $app->register(new Bangpound\Guzzle\Proxy\GuzzleProxyProvider(), array(
        'proxy.endpoints' => array(
            'elasticsearch' => array(
                'host' => 'http://localhost:9200/',
            ),
            'couchdb' => array(
                'host' => 'http://localhost:5984/',
            ),
        ),
    ));


[1]: http://silex.sensiolabs.org/doc/providers/security.html