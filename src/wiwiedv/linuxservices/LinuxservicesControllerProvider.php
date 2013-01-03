<?php

namespace wiwiedv\Linuxservices;

use Silex\Application;
use Silex\ControllerCollection;

use wiwiedv\GuentherControllerProviderInterface;
use wiwiedv\DoctrineConfigurationProviderInterface;

class LinuxservicesControllerProvider implements GuentherControllerProviderInterface, DoctrineConfigurationProviderInterface
{
    const NAME = "Linuxservices";

    public function connect(Application $app) {
        /** @var $controllers ControllerCollection */
        $controllers = $app['controllers_factory'];

        $controllers->get("/", function() use($app) {
            return $app->json(true);
        });

        return $controllers;
    }

    public function getName() {
        return self::NAME;
    }

    public function getConnectionConfiguration() {
        return array(
            strtolower(self::NAME) => array(
                "driver"   => "pdo_sqlite",
                "path"     => "/db/" . strtolower(self::NAME) . ".sqlite3",
            )
        );
    }
}