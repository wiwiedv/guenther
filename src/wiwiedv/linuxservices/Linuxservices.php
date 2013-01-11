<?php

namespace wiwiedv\Linuxservices;

use Silex\Application;

use wiwiedv\GuentherResponse;
use wiwiedv\AbstractGuentherModule;

class Linuxservices
    extends AbstractGuentherModule
{
    public function index() {
        // GET /
        return new GuentherResponse("Linuxservices!");
    }

    public function allModules() {
        return new GuentherResponse($this->app['modules']);
    }

    public function secTest() {
        return new GuentherResponse(print_r($this->user(), true));
    }

    private function user() {
        if (null === $token = $this->app['security']->getToken()) {
            return null;
        }

        if (!is_object($user = $token->getUser())) {
            return null;
        }

        return $user;
    }
}