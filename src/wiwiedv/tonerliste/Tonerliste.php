<?php

namespace wiwiedv\Tonerliste;

use Silex\Application;

use wiwiedv\GuentherResponse;

class Tonerliste
{
    private $app;
    private $db;

    public function __construct($app) {
        $this->app = $app;
        $this->db = $this->app['dbs']['tonerliste'];
    }

    public function listAllToners() {
        // GET /
        $toners = $this->db->fetchAssoc("SELECT * FROM toner");
        $response = new GuentherResponse($toners);
        return $response;
    }

    public function insertToner() {
        // POST /
        return new GuentherResponse("Not implemented.", 501);
    }

    public function showToner($id = null) {
        // GET /{tonerId}
        return new GuentherResponse("Not implemented.", 501);
    }

    public function updateToner($id = null) {
        // PUT /{tonerId}
        return new GuentherResponse("Not implemented.", 501);
    }

    public function deleteToner($id = null) {
        // DELETE /{tonerId}
        return new GuentherResponse("Not implemented.", 501);
    }
}