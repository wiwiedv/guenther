<?php

namespace wiwiedv;

use \Silex\ControllerProviderInterface;

abstract class AbstractGuentherControllerProvider implements ControllerProviderInterface
{
    protected $name = "";

    /**
     * @param string|null $name
     */
    protected function setName($name = null) {
        if (empty($name)) {
            $name = explode("\\", get_called_class());
            $name = end($name);
            $name = preg_replace("/ControllerProvider$/", "", $name);
        }
        $this->name = $name;
    }

    /**
     * @param bool $lowercase
     * @return string
     */
    public function getName($lowercase = true) {
        if (empty($this->name)) $this->setName();
        return $lowercase ? strtolower($this->name) : $this->name;
    }

    /**
     * @return array
     */
    public function getDBConfiguration() {
        return array(
            $this->getName() => array(
                "driver"   => "pdo_sqlite",
                "path"     => realpath("../../db/" . $this->getName() . ".sqlite3"),
            )
        );
    }

    /**
     * @return array
     */
    public function getSecurityConfiguration() {
        return array(
            $this->getName() => array(
                'pattern' => '^/api/' . $this->getName(),
                'http' => true,
                'users' => array(
                    // raw password is foo
                    'admin' => array('ROLE_ADMIN', '5FZ2Z8QIkA7UTZ4BYkoC+GsReLf569mSKDsfods6LYQ8t+a8EW9oaircfMpmaLbPBh4FOBiiFyLfuZmTSUwzZg=='),
                ),
            ),
        );
    }

    /**
     * @return string
     */
    function __toString() {
        return $this->getName();
    }
}