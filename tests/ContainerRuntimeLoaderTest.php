<?php

namespace Bubble\Tests;

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Bubble\RuntimeLoader\ContainerRuntimeLoader;

class ContainerRuntimeLoaderTest extends TestCase
{
    public function testLoad()
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())->method('has')->with('stdClass')->willReturn(true);
        $container->expects($this->once())->method('get')->with('stdClass')->willReturn(new \stdClass());

        $loader = new ContainerRuntimeLoader($container);

        $this->assertInstanceOf('stdClass', $loader->load('stdClass'));
    }

    public function testLoadUnknownRuntimeReturnsNull()
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())->method('has')->with('Foo');
        $container->expects($this->never())->method('get');

        $this->assertNull((new ContainerRuntimeLoader($container))->load('Foo'));
    }
}
