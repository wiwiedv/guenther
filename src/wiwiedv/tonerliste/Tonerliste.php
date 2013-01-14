<?php

namespace wiwiedv\Tonerliste;

use Silex\Application;

use wiwiedv\AbstractGuentherModule;
use wiwiedv\GuentherResponse;

class Tonerliste
    extends AbstractGuentherModule
{
    const VALID_STRING = '/[\d\w\040\.\-_+\/]{2,}/i';
    const VALID_NUMBER = '/\d+/';

    private $itemTypes = ['toner' => 1, 'drum' => 2];
    private $colors = ['black' => 1, 'magenta' => 2, 'cyan' => 3, 'yellow' => 4, 'universal' => 5];
    private $actions = ['deposit' => 1, 'withdraw' => 2, 'create' => 3, 'modify' => 4];

    /**
     * @param null $type
     * @return \wiwiedv\GuentherResponse
     */
    public function listAll($type = null) {
        $items = $this->fetchFromItems(null, $type);

        if ($items) {
            return new GuentherResponse($items);
        } else {
            return new GuentherResponse("Error retrieving data", 500);
        }
    }

    /**
     * @param $id
     * @param null $type
     * @return \wiwiedv\GuentherResponse
     */
    public function getItem($id, $type = null) {
        $item = $this->fetchFromItems($id, $type, false);

        if ($item && isset($item['id'])) {
            $item['transactions'] = $this->fetchTransactionsForItem($item['id']);
            return new GuentherResponse($item);
        } else {
            return new GuentherResponse("Not found", 404);
        }
    }

    /**
     * @param $name
     * @param $type
     * @param $color
     * @param $printer
     * @return \wiwiedv\GuentherResponse
     */
    public function newItem($name, $type, $color, $printer) {
        $newItemData = [];

        $type = $this->normalizeItemType($type);
        $color = $this->normalizeColor($color);

        $validitySettings = [
            'name'    => self::VALID_STRING,
            'type'    => self::VALID_NUMBER,
            'color'   => self::VALID_NUMBER,
            'printer' => self::VALID_STRING,
        ];
        foreach ($validitySettings as $argname => $target) {
            if ($this->validate($$argname, $target)) {
                $newItemData[$argname] = $$argname;
            } else {
                return new GuentherResponse("Missing or wrong value for parameter '$argname'", 400);
            }
        }

        if ($this->db()->insert("items", $newItemData) !== 1) {
            return new GuentherResponse("Could not persist new item", 500);
        }

        $id = intval($this->db()->lastInsertId());

        $transaction = $this->storeTransaction($id, "create", "new");
        if ($transaction instanceof GuentherResponse) {
            return $transaction;
        } elseif (false === $transaction) {
            return new GuentherResponse("Could not store transaction", 500);
        }

        $response = $this->getItem($id, $type);
        if ($response->getStatusCode() == 200) {
            $response->setStatusCode(201)
                     ->headers->add(['Location' => $this->getUrlForItem($id, $type)]);
        }
        return $response;
    }

    /**
     * @param $id
     * @param $name
     * @param $color
     * @param $printer
     * @param $hidden
     * @param null $type
     * @return \wiwiedv\GuentherResponse
     */
    public function modifyItem($id, $name, $color, $printer, $hidden, $type = null) {
        $hidden = !!$hidden;
        $type = $this->normalizeItemType($type);
        $color = $this->normalizeColor($color);
        $validitySettings = [
            'id'      => self::VALID_NUMBER,
            'name'    => self::VALID_STRING,
            'printer' => self::VALID_STRING,
            'color'   => self::VALID_NUMBER,
            'hidden'  => [true, false],
            'type'    => self::VALID_NUMBER
        ];
        foreach ($validitySettings as $argname => $target) {
            if (!$this->validate($$argname, $target)) {
                return new GuentherResponse("Missing or wrong value for parameter '$argname'", 400);
            }
        }

        $item = $this->fetchFromItems($id, $type, false);
        if (!$item) {
            return new GuentherResponse("Item not found", 404);
        } else {
            $item['name'] = $name;
            $item['printer'] = $printer;
            $item['hidden'] = $hidden;
            $item['color'] = $color;
            if (!$this->db()->update('items', $item, ['id' => $id])) {
                return new GuentherResponse("Could not store data", 500);
            }
            $transaction = $this->storeTransaction($id, "modify", "change");
            if ($transaction instanceof GuentherResponse) {
                return $transaction;
            } elseif (false === $transaction) {
                return new GuentherResponse("Could not store transaction", 500);
            }
        }

        return new GuentherResponse($item);
    }

    /**
     * @param $item
     * @param $action
     * @param $reason
     * @return \wiwiedv\GuentherResponse
     */
    public function newTransaction($item, $action, $reason) {
        $action = $this->normalizeAction($action);

        if ($action == $this->actions['deposit'] ||
            $action == $this->actions['withdraw']) {
            $transaction = $this->storeTransaction($item, $action, $reason);
            if ($transaction instanceof GuentherResponse) {
                return $transaction;
            } elseif (false === $transaction) {
                return new GuentherResponse("Could not store transaction", 500);
            }
        } else {
            return new GuentherResponse("Only depositions and withdrawals allowed", 403);
        }

        return new GuentherResponse("", 204);
    }

    /**
     * @param $item
     * @param $action
     * @param $reason
     * @return bool|\wiwiedv\GuentherResponse
     */
    private function storeTransaction($item, $action, $reason) {
        $action = $this->normalizeAction($action);
        $validitySettings = [
            'item'   => self::VALID_NUMBER,
            'action' => self::VALID_NUMBER,
            'reason' => self::VALID_STRING
        ];
        foreach ($validitySettings as $argname => $target) {
            if (!$this->validate($$argname, $target)) {
                return new GuentherResponse("Missing or wrong value for parameter '$argname'", 400);
            }
        }

        $item = $this->fetchFromItems($item, null, false);
        if (!$item) {
            return new GuentherResponse("Item not found", 404);
        }

        $res = true;

        if ($action == $this->actions['deposit'] ||
            $action == $this->actions['withdraw']) {

            if ($item['stock'] < 1) {
                return new GuentherResponse("Toner out of stock", 409);
            }
            $newStock = $item['stock'] + ($action == $this->actions['deposit'] ? 1 : -1);
            $res = $res && $this->db()->update('item', ['stock' => $newStock], ['id' => $item['id']]);
        }

        $newTransactionData = [
            'date'   => time(),
            'action' => $action,
            'user'   => $this->getUser(),
            'item'   => $item,
            'reason' => $reason
        ];
        $res = $res && $this->db()->insert('transactions', $newTransactionData);

        return $res;
    }

    /**
     * @param $id
     * @return array
     */
    private function fetchTransactionsForItem($id) {
        return $this->db()->fetchAll("SELECT * FROM `transactions` WHERE `item` = ?", array($id));
    }

    /**
     * @param $id
     * @param null $itemType
     * @param bool $fetchAll
     * @return array
     */
    private function fetchFromItems($id = null, $itemType = null, $fetchAll = true) {
        $sql = "SELECT * FROM `items`";
        $params = [];

        $itemType = $this->normalizeItemType($itemType);

        if (!empty($id) || !empty($itemType)) {
            $sql .= " WHERE 1=1";

            if (!empty($id)) {
                $sql .= " AND `id` = ?";
                $params[] = $id;
            }

            if (!empty($id)) {
                $sql .= " AND `type` = ?";
                $params[] = $itemType;
            }
        }

        if ($fetchAll) {
            return $this->db()->fetchAll($sql, $params);
        } else {
            return $this->db()->fetchAssoc($sql, $params);
        }
    }

    private function normalizeColor($color) {
        return $this->normalizeAnything($color, $this->colors);
    }

    private function normalizeAction($action) {
        return $this->normalizeAnything($action, $this->actions);
    }

    private function normalizeItemType($itemType) {
        return $this->normalizeAnything($itemType, $this->itemTypes);
    }

    private function normalizeAnything($thing, $any) {
        if (is_null($thing)) {
            return null;
        } elseif (is_numeric($thing) && in_array($thing, $any)){
            return $thing;
        } elseif (isset($any[rtrim(strtolower($thing), 's')])) {
            return $any[rtrim(strtolower($thing), 's')];
        }
        return false;
    }

    private function getUrlForItem($id, $route) {
        return $this->app['url_generator']->generate($route, array("id" => $id));
    }

    /**
     * @return string
     */
    private function getUser() {
        $token = $this->app['security']->getToken();
        if (is_null($token)) {
            $user = "anonymous";
        } else {
            $user = $token->getUser();
        }
        return $user;
    }

    /**
     * @param mixed $input
     * @param string|array $target
     * @return bool
     */
    private function validate($input, $target) {
        if (is_array($target)) {
            return in_array($input, $target);
        } else {
            return 1 === preg_match($target, $input);
        }
    }
}