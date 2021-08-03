<?php

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

/**
 * @group unit
 */
class Bubble_Test_AutoloaderTest extends PHPUnit_Framework_TestCase
{
    public function testRegister()
    {
        $loader = Bubble_Autoloader::register();
        $this->assertTrue(spl_autoload_unregister(array($loader, 'autoload')));
    }

    public function testAutoloader()
    {
        $loader = new Bubble_Autoloader(dirname(__FILE__) . '/../../fixtures/autoloader');

        $this->assertNull($loader->autoload('NonBubbleClass'));
        $this->assertFalse(class_exists('NonBubbleClass'));

        $loader->autoload('Bubble_Foo');
        $this->assertTrue(class_exists('Bubble_Foo'));

        $loader->autoload('\Bubble_Bar');
        $this->assertTrue(class_exists('Bubble_Bar'));
    }

    /**
     * Test that the autoloader won't register multiple times.
     */
    public function testRegisterMultiple()
    {
        $numLoaders = count(spl_autoload_functions());

        Bubble_Autoloader::register();
        Bubble_Autoloader::register();

        $expectedNumLoaders = $numLoaders + 1;

        $this->assertCount($expectedNumLoaders, spl_autoload_functions());
    }
}
