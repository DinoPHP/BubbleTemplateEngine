<?php

namespace Bubble\Tests\Node\Expression;

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

use Bubble\Environment;
use Bubble\Loader\LoaderInterface;
use Bubble\Node\Expression\NameExpression;
use Bubble\Test\NodeTestCase;

class NameTest extends NodeTestCase
{
    public function testConstructor()
    {
        $node = new NameExpression('foo', 1);

        $this->assertEquals('foo', $node->getAttribute('name'));
    }

    public function getTests()
    {
        $node = new NameExpression('foo', 1);
        $self = new NameExpression('_self', 1);
        $context = new NameExpression('_context', 1);

        $env = new Environment($this->createMock(LoaderInterface::class), ['strict_variables' => true]);
        $env1 = new Environment($this->createMock(LoaderInterface::class), ['strict_variables' => false]);

        $output = '(isset($context["foo"]) || array_key_exists("foo", $context) ? $context["foo"] : (function () { throw new RuntimeError(\'Variable "foo" does not exist.\', 1, $this->source); })())';

        return [
            [$node, "// line 1\n".$output, $env],
            [$node, $this->getVariableGetter('foo', 1), $env1],
            [$self, "// line 1\n\$this->getTemplateName()"],
            [$context, "// line 1\n\$context"],
        ];
    }
}
