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

        // GET
        $controllers->get("/{type}", function(Request $request, $type) use($app, $tonerliste) {
            return $tonerliste->listAll($type);
        })->assert('type', '((toner|drum)s|)')
          ->value('type', '')
          ->bind("tonerliste");

        $controllers->get("/{type}/{id}", function(Request $request, $type, $id) use($app, $tonerliste) {
            return $tonerliste->getItem($id, $type);
        })->assert('type', '(toner|drum)')
          ->assert('id', '\d+')
          ->bind("tonerliste_get_item");

        // POST
        $controllers->post("/{type}", function(Request $request, $type) use($app, $tonerliste) {
            return $tonerliste->newItem(
                $request->get("name"),
                $type,
                $request->get("color"),
                $request->get("printer")
            );
        })->assert('type', '(toner|drum)s')
          ->bind('tonerliste_post_item');

        $controllers->post("/{type}/{id}/transactions", function(Request $request, $type, $id) use($app, $tonerliste) {
            return $tonerliste->newTransaction(
                $id,
                $request->get('action'),
                $request->get('reason')
            );
        })->assert('type', '(toner|drum)')
          ->assert('id', '\d+')
          ->bind("tonerliste_post_transaction");

        // PUT
        $controllers->put("/{type}/{id}", function(Request $request, $type, $id) use($app, $tonerliste) {
            return $tonerliste->modifyItem(
                $id,
                $request->get('name'),
                $request->get('color'),
                $request->get('printer'),
                $request->get('hidden'),
                $type
            );
        })->assert('type', '(toner|drum)')
          ->assert('id', '\d+')
          ->bind('tonerliste_put_item');

        // FALLBACK
        $controllers->match("*", function() {
            return new Response("Method not implemented", 405);
        });

        return $controllers;
    }
}