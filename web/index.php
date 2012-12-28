<?php
require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();
$app['debug'] = true;

// definitions
$app->get("/x", function(){
	return "Hallo";
});

$app->run();