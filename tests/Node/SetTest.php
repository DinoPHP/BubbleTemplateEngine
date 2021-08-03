<?php

namespace Bubble\Tests\Node;

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

use Bubble\Node\Expression\AssignNameExpression;
use Bubble\Node\Expression\ConstantExpression;
use Bubble\Node\Expression\NameExpression;
use Bubble\Node\Node;
use Bubble\Node\PrintNode;
use Bubble\Node\SetNode;
use Bubble\Node\TextNode;
use Bubble\Test\NodeTestCase;

class SetTest extends NodeTestCase
{
    public function testConstructor()
    {
        $names = new Node([new AssignNameExpression('foo', 1)], [], 1);
        $values = new Node([new ConstantExpression('foo', 1)], [], 1);
        $node = new SetNode(false, $names, $values, 1);

        $this->assertEquals($names, $node->getNode('names'));
        $this->assertEquals($values, $node->getNode('values'));
        $this->assertFalse($node->getAttribute('capture'));
    }

    public function getTests()
    {
        $tests = [];

        $names = new Node([new AssignNameExpression('foo', 1)], [], 1);
        $values = new Node([new ConstantExpression('foo', 1)], [], 1);
        $node = new SetNode(false, $names, $values, 1);
        $tests[] = [$node, <<<EOF
// line 1
\$context["foo"] = "foo";
EOF
        ];

        $names = new Node([new AssignNameExpression('foo', 1)], [], 1);
        $values = new Node([new PrintNode(new ConstantExpression('foo', 1), 1)], [], 1);
        $node = new SetNode(true, $names, $values, 1);
        $tests[] = [$node, <<<EOF
// line 1
ob_start(function () { return ''; });
echo "foo";
\$context["foo"] = ('' === \$tmp = ob_get_clean()) ? '' : new Markup(\$tmp, \$this->env->getCharset());
EOF
        ];

        $names = new Node([new AssignNameExpression('foo', 1)], [], 1);
        $values = new TextNode('foo', 1);
        $node = new SetNode(true, $names, $values, 1);
        $tests[] = [$node, <<<EOF
// line 1
\$context["foo"] = ('' === \$tmp = "foo") ? '' : new Markup(\$tmp, \$this->env->getCharset());
EOF
        ];

        $names = new Node([new AssignNameExpression('foo', 1), new AssignNameExpression('bar', 1)], [], 1);
        $values = new Node([new ConstantExpression('foo', 1), new NameExpression('bar', 1)], [], 1);
        $node = new SetNode(false, $names, $values, 1);
        $tests[] = [$node, <<<EOF
// line 1
list(\$context["foo"], \$context["bar"]) = ["foo", {$this->getVariableGetter('bar')}];
EOF
        ];

        return $tests;
    }
}
