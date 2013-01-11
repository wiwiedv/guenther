<?php
require_once __DIR__.'/../../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use wiwiedv\DoctrineConfigurationProviderInterface;
use wiwiedv\AbstractGuentherControllerProvider;
use wiwiedv\SecurityConfigurationProviderInterface;

$app = new Silex\Application();
$app['debug'] = true;
$modules = array();
$dbsOptions = array();
$firewallOptions = array();

// Instantiate ControllerProviders
// insert new providers here.
$controllerProviders = array(
    new \wiwiedv\Linuxservices\LinuxservicesControllerProvider(),
    new \wiwiedv\Tonerliste\TonerlisteControllerProvider()
);

$app->register(new \Silex\Provider\UrlGeneratorServiceProvider());

// Mount ControllerProviders
// also, collect configs according to implemented interfaces
foreach ($controllerProviders as $provider) {
    // doctrine configs?
    if ($provider instanceof DoctrineConfigurationProviderInterface) {
        $dbsOptions = array_merge($dbsOptions, $provider->getDBConfiguration());
    }

    // security configs? - not yet implemented.
    if ($provider instanceof SecurityConfigurationProviderInterface) {
        $firewallOptions = array_merge($firewallOptions, $provider->getSecurityConfiguration());
    }

    // twig paths and controllers
    if ($provider instanceof AbstractGuentherControllerProvider) {
        $app->mount("/" . $provider->getName(), $provider);
        $modules = array(
                       "name" => $provider->getName(false),
                       "url" => $app['url_generator']->generate($provider->getName())
                   );
    }
}

// Register ServiceProviders
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'dbs.options' => $dbsOptions
));
$app->register(new Silex\Provider\SecurityServiceProvider(), array(
    'security.firewalls' => $firewallOptions
));

$app['modules'] = $modules;

// Register middleware to handle json data in requests
$app->before(function (Request $request) {
    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : array());
    }
});

// Set route for module listing
$app->get('/modules', function() use($app) {
    return new \wiwiedv\GuentherResponse($app['modules']);
});

// Set default controller
$app->get("/", function() use($app) {
    return $app->redirect($app['url_generator']->generate('linuxservices'));
});

$app->run();
