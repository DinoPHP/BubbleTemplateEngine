<?php

namespace Bubble\Tests\Node;

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

use Bubble\Environment;
use Bubble\Loader\LoaderInterface;
use Bubble\Node\DeprecatedNode;
use Bubble\Node\Expression\ConstantExpression;
use Bubble\Node\Expression\FunctionExpression;
use Bubble\Node\IfNode;
use Bubble\Node\Node;
use Bubble\Source;
use Bubble\Test\NodeTestCase;
use Bubble\BubbleFunction;

class DeprecatedTest extends NodeTestCase
{
    public function testConstructor()
    {
        $expr = new ConstantExpression('foo', 1);
        $node = new DeprecatedNode($expr, 1);

        $this->assertEquals($expr, $node->getNode('expr'));
    }

    public function getTests()
    {
        $tests = [];

        $expr = new ConstantExpression('This section is deprecated', 1);
        $node = new DeprecatedNode($expr, 1, 'deprecated');
        $node->setSourceContext(new Source('', 'foo.bubble'));

        $tests[] = [$node, <<<EOF
// line 1
@trigger_error("This section is deprecated"." (\"foo.bubble\" at line 1).", E_USER_DEPRECATED);
EOF
        ];

        $t = new Node([
            new ConstantExpression(true, 1),
            new DeprecatedNode($expr, 2, 'deprecated'),
        ], [], 1);
        $node = new IfNode($t, null, 1);
        $node->setSourceContext(new Source('', 'foo.bubble'));

        $tests[] = [$node, <<<EOF
// line 1
if (true) {
    // line 2
    @trigger_error("This section is deprecated"." (\"foo.bubble\" at line 2).", E_USER_DEPRECATED);
}
EOF
        ];

        $environment = new Environment($this->createMock(LoaderInterface::class));
        $environment->addFunction(new BubbleFunction('foo', 'foo', []));

        $expr = new FunctionExpression('foo', new Node(), 1);
        $node = new DeprecatedNode($expr, 1, 'deprecated');
        $node->setSourceContext(new Source('', 'foo.bubble'));

        $compiler = $this->getCompiler($environment);
        $varName = $compiler->getVarName();

        $tests[] = [$node, <<<EOF
// line 1
\$$varName = foo();
@trigger_error(\$$varName." (\"foo.bubble\" at line 1).", E_USER_DEPRECATED);
EOF
        , $environment];

        return $tests;
    }
}
