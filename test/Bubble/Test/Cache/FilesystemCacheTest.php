<?php

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

/**
 * @group functional
 */
class Bubble_Test_Cache_FilesystemCacheTest extends Bubble_Test_FunctionalTestCase
{
    public function testCacheGetNone()
    {
        $key = 'some key';
        $cache = new Bubble_Cache_FilesystemCache(self::$tempDir);
        $loaded = $cache->load($key);

        $this->assertFalse($loaded);
    }

    public function testCachePut()
    {
        $key = 'some key';
        $value = '<?php /* some value */';
        $cache = new Bubble_Cache_FilesystemCache(self::$tempDir);
        $cache->cache($key, $value);
        $loaded = $cache->load($key);

        $this->assertTrue($loaded);
    }
}
