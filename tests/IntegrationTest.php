<?php

namespace Bubble\Tests;

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

use Bubble\Extension\AbstractExtension;
use Bubble\Extension\DebugExtension;
use Bubble\Extension\SandboxExtension;
use Bubble\Extension\StringLoaderExtension;
use Bubble\Node\Expression\ConstantExpression;
use Bubble\Node\Node;
use Bubble\Node\PrintNode;
use Bubble\Sandbox\SecurityPolicy;
use Bubble\Test\IntegrationTestCase;
use Bubble\Token;
use Bubble\TokenParser\AbstractTokenParser;
use Bubble\BubbleFilter;
use Bubble\BubbleFunction;
use Bubble\BubbleTest;

// This function is defined to check that escaping strategies
// like html works even if a function with the same name is defined.
function html()
{
    return 'foo';
}

class IntegrationTest extends IntegrationTestCase
{
    public function getExtensions()
    {
        $policy = new SecurityPolicy([], [], [], [], ['dump']);

        return [
            new DebugExtension(),
            new SandboxExtension($policy, false),
            new StringLoaderExtension(),
            new BubbleTestExtension(),
        ];
    }

    public function getFixturesDir()
    {
        return __DIR__.'/Fixtures/';
    }
}

function test_foo($value = 'foo')
{
    return $value;
}

class BubbleTestFoo implements \Iterator
{
    public const BAR_NAME = 'bar';

    public $position = 0;
    public $array = [1, 2];

    public function bar($param1 = null, $param2 = null)
    {
        return 'bar'.($param1 ? '_'.$param1 : '').($param2 ? '-'.$param2 : '');
    }

    public function getFoo()
    {
        return 'foo';
    }

    public function getSelf()
    {
        return $this;
    }

    public function is()
    {
        return 'is';
    }

    public function in()
    {
        return 'in';
    }

    public function not()
    {
        return 'not';
    }

    public function strToLower($value)
    {
        return strtolower($value);
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    #[\ReturnTypeWillChange]
    public function current()
    {
        return $this->array[$this->position];
    }

    #[\ReturnTypeWillChange]
    public function key()
    {
        return 'a';
    }

    public function next(): void
    {
        ++$this->position;
    }

    public function valid(): bool
    {
        return isset($this->array[$this->position]);
    }
}

class BubbleTestTokenParser_§ extends AbstractTokenParser
{
    public function parse(Token $token): Node
    {
        $this->parser->getStream()->expect(Token::BLOCK_END_TYPE);

        return new PrintNode(new ConstantExpression('§', -1), -1);
    }

    public function getTag(): string
    {
        return '§';
    }
}

class BubbleTestExtension extends AbstractExtension
{
    public function getTokenParsers(): array
    {
        return [
            new BubbleTestTokenParser_§(),
        ];
    }

    public function getFilters(): array
    {
        return [
            new BubbleFilter('§', [$this, '§Filter']),
            new BubbleFilter('escape_and_nl2br', [$this, 'escape_and_nl2br'], ['needs_environment' => true, 'is_safe' => ['html']]),
            new BubbleFilter('nl2br', [$this, 'nl2br'], ['pre_escape' => 'html', 'is_safe' => ['html']]),
            new BubbleFilter('escape_something', [$this, 'escape_something'], ['is_safe' => ['something']]),
            new BubbleFilter('preserves_safety', [$this, 'preserves_safety'], ['preserves_safety' => ['html']]),
            new BubbleFilter('static_call_string', 'Bubble\Tests\BubbleTestExtension::staticCall'),
            new BubbleFilter('static_call_array', ['Bubble\Tests\BubbleTestExtension', 'staticCall']),
            new BubbleFilter('magic_call', [$this, 'magicCall']),
            new BubbleFilter('magic_call_string', 'Bubble\Tests\BubbleTestExtension::magicStaticCall'),
            new BubbleFilter('magic_call_array', ['Bubble\Tests\BubbleTestExtension', 'magicStaticCall']),
            new BubbleFilter('*_path', [$this, 'dynamic_path']),
            new BubbleFilter('*_foo_*_bar', [$this, 'dynamic_foo']),
            new BubbleFilter('not', [$this, 'notFilter']),
            new BubbleFilter('anon_foo', function ($name) { return '*'.$name.'*'; }),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new BubbleFunction('§', [$this, '§Function']),
            new BubbleFunction('safe_br', [$this, 'br'], ['is_safe' => ['html']]),
            new BubbleFunction('unsafe_br', [$this, 'br']),
            new BubbleFunction('static_call_string', 'Bubble\Tests\BubbleTestExtension::staticCall'),
            new BubbleFunction('static_call_array', ['Bubble\Tests\BubbleTestExtension', 'staticCall']),
            new BubbleFunction('*_path', [$this, 'dynamic_path']),
            new BubbleFunction('*_foo_*_bar', [$this, 'dynamic_foo']),
            new BubbleFunction('anon_foo', function ($name) { return '*'.$name.'*'; }),
        ];
    }

    public function getTests(): array
    {
        return [
            new BubbleTest('multi word', [$this, 'is_multi_word']),
            new BubbleTest('test_*', [$this, 'dynamic_test']),
        ];
    }

    public function notFilter($value)
    {
        return 'not '.$value;
    }

    public function §Filter($value)
    {
        return "§{$value}§";
    }

    public function §Function($value)
    {
        return "§{$value}§";
    }

    /**
     * nl2br which also escapes, for testing escaper filters.
     */
    public function escape_and_nl2br($env, $value, $sep = '<br />')
    {
        return $this->nl2br(bubble_escape_filter($env, $value, 'html'), $sep);
    }

    /**
     * nl2br only, for testing filters with pre_escape.
     */
    public function nl2br($value, $sep = '<br />')
    {
        // not secure if $value contains html tags (not only entities)
        // don't use
        return str_replace("\n", "$sep\n", $value);
    }

    public function dynamic_path($element, $item)
    {
        return $element.'/'.$item;
    }

    public function dynamic_foo($foo, $bar, $item)
    {
        return $foo.'/'.$bar.'/'.$item;
    }

    public function dynamic_test($element, $item)
    {
        return $element === $item;
    }

    public function escape_something($value)
    {
        return strtoupper($value);
    }

    public function preserves_safety($value)
    {
        return strtoupper($value);
    }

    public static function staticCall($value)
    {
        return "*$value*";
    }

    public function br()
    {
        return '<br />';
    }

    public function is_multi_word($value)
    {
        return false !== strpos($value, ' ');
    }

    public function __call($method, $arguments)
    {
        if ('magicCall' !== $method) {
            throw new \BadMethodCallException('Unexpected call to __call');
        }

        return 'magic_'.$arguments[0];
    }

    public static function __callStatic($method, $arguments)
    {
        if ('magicStaticCall' !== $method) {
            throw new \BadMethodCallException('Unexpected call to __callStatic');
        }

        return 'static_magic_'.$arguments[0];
    }
}

/**
 * This class is used in tests for the "length" filter and "empty" test. It asserts that __call is not
 * used to convert such objects to strings.
 */
class MagicCallStub
{
    public function __call($name, $args)
    {
        throw new \Exception('__call shall not be called');
    }
}

class ToStringStub
{
    /**
     * @var string
     */
    private $string;

    public function __construct($string)
    {
        $this->string = $string;
    }

    public function __toString()
    {
        return $this->string;
    }
}

/**
 * This class is used in tests for the length filter and empty test to show
 * that when \Countable is implemented, it is preferred over the __toString()
 * method.
 */
class CountableStub implements \Countable
{
    private $count;

    public function __construct($count)
    {
        $this->count = $count;
    }

    public function count(): int
    {
        return $this->count;
    }

    public function __toString()
    {
        throw new \Exception('__toString shall not be called on \Countables');
    }
}

/**
 * This class is used in tests for the length filter.
 */
class IteratorAggregateStub implements \IteratorAggregate
{
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->data);
    }
}

class SimpleIteratorForTesting implements \Iterator
{
    private $data = [1, 2, 3, 4, 5, 6, 7];
    private $key = 0;

    #[\ReturnTypeWillChange]
    public function current()
    {
        return $this->key;
    }

    public function next(): void
    {
        ++$this->key;
    }

    #[\ReturnTypeWillChange]
    public function key()
    {
        return $this->key;
    }

    public function valid(): bool
    {
        return isset($this->data[$this->key]);
    }

    public function rewind(): void
    {
        $this->key = 0;
    }

    public function __toString()
    {
        // for testing, make sure string length returned is not the same as the `iterator_count`
        return str_repeat('X', iterator_count($this) + 10);
    }
}
