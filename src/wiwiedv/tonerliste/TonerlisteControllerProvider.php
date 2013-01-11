<?php

namespace wiwiedv\Tonerliste;

use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use wiwiedv\AbstractGuentherControllerProvider;
use wiwiedv\DoctrineConfigurationProviderInterface;
use wiwiedv\SecurityConfigurationProviderInterface;

use wiwiedv\Tonerliste\Tonerliste;

class TonerlisteControllerProvider
    extends AbstractGuentherControllerProvider
    implements DoctrineConfigurationProviderInterface, SecurityConfigurationProviderInterface
{
    /**
     * @param \Silex\Application $app
     * @return \Silex\ControllerCollection
     */
    public function connect(Application $app) {
        /** @var $controllers ControllerCollection */
        $controllers = $app['controllers_factory'];

        $tonerliste = new Tonerliste($app, $this->getName());

        $controllers->get("/", function(Request $request) use($app, $tonerliste) {
            return $tonerliste->listAllToners();
        })->bind($this->getName());

        $controllers->get("/toner/{tonerId}", function(Request $request, $tonerId) use($app, $tonerliste) {
            return $tonerliste->showToner($tonerId);
        })->bind($this->getName() . "_toner");

        $controllers->post("/toner", function(Request $request) use($app, $tonerliste) {
            $model = $request->get("model", null);
            return $tonerliste->registerToner($model);
        });

        $controllers->post("/toner/{tonerId}/depositions", function(Request $request, $tonerId) use($app, $tonerliste) {
            $reason = $request->get("reason", null);
            return $tonerliste->depositToner($tonerId, $reason);
        });

        $controllers->post("/toner/{tonerId}/withdrawals", function(Request $request, $tonerId) use($app, $tonerliste) {
            $reason = $request->get("reason", null);
            return $tonerliste->withdrawToner($tonerId, $reason);
        });

        $controllers->put("/toner/{tonerId}", function(Request $request, $tonerId) use($app, $tonerliste) {
            $model = $request->get("model", null);
            $hidden = $request->get("hidden", null);
            return $tonerliste->updateToner($tonerId, $model, $hidden);
        });

        $controllers->match("*", function() {
            return new Response("Method not implemented", 405);
        });

        return $controllers;
    }
}