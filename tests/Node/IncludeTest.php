<?php

namespace Bubble\Tests\Node;

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

use Bubble\Node\Expression\ArrayExpression;
use Bubble\Node\Expression\ConditionalExpression;
use Bubble\Node\Expression\ConstantExpression;
use Bubble\Node\IncludeNode;
use Bubble\Test\NodeTestCase;

class IncludeTest extends NodeTestCase
{
    public function testConstructor()
    {
        $expr = new ConstantExpression('foo.bubble', 1);
        $node = new IncludeNode($expr, null, false, false, 1);

        $this->assertFalse($node->hasNode('variables'));
        $this->assertEquals($expr, $node->getNode('expr'));
        $this->assertFalse($node->getAttribute('only'));

        $vars = new ArrayExpression([new ConstantExpression('foo', 1), new ConstantExpression(true, 1)], 1);
        $node = new IncludeNode($expr, $vars, true, false, 1);
        $this->assertEquals($vars, $node->getNode('variables'));
        $this->assertTrue($node->getAttribute('only'));
    }

    public function getTests()
    {
        $tests = [];

        $expr = new ConstantExpression('foo.bubble', 1);
        $node = new IncludeNode($expr, null, false, false, 1);
        $tests[] = [$node, <<<EOF
// line 1
\$this->loadTemplate("foo.bubble", null, 1)->display(\$context);
EOF
        ];

        $expr = new ConditionalExpression(
                        new ConstantExpression(true, 1),
                        new ConstantExpression('foo', 1),
                        new ConstantExpression('foo', 1),
                        0
                    );
        $node = new IncludeNode($expr, null, false, false, 1);
        $tests[] = [$node, <<<EOF
// line 1
\$this->loadTemplate(((true) ? ("foo") : ("foo")), null, 1)->display(\$context);
EOF
        ];

        $expr = new ConstantExpression('foo.bubble', 1);
        $vars = new ArrayExpression([new ConstantExpression('foo', 1), new ConstantExpression(true, 1)], 1);
        $node = new IncludeNode($expr, $vars, false, false, 1);
        $tests[] = [$node, <<<EOF
// line 1
\$this->loadTemplate("foo.bubble", null, 1)->display(bubble_array_merge(\$context, ["foo" => true]));
EOF
        ];

        $node = new IncludeNode($expr, $vars, true, false, 1);
        $tests[] = [$node, <<<EOF
// line 1
\$this->loadTemplate("foo.bubble", null, 1)->display(bubble_to_array(["foo" => true]));
EOF
        ];

        $node = new IncludeNode($expr, $vars, true, true, 1);
        $tests[] = [$node, <<<EOF
// line 1
\$__internal_%s = null;
try {
    \$__internal_%s =     \$this->loadTemplate("foo.bubble", null, 1);
} catch (LoaderError \$e) {
    // ignore missing template
}
if (\$__internal_%s) {
    \$__internal_%s->display(bubble_to_array(["foo" => true]));
}
EOF
        , null, true];

        return $tests;
    }
}
