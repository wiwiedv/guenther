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
}