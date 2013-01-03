<?php
require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use wiwiedv\DoctrineConfigurationProviderInterface;
use wiwiedv\GuentherControllerProviderInterface;

$app = new Silex\Application();
$app['debug'] = true;
$dbsOptions = array();

// Instantiate ControllerProviders
// insert new providers here.
$controllerProviders = array(
    new \wiwiedv\Linuxservices\LinuxservicesControllerProvider()
);

// Mount ControllerProviders and collect Doctrine-configs
foreach ($controllerProviders as $provider) {
    if ($provider instanceof DoctrineConfigurationProviderInterface) {
        $connectionConfigurations = $provider->getConnectionConfiguration();
        foreach ($connectionConfigurations as $ccName => $ccData) {
            $dbsOptions[$ccName] = $ccData;
        }
    }
    if ($provider instanceof GuentherControllerProviderInterface) {
        $app->mount("/" . strtolower($provider->getName()), $provider);
    }
}

// Register ServiceProviders
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'dbs.options' => $dbsOptions
));
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/templates',
));

// Register Middleware to handle json data in requests
$app->before(function (Request $request) {
    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : array());
    }
});

// Set default controller
$app->get("/", function() use($app) {
    return $app->redirect("/linuxservices");
});

$app->run();
