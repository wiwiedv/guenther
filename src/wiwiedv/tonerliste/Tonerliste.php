<?php

namespace wiwiedv\Tonerliste;

use Silex\Application;

use wiwiedv\AbstractGuentherModule;
use wiwiedv\GuentherResponse;

class Tonerliste
    extends AbstractGuentherModule
{

    public function listAllToners() {
        $toners = $this->db()->fetchAll("SELECT * FROM toner");
        return new GuentherResponse($toners);
    }

    public function showToner($id) {
        if (!($toner = $this->getTonerById($id))) {
            return new GuentherResponse("Not found", 404);
        }

        $toner['transactions'] = $this->getTransactionsForToner($toner['id']);
        return new GuentherResponse($toner);
    }

    public function registerToner($model) {
        if (empty($model)) {
            return new GuentherResponse("Missing parameter 'model'", 400);
        }

        // store new model to db with initial stock 0
        $res = $this->db()->insert("toner", array("model" => $model, "stock" => 0));
        if (false === $res) {
            return new GuentherResponse("Could not store data", 500);
        }

        $id = $this->db()->lastInsertId();
        $toner = $this->getTonerById($id);

        return new GuentherResponse($toner, 201, array("Location" => $this->getUrlForToner($id)));
    }

    public function depositToner($id, $reason) {
        if (empty($reason)) {
            return new GuentherResponse("Missing parameter 'reason'", 400);
        }

        if (!($toner = $this->getTonerById($id))) {
            return new GuentherResponse("Not found", 404);
        }

        // toner model exists, increment stock.
        $toner['stock'] += 1;
        $res = $this->db()->update("toner",
                                 array("stock" => $toner['stock']),
                                 array("id" => $toner['id']));
        $res = $res && $this->saveTransactionForToner($toner['id'], 1, $reason);

        if (false === $res) {
            return new GuentherResponse("Could not store data", 500);
        }

        return new GuentherResponse($toner, 201, array("Location" => $this->getUrlForToner($id)));
    }

    public function updateToner($id, $model, $hidden) {
        if (!($toner = $this->getTonerById($id))) {
            return new GuentherResponse("Not found", 404);
        }

        if (!empty($model)) {
            $toner['model'] = $model;
        }

        if (!is_null($hidden)) {
            $toner['hidden'] = (!!$hidden ? 1 : 0);
        }

        $res = $this->db()->update("toner",
                          array("model" => $toner['model'], "hidden" => intval($toner['hidden'])),
                          array("id" => $toner['id']));

        if (false === $res) {
            return new GuentherResponse("Could not store data", 500);
        }

        return new GuentherResponse($toner);
    }

    public function withdrawToner($id, $reason) {
        if (empty($reason)) {
            return new GuentherResponse("Missing parameter 'reason'", 400);
        }

        if (!($toner = $this->getTonerById($id))) {
            return new GuentherResponse("Not found", 404);
        }

        if ($toner['stock'] < 1) {
            return new GuentherResponse("Toner out of stock", 409);
        }

        $toner['stock'] -= 1;
        $res = $this->db()->update("toner",
                                 array("stock" => $toner['stock']),
                                 array("id" => $toner['id']));
        $res = $res && $this->saveTransactionForToner($toner['id'], 2, $reason);

        if (false === $res) {
            return new GuentherResponse("Could not store data", 500);
        }

        return new GuentherResponse($toner, 200);
    }


    private function getUrlForToner($id) {
        return $this->app['url_generator']->generate('tonerliste_toner', array("tonerId" => $id));
    }

    private function getTonerById($id) {
        return $this->db()->fetchAssoc("SELECT * FROM toner WHERE id = ?", array($id));
    }

    private function getTransactionsForToner($id) {
        return $this->db()->fetchAll("SELECT * FROM transactions WHERE toner = ?", array($id));
    }

    private function saveTransactionForToner($id, $transactionType, $reason) {
        $token = $this->app['security']->getToken();
        if (is_null($token)) {
            $user = "anonymous";
        } else {
            $user = $token->getUser();
        }

        return 1 === $this->db()->insert("transactions",
                                       array(
                                           "date" => time(),
                                           "type" => $transactionType,
                                           "name" => $user,
                                           "toner" => $id,
                                           "reason" => $reason
                                       ));
    }
}