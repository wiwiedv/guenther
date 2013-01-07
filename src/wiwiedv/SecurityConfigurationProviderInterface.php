<?php

namespace wiwiedv;

use Silex\ControllerProviderInterface;
use Silex\Application;

interface SecurityConfigurationProviderInterface
{
    public function getSecurityConfiguration();
}