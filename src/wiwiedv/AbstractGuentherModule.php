<?php

namespace wiwiedv;

use Silex\Application;
use Doctrine\DBAL\Connection;

abstract class AbstractGuentherModule
{
    /** @var $app Application */
    protected $app;

    /** @var $db Connection */
    private $db;

    /** @var $name string */
    protected $name;

    /**
     * @param \Silex\Application $app
     * @param string $name
     */
    public function __construct(Application $app, $name) {
        $this->app = $app;
        $this->name = $name;
    }

    /**
     * @return \Doctrine\DBAL\Connection
     */
    protected function db() {
        if (empty($this->db)) {
            $this->db = $this->app['dbs'][$this->name];
        }

        return $this->db;
    }
}