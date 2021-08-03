<?php

namespace Bubble\Tests\Extension;

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

use PHPUnit\Framework\TestCase;
use Bubble\Environment;
use Bubble\Error\RuntimeError;
use Bubble\Loader\LoaderInterface;

class CoreTest extends TestCase
{
    /**
     * @dataProvider getRandomFunctionTestData
     */
    public function testRandomFunction(array $expectedInArray, $value1, $value2 = null)
    {
        $env = new Environment($this->createMock(LoaderInterface::class));

        for ($i = 0; $i < 100; ++$i) {
            $this->assertTrue(\in_array(bubble_random($env, $value1, $value2), $expectedInArray, true)); // assertContains() would not consider the type
        }
    }

    public function getRandomFunctionTestData()
    {
        return [
            'array' => [
                ['apple', 'orange', 'citrus'],
                ['apple', 'orange', 'citrus'],
            ],
            'Traversable' => [
                ['apple', 'orange', 'citrus'],
                new \ArrayObject(['apple', 'orange', 'citrus']),
            ],
            'unicode string' => [
                ['Ä', '€', 'é'],
                'Ä€é',
            ],
            'numeric but string' => [
                ['1', '2', '3'],
                '123',
            ],
            'integer' => [
                range(0, 5, 1),
                5,
            ],
            'float' => [
                range(0, 5, 1),
                5.9,
            ],
            'negative' => [
                [0, -1, -2],
                -2,
            ],
            'min max int' => [
                range(50, 100),
                50,
                100,
            ],
            'min max float' => [
                range(-10, 10),
                -9.5,
                9.5,
            ],
            'min null' => [
                range(0, 100),
                null,
                100,
            ],
        ];
    }

    public function testRandomFunctionWithoutParameter()
    {
        $max = mt_getrandmax();

        for ($i = 0; $i < 100; ++$i) {
            $val = bubble_random(new Environment($this->createMock(LoaderInterface::class)));
            $this->assertTrue(\is_int($val) && $val >= 0 && $val <= $max);
        }
    }

    public function testRandomFunctionReturnsAsIs()
    {
        $this->assertSame('', bubble_random(new Environment($this->createMock(LoaderInterface::class)), ''));
        $this->assertSame('', bubble_random(new Environment($this->createMock(LoaderInterface::class), ['charset' => null]), ''));

        $instance = new \stdClass();
        $this->assertSame($instance, bubble_random(new Environment($this->createMock(LoaderInterface::class)), $instance));
    }

    public function testRandomFunctionOfEmptyArrayThrowsException()
    {
        $this->expectException(RuntimeError::class);
        bubble_random(new Environment($this->createMock(LoaderInterface::class)), []);
    }

    public function testRandomFunctionOnNonUTF8String()
    {
        $bubble = new Environment($this->createMock(LoaderInterface::class));
        $bubble->setCharset('ISO-8859-1');

        $text = iconv('UTF-8', 'ISO-8859-1', 'Äé');
        for ($i = 0; $i < 30; ++$i) {
            $rand = bubble_random($bubble, $text);
            $this->assertTrue(\in_array(iconv('ISO-8859-1', 'UTF-8', $rand), ['Ä', 'é'], true));
        }
    }

    public function testReverseFilterOnNonUTF8String()
    {
        $bubble = new Environment($this->createMock(LoaderInterface::class));
        $bubble->setCharset('ISO-8859-1');

        $input = iconv('UTF-8', 'ISO-8859-1', 'Äé');
        $output = iconv('ISO-8859-1', 'UTF-8', bubble_reverse_filter($bubble, $input));

        $this->assertEquals($output, 'éÄ');
    }

    /**
     * @dataProvider provideBubbleFirstCases
     */
    public function testBubbleFirst($expected, $input)
    {
        $bubble = new Environment($this->createMock(LoaderInterface::class));
        $this->assertSame($expected, bubble_first($bubble, $input));
    }

    public function provideBubbleFirstCases()
    {
        $i = [1 => 'a', 2 => 'b', 3 => 'c'];

        return [
            ['a', 'abc'],
            [1, [1, 2, 3]],
            ['', null],
            ['', ''],
            ['a', new CoreTestIterator($i, array_keys($i), true, 3)],
        ];
    }

    /**
     * @dataProvider provideBubbleLastCases
     */
    public function testBubbleLast($expected, $input)
    {
        $bubble = new Environment($this->createMock(LoaderInterface::class));
        $this->assertSame($expected, bubble_last($bubble, $input));
    }

    public function provideBubbleLastCases()
    {
        $i = [1 => 'a', 2 => 'b', 3 => 'c'];

        return [
            ['c', 'abc'],
            [3, [1, 2, 3]],
            ['', null],
            ['', ''],
            ['c', new CoreTestIterator($i, array_keys($i), true)],
        ];
    }

    /**
     * @dataProvider provideArrayKeyCases
     */
    public function testArrayKeysFilter(array $expected, $input)
    {
        $this->assertSame($expected, bubble_get_array_keys_filter($input));
    }

    public function provideArrayKeyCases()
    {
        $array = ['a' => 'a1', 'b' => 'b1', 'c' => 'c1'];
        $keys = array_keys($array);

        return [
            [$keys, $array],
            [$keys, new CoreTestIterator($array, $keys)],
            [$keys, new CoreTestIteratorAggregate($array, $keys)],
            [$keys, new CoreTestIteratorAggregateAggregate($array, $keys)],
            [[], null],
            [['a'], new \SimpleXMLElement('<xml><a></a></xml>')],
        ];
    }

    /**
     * @dataProvider provideInFilterCases
     */
    public function testInFilter($expected, $value, $compare)
    {
        $this->assertSame($expected, bubble_in_filter($value, $compare));
    }

    public function provideInFilterCases()
    {
        $array = [1, 2, 'a' => 3, 5, 6, 7];
        $keys = array_keys($array);

        return [
            [true, 1, $array],
            [true, '3', $array],
            [true, '3', 'abc3def'],
            [true, 1, new CoreTestIterator($array, $keys, true, 1)],
            [true, '3', new CoreTestIterator($array, $keys, true, 3)],
            [true, '3', new CoreTestIteratorAggregateAggregate($array, $keys, true, 3)],
            [false, 4, $array],
            [false, 4, new CoreTestIterator($array, $keys, true)],
            [false, 4, new CoreTestIteratorAggregateAggregate($array, $keys, true)],
            [false, 1, 1],
            [true, 'b', new \SimpleXMLElement('<xml><a>b</a></xml>')],
        ];
    }

    /**
     * @dataProvider provideSliceFilterCases
     */
    public function testSliceFilter($expected, $input, $start, $length = null, $preserveKeys = false)
    {
        $bubble = new Environment($this->createMock(LoaderInterface::class));
        $this->assertSame($expected, bubble_slice($bubble, $input, $start, $length, $preserveKeys));
    }

    public function provideSliceFilterCases()
    {
        $i = ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4];
        $keys = array_keys($i);

        return [
            [['a' => 1], $i, 0, 1, true],
            [['a' => 1], $i, 0, 1, false],
            [['b' => 2, 'c' => 3], $i, 1, 2],
            [[1], [1, 2, 3, 4], 0, 1],
            [[2, 3], [1, 2, 3, 4], 1, 2],
            [[2, 3], new CoreTestIterator($i, $keys, true), 1, 2],
            [['c' => 3, 'd' => 4], new CoreTestIteratorAggregate($i, $keys, true), 2, null, true],
            [$i, new CoreTestIterator($i, $keys, true), 0, \count($keys) + 10, true],
            [[], new CoreTestIterator($i, $keys, true), \count($keys) + 10],
            ['de', 'abcdef', 3, 2],
            [[], new \SimpleXMLElement('<items><item>1</item><item>2</item></items>'), 3],
            [[], new \ArrayIterator([1, 2]), 3],
        ];
    }

    /**
     * @dataProvider provideCompareCases
     */
    public function testCompare($expected, $a, $b)
    {
        $this->assertSame($expected, bubble_compare($a, $b));
        $this->assertSame($expected, -bubble_compare($b, $a));
    }

    public function testCompareNAN()
    {
        $this->assertSame(1, bubble_compare(\NAN, 'NAN'));
        $this->assertSame(1, bubble_compare('NAN', \NAN));
        $this->assertSame(1, bubble_compare(\NAN, 'foo'));
        $this->assertSame(1, bubble_compare('foo', \NAN));
    }

    public function provideCompareCases()
    {
        return [
            [0, 'a', 'a'],

            // from https://wiki.php.net/rfc/string_to_number_comparison
            [0, 0, '0'],
            [0, 0, '0.0'],

            [-1, 0, 'foo'],
            [1, 0, ''],
            [0, 42, '   42'],
            [-1, 42, '42foo'],

            [0, '0', '0'],
            [0, '0', '0.0'],
            [-1, '0', 'foo'],
            [1, '0', ''],
            [0, '42', '   42'],
            [-1, '42', '42foo'],

            [0, 42, '000042'],
            [0, 42, '42.0'],
            [0, 42.0, '+42.0E0'],
            [0, 0, '0e214987142012'],

            [0, '42', '000042'],
            [0, '42', '42.0'],
            [0, '42.0', '+42.0E0'],
            [0, '0', '0e214987142012'],

            [0, 42, '   42'],
            [0, 42, '42   '],
            [-1, 42, '42abc'],
            [-1, 42, 'abc42'],
            [-1, 0, 'abc42'],

            [0, 42.0, '   42.0'],
            [0, 42.0, '42.0   '],
            [-1, 42.0, '42.0abc'],
            [-1, 42.0, 'abc42.0'],
            [-1, 0.0, 'abc42.0'],

            [0, \INF, 'INF'],
            [0, -\INF, '-INF'],
            [0, \INF, '1e1000'],
            [0, -\INF, '-1e1000'],

            [-1, 10, 20],
            [-1, '10', 20],
            [-1, 10, '20'],

            [1, 42, ' foo'],
            [0, 42, "42\f"],
            [1, 42, "\x00\x34\x32"],
        ];
    }
}

final class CoreTestIteratorAggregate implements \IteratorAggregate
{
    private $iterator;

    public function __construct(array $array, array $keys, $allowAccess = false, $maxPosition = false)
    {
        $this->iterator = new CoreTestIterator($array, $keys, $allowAccess, $maxPosition);
    }

    public function getIterator(): \Traversable
    {
        return $this->iterator;
    }
}

final class CoreTestIteratorAggregateAggregate implements \IteratorAggregate
{
    private $iterator;

    public function __construct(array $array, array $keys, $allowValueAccess = false, $maxPosition = false)
    {
        $this->iterator = new CoreTestIteratorAggregate($array, $keys, $allowValueAccess, $maxPosition);
    }

    public function getIterator(): \Traversable
    {
        return $this->iterator;
    }
}

final class CoreTestIterator implements \Iterator
{
    private $position;
    private $array;
    private $arrayKeys;
    private $allowValueAccess;
    private $maxPosition;

    public function __construct(array $values, array $keys, $allowValueAccess = false, $maxPosition = false)
    {
        $this->array = $values;
        $this->arrayKeys = $keys;
        $this->position = 0;
        $this->allowValueAccess = $allowValueAccess;
        $this->maxPosition = false === $maxPosition ? \count($values) + 1 : $maxPosition;
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    #[\ReturnTypeWillChange]
    public function current()
    {
        if ($this->allowValueAccess) {
            return $this->array[$this->key()];
        }

        throw new \LogicException('Code should only use the keys, not the values provided by iterator.');
    }

    #[\ReturnTypeWillChange]
    public function key()
    {
        return $this->arrayKeys[$this->position];
    }

    public function next(): void
    {
        ++$this->position;
        if ($this->position === $this->maxPosition) {
            throw new \LogicException(sprintf('Code should not iterate beyond %d.', $this->maxPosition));
        }
    }

    public function valid(): bool
    {
        return isset($this->arrayKeys[$this->position]);
    }
}
