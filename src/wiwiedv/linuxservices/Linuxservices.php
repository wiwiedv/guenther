<?php

namespace wiwiedv\Linuxservices;

use Silex\Application;

use wiwiedv\GuentherResponse;

class Linuxservices
{
    private $app;
    private $db;

    public function __construct($app) {
        $this->app = $app;
        $this->db = $this->app['dbs']['linuxservices'];
    }

    public function index() {
        // GET /
        return new GuentherResponse("Linuxservices!");
    }
}