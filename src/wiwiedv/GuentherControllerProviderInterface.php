<?php

namespace wiwiedv;

use Silex\ControllerProviderInterface;
use Silex\Application;

interface GuentherControllerProviderInterface extends ControllerProviderInterface
{
    public function getName();
}