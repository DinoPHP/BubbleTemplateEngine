<?php

namespace Bubble\Tests\Loader;

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

use PHPUnit\Framework\TestCase;
use Bubble\Error\LoaderError;
use Bubble\Loader\ArrayLoader;

class ArrayTest extends TestCase
{
    public function testGetSourceContextWhenTemplateDoesNotExist()
    {
        $this->expectException(LoaderError::class);

        $loader = new ArrayLoader([]);

        $loader->getSourceContext('foo');
    }

    public function testGetCacheKey()
    {
        $loader = new ArrayLoader(['foo' => 'bar']);

        $this->assertEquals('foo:bar', $loader->getCacheKey('foo'));
    }

    public function testGetCacheKeyWhenTemplateHasDuplicateContent()
    {
        $loader = new ArrayLoader([
            'foo' => 'bar',
            'baz' => 'bar',
        ]);

        $this->assertEquals('foo:bar', $loader->getCacheKey('foo'));
        $this->assertEquals('baz:bar', $loader->getCacheKey('baz'));
    }

    public function testGetCacheKeyIsProtectedFromEdgeCollisions()
    {
        $loader = new ArrayLoader([
            'foo__' => 'bar',
            'foo' => '__bar',
        ]);

        $this->assertEquals('foo__:bar', $loader->getCacheKey('foo__'));
        $this->assertEquals('foo:__bar', $loader->getCacheKey('foo'));
    }

    public function testGetCacheKeyWhenTemplateDoesNotExist()
    {
        $this->expectException(LoaderError::class);

        $loader = new ArrayLoader([]);

        $loader->getCacheKey('foo');
    }

    public function testSetTemplate()
    {
        $loader = new ArrayLoader([]);
        $loader->setTemplate('foo', 'bar');

        $this->assertEquals('bar', $loader->getSourceContext('foo')->getCode());
    }

    public function testIsFresh()
    {
        $loader = new ArrayLoader(['foo' => 'bar']);
        $this->assertTrue($loader->isFresh('foo', time()));
    }

    public function testIsFreshWhenTemplateDoesNotExist()
    {
        $this->expectException(LoaderError::class);

        $loader = new ArrayLoader([]);

        $loader->isFresh('foo', time());
    }
}
