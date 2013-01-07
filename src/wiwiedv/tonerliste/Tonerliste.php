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
        $toners = $this->db->fetchAll("SELECT * FROM toner");
        return new GuentherResponse($toners);
    }

    public function insertToner($id = null) {
        // POST /
        // POST /{tonerId}
        if (is_numeric($id)) {
            if ($toner = $this->getTonerById($id)) {
                // toner model exists, increment stock.
                $toner['stock'] += 1;
                $this->db->update("toner",
                                  array("stock" => $toner['stock']),
                                  array("id" => $toner['id']));
                $this->saveTransactionForToner($toner['id'], 1);
            } else {
                // requested non-existent tonerId
                return new GuentherResponse("Not found", 404);
            }
        } else {
            // new toner, need model name, etc.
            $model = $this->app['request']->get('model', null);
            if (is_null($model)) {
                return new GuentherResponse("Missing parameter 'model'", 400);
            }

            // store new model to db with initial stock 1
            $res = $this->db->insert("toner", array("model" => $model, "stock" => 1));

            if ($res === 1) {
                // on success reload resource
                $id = $this->db->lastInsertId();
                $toner = $this->getTonerById($id);
            } else {
                return new GuentherResponse("Could not store data", 500);
            }
        }

        // generate uri for new resource according to http spec
        $uri = $this->app['url_generator']->generate('specific_toner', array("tonerId" => $id));
        return new GuentherResponse($toner, 201, array("Location" => $uri));
    }

    public function showToner($id = null) {
        // GET /{tonerId}
        if ($toner = $this->getTonerById($id)) {
            $toner['transactions'] = $this->getTransactionsForToner($toner['id']);
            return new GuentherResponse($toner);
        }
        return new GuentherResponse("Not found", 404);
    }

    public function updateToner($id = null) {
        // PUT /{tonerId}
        return new GuentherResponse("Not implemented", 501);
    }

    public function removeToner($id = null) {
        // DELETE /{tonerId}
        return new GuentherResponse("Not implemented", 501);
    }


    private function getTonerById($id) {
        return $this->db->fetchAssoc("SELECT * FROM toner WHERE id = ?", array($id));
    }

    private function getTransactionsForToner($id) {
        return $this->db->fetchAll("SELECT * FROM transactions WHERE toner = ?", array($id));
    }

    private function saveTransactionForToner($id, $transactionType) {
        $token = $this->app['security']->getToken();
        if (is_null($token)) {
            $user = "anonymous";
        } else {
            $user = $token->getUser();
        }

        return 1 === $this->db->insert("transactions",
                                       array(
                                           "date" => time(),
                                           "type" => $transactionType,
                                           "name" => $user,
                                           "toner" => $id
                                       ));
    }
}