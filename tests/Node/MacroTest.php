<?php

namespace Bubble\Tests\Node;

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

use Bubble\Node\Expression\ConstantExpression;
use Bubble\Node\Expression\NameExpression;
use Bubble\Node\MacroNode;
use Bubble\Node\Node;
use Bubble\Node\TextNode;
use Bubble\Test\NodeTestCase;

class MacroTest extends NodeTestCase
{
    public function testConstructor()
    {
        $body = new TextNode('foo', 1);
        $arguments = new Node([new NameExpression('foo', 1)], [], 1);
        $node = new MacroNode('foo', $body, $arguments, 1);

        $this->assertEquals($body, $node->getNode('body'));
        $this->assertEquals($arguments, $node->getNode('arguments'));
        $this->assertEquals('foo', $node->getAttribute('name'));
    }

    public function getTests()
    {
        $body = new TextNode('foo', 1);
        $arguments = new Node([
            'foo' => new ConstantExpression(null, 1),
            'bar' => new ConstantExpression('Foo', 1),
        ], [], 1);
        $node = new MacroNode('foo', $body, $arguments, 1);

        return [
            [$node, <<<EOF
// line 1
public function macro_foo(\$__foo__ = null, \$__bar__ = "Foo", ...\$__varargs__)
{
    \$macros = \$this->macros;
    \$context = \$this->env->mergeGlobals([
        "foo" => \$__foo__,
        "bar" => \$__bar__,
        "varargs" => \$__varargs__,
    ]);

    \$blocks = [];

    ob_start(function () { return ''; });
    try {
        echo "foo";

        return ('' === \$tmp = ob_get_contents()) ? '' : new Markup(\$tmp, \$this->env->getCharset());
    } finally {
        ob_end_clean();
    }
}
EOF
            ],
        ];
    }
}
