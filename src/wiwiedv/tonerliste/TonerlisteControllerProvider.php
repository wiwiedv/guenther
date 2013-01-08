<?php

namespace wiwiedv\Tonerliste;

use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use wiwiedv\GuentherControllerProviderInterface;
use wiwiedv\DoctrineConfigurationProviderInterface;

use wiwiedv\Tonerliste\Tonerliste;

class TonerlisteControllerProvider implements GuentherControllerProviderInterface, DoctrineConfigurationProviderInterface
{
    const NAME = "Tonerliste";

    /**
     * @param \Silex\Application $app
     * @return \Silex\ControllerCollection
     */
    public function connect(Application $app) {
        /** @var $controllers ControllerCollection */
        $controllers = $app['controllers_factory'];

        $app[strtolower(self::NAME)] = function($app) {
            return new Tonerliste($app);
        };

        $controllers->get("/", function() use($app) {
            return $app['tonerliste']->listAllToners();
        });

        $controllers->post("/", function() use($app) {
            $model = $app['request']->get("model", null);
            return $app['tonerliste']->registerToner($model);
        });

        $controllers->get("/{tonerId}", function($tonerId) use($app) {
            return $app['tonerliste']->showToner($tonerId);
        })->bind("specific_toner");

        $controllers->post("/{tonerId}", function($tonerId) use($app) {
            $reason = $app['request']->get("reason", null);
            return $app['tonerliste']->depositToner($tonerId, $reason);
        });

        $controllers->delete("/{tonerId}", function($tonerId) use($app) {
            $reason = $app['request']->get("reason", null);
            return $app['tonerliste']->listAllToners($tonerId, $reason);
        });

        return $controllers;
    }

    /**
     * @return array
     */
    public function getDBConfiguration() {
        return array(
            strtolower(self::NAME) => array(
                "driver"   => "pdo_sqlite",
                "path"     => realpath("../db/" . strtolower(self::NAME) . ".sqlite3"),
            )
        );
    }

    /**
     * @return string
     */
    public function getName() {
        return self::NAME;
    }

    /**
     * @return array
     */
    public function getTwigConfiguration() {
        return array(
            __DIR__ . "/views"
        );
    }
}