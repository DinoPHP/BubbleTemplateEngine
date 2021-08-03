<?php

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

class Bubble_Test_Cache_AbstractCacheTest extends PHPUnit_Framework_TestCase
{
    public function testGetSetLogger()
    {
        $cache  = new CacheStub();
        $logger = new Bubble_Logger_StreamLogger('php://stdout');
        $cache->setLogger($logger);
        $this->assertSame($logger, $cache->getLogger());
    }

    /**
     * @expectedException Bubble_Exception_InvalidArgumentException
     */
    public function testSetLoggerThrowsExceptions()
    {
        $cache  = new CacheStub();
        $logger = new StdClass();
        $cache->setLogger($logger);
    }
}

class CacheStub extends Bubble_Cache_AbstractCache
{
    public function load($key)
    {
        // nada
    }

    public function cache($key, $value)
    {
        // nada
    }
}
