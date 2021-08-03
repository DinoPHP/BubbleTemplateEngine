<?php

namespace DinoPHP\Bubble\Extension;

use DinoPHP\Bubble\Engine;

/**
 * A common interface for extensions.
 */
interface ExtensionInterface
{
    public function register(Engine $engine);
}
