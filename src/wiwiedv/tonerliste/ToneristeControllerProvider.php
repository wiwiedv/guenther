<?php

namespace wiwiedv\Tonerliste;

use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use wiwiedv\GuentherControllerProviderInterface;
use wiwiedv\DoctrineConfigurationProviderInterface;

use wiwiedv\Tonerliste\Tonerliste;

class ToneristeControllerProvider implements GuentherControllerProviderInterface, DoctrineConfigurationProviderInterface
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

        $controllers->get("/", function(Request $request) use($app) {
            return $app['tonerliste']->listAllToners($request);
        });

        $controllers->post("/", function(Request $request) use($app) {
            return $app['tonerliste']->insertToner($request);
        });

        $controllers->get("/{tonerId}", function(Request $request, $tonerId) use($app) {
            return $app['tonerliste']->showToner(null, $tonerId);
        });

        $controllers->put("/{tonerId}", function(Request $request, $tonerId) use($app) {
            return $app['tonerliste']->updateToner($request, $tonerId);
        });

        $controllers->delete("/{tonerId}", function(Request $request, $tonerId) use($app) {
            return $app['tonerliste']->listAllToners(null, $tonerId);
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