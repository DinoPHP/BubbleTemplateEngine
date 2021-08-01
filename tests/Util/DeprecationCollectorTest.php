<?php

namespace Bubble\Tests\Util;

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

use PHPUnit\Framework\TestCase;
use Bubble\Environment;
use Bubble\Loader\LoaderInterface;
use Bubble\BubbleFunction;
use Bubble\Util\DeprecationCollector;

class DeprecationCollectorTest extends TestCase
{
    /**
     * @requires PHP 5.3
     */
    public function testCollect()
    {
        $bubble = new Environment($this->createMock(LoaderInterface::class));
        $bubble->addFunction(new BubbleFunction('deprec', [$this, 'deprec'], ['deprecated' => '1.1']));

        $collector = new DeprecationCollector($bubble);
        $deprecations = $collector->collect(new Bubble_Tests_Util_Iterator());

        $this->assertEquals(['Bubble Function "deprec" is deprecated since version 1.1 in deprec.bubble at line 1.'], $deprecations);
    }

    public function deprec()
    {
    }
}

class Bubble_Tests_Util_Iterator implements \IteratorAggregate
{
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator([
            'ok.bubble' => '{{ foo }}',
            'deprec.bubble' => '{{ deprec("foo") }}',
        ]);
    }
}
