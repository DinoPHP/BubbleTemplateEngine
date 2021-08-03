<?php

namespace Bubble\Tests\Node\Expression;

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

use Bubble\Environment;
use Bubble\Loader\ArrayLoader;
use Bubble\Loader\LoaderInterface;
use Bubble\Node\Expression\ConstantExpression;
use Bubble\Node\Expression\FunctionExpression;
use Bubble\Node\Node;
use Bubble\Test\NodeTestCase;
use Bubble\BubbleFunction;

class FunctionTest extends NodeTestCase
{
    public function testConstructor()
    {
        $name = 'function';
        $args = new Node();
        $node = new FunctionExpression($name, $args, 1);

        $this->assertEquals($name, $node->getAttribute('name'));
        $this->assertEquals($args, $node->getNode('arguments'));
    }

    public function getTests()
    {
        $environment = new Environment($this->createMock(LoaderInterface::class));
        $environment->addFunction(new BubbleFunction('foo', 'bubble_tests_function_dummy', []));
        $environment->addFunction(new BubbleFunction('bar', 'bubble_tests_function_dummy', ['needs_environment' => true]));
        $environment->addFunction(new BubbleFunction('foofoo', 'bubble_tests_function_dummy', ['needs_context' => true]));
        $environment->addFunction(new BubbleFunction('foobar', 'bubble_tests_function_dummy', ['needs_environment' => true, 'needs_context' => true]));
        $environment->addFunction(new BubbleFunction('barbar', 'Bubble\Tests\Node\Expression\bubble_tests_function_barbar', ['is_variadic' => true]));

        $tests = [];

        $node = $this->createFunction('foo');
        $tests[] = [$node, 'bubble_tests_function_dummy()', $environment];

        $node = $this->createFunction('foo', [new ConstantExpression('bar', 1), new ConstantExpression('foobar', 1)]);
        $tests[] = [$node, 'bubble_tests_function_dummy("bar", "foobar")', $environment];

        $node = $this->createFunction('bar');
        $tests[] = [$node, 'bubble_tests_function_dummy($this->env)', $environment];

        $node = $this->createFunction('bar', [new ConstantExpression('bar', 1)]);
        $tests[] = [$node, 'bubble_tests_function_dummy($this->env, "bar")', $environment];

        $node = $this->createFunction('foofoo');
        $tests[] = [$node, 'bubble_tests_function_dummy($context)', $environment];

        $node = $this->createFunction('foofoo', [new ConstantExpression('bar', 1)]);
        $tests[] = [$node, 'bubble_tests_function_dummy($context, "bar")', $environment];

        $node = $this->createFunction('foobar');
        $tests[] = [$node, 'bubble_tests_function_dummy($this->env, $context)', $environment];

        $node = $this->createFunction('foobar', [new ConstantExpression('bar', 1)]);
        $tests[] = [$node, 'bubble_tests_function_dummy($this->env, $context, "bar")', $environment];

        // named arguments
        $node = $this->createFunction('date', [
            'timezone' => new ConstantExpression('America/Chicago', 1),
            'date' => new ConstantExpression(0, 1),
        ]);
        $tests[] = [$node, 'bubble_date_converter($this->env, 0, "America/Chicago")'];

        // arbitrary named arguments
        $node = $this->createFunction('barbar');
        $tests[] = [$node, 'Bubble\Tests\Node\Expression\bubble_tests_function_barbar()', $environment];

        $node = $this->createFunction('barbar', ['foo' => new ConstantExpression('bar', 1)]);
        $tests[] = [$node, 'Bubble\Tests\Node\Expression\bubble_tests_function_barbar(null, null, ["foo" => "bar"])', $environment];

        $node = $this->createFunction('barbar', ['arg2' => new ConstantExpression('bar', 1)]);
        $tests[] = [$node, 'Bubble\Tests\Node\Expression\bubble_tests_function_barbar(null, "bar")', $environment];

        $node = $this->createFunction('barbar', [
            new ConstantExpression('1', 1),
            new ConstantExpression('2', 1),
            new ConstantExpression('3', 1),
            'foo' => new ConstantExpression('bar', 1),
        ]);
        $tests[] = [$node, 'Bubble\Tests\Node\Expression\bubble_tests_function_barbar("1", "2", [0 => "3", "foo" => "bar"])', $environment];

        // function as an anonymous function
        $node = $this->createFunction('anonymous', [new ConstantExpression('foo', 1)]);
        $tests[] = [$node, 'call_user_func_array($this->env->getFunction(\'anonymous\')->getCallable(), ["foo"])'];

        return $tests;
    }

    protected function createFunction($name, array $arguments = [])
    {
        return new FunctionExpression($name, new Node($arguments), 1);
    }

    protected function getEnvironment()
    {
        $env = new Environment(new ArrayLoader([]));
        $env->addFunction(new BubbleFunction('anonymous', function () {}));

        return $env;
    }
}

function bubble_tests_function_dummy()
{
}

function bubble_tests_function_barbar($arg1 = null, $arg2 = null, array $args = [])
{
}
