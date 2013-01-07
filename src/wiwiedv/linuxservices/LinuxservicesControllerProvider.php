<?php

namespace wiwiedv\Linuxservices;

use Silex\Application;
use Silex\ControllerCollection;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use wiwiedv\GuentherControllerProviderInterface;
use wiwiedv\DoctrineConfigurationProviderInterface;
use wiwiedv\SecurityConfigurationProviderInterface;

class LinuxservicesControllerProvider implements GuentherControllerProviderInterface, DoctrineConfigurationProviderInterface, SecurityConfigurationProviderInterface
{
    const NAME = "Linuxservices";

    public function connect(Application $app) {
        /** @var $controllers ControllerCollection */
        $controllers = $app['controllers_factory'];

        $app[strtolower(self::NAME)] = function($app) {
            return new Linuxservices($app);
        };

        $controllers->get("/", function() use($app) {
            return $app['linuxservices']->index();
        });

        return $controllers;
    }

    public function getName() {
        return self::NAME;
    }

    public function getDBConfiguration() {
        return array(
            strtolower(self::NAME) => array(
                "driver"   => "pdo_sqlite",
                "path"     => "/db/" . strtolower(self::NAME) . ".sqlite3",
            )
        );
    }

    public function getTwigConfiguration() {
        return array(
            __DIR__ . "/views"
        );
    }

    public function getSecurityConfiguration() {
        return array(
            'linuxservices' => array(
                'pattern' => '^/admin',
                'http' => true,
                'users' => array(
                    // raw password is foo
                    'admin' => array('ROLE_ADMIN', '5FZ2Z8QIkA7UTZ4BYkoC+GsReLf569mSKDsfods6LYQ8t+a8EW9oaircfMpmaLbPBh4FOBiiFyLfuZmTSUwzZg=='),
                ),
            ),
        );
    }
}