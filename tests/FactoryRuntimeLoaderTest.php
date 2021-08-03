<?php

namespace Bubble\Tests;

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

use PHPUnit\Framework\TestCase;
use Bubble\RuntimeLoader\FactoryRuntimeLoader;

class FactoryRuntimeLoaderTest extends TestCase
{
    public function testLoad()
    {
        $loader = new FactoryRuntimeLoader(['stdClass' => '\Bubble\Tests\getRuntime']);

        $this->assertInstanceOf('stdClass', $loader->load('stdClass'));
    }

    public function testLoadReturnsNullForUnmappedRuntime()
    {
        $loader = new FactoryRuntimeLoader();

        $this->assertNull($loader->load('stdClass'));
    }
}

function getRuntime()
{
    return new \stdClass();
}
