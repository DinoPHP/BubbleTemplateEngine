<?php

namespace Bubble\Tests\Node;

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

use Bubble\Node\Expression\AssignNameExpression;
use Bubble\Node\Expression\ConstantExpression;
use Bubble\Node\ImportNode;
use Bubble\Test\NodeTestCase;

class ImportTest extends NodeTestCase
{
    public function testConstructor()
    {
        $macro = new ConstantExpression('foo.bubble', 1);
        $var = new AssignNameExpression('macro', 1);
        $node = new ImportNode($macro, $var, 1);

        $this->assertEquals($macro, $node->getNode('expr'));
        $this->assertEquals($var, $node->getNode('var'));
    }

    public function getTests()
    {
        $tests = [];

        $macro = new ConstantExpression('foo.bubble', 1);
        $var = new AssignNameExpression('macro', 1);
        $node = new ImportNode($macro, $var, 1);

        $tests[] = [$node, <<<EOF
// line 1
\$macros["macro"] = \$this->macros["macro"] = \$this->loadTemplate("foo.bubble", null, 1)->unwrap();
EOF
        ];

        return $tests;
    }
}
