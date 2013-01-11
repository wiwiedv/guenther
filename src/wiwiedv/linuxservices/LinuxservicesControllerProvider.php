<?php

namespace wiwiedv\Linuxservices;

use Silex\Application;
use Silex\ControllerCollection;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use wiwiedv\AbstractGuentherControllerProvider;
use wiwiedv\DoctrineConfigurationProviderInterface;
use wiwiedv\SecurityConfigurationProviderInterface;

use wiwiedv\Linuxservices\Linuxservices;

class LinuxservicesControllerProvider
    extends AbstractGuentherControllerProvider
    implements DoctrineConfigurationProviderInterface//, SecurityConfigurationProviderInterface
{
    const NAME = "Linuxservices";

    public function connect(Application $app) {
        /** @var $controllers ControllerCollection */
        $controllers = $app['controllers_factory'];

        $linuxservices = new Linuxservices($app, $this->getName());

        $controllers->get("/", function() use($app, $linuxservices) {
            return $linuxservices->index();
        })->bind($this->getName());

        $controllers->get("/modules", function() use($app, $linuxservices) {
            return $linuxservices->allModules();
        });

        return $controllers;
    }
}