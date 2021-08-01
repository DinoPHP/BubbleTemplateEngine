<?php

namespace Bubble\Tests\Node;

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

use Bubble\Node\SandboxNode;
use Bubble\Node\TextNode;
use Bubble\Test\NodeTestCase;

class SandboxTest extends NodeTestCase
{
    public function testConstructor()
    {
        $body = new TextNode('foo', 1);
        $node = new SandboxNode($body, 1);

        $this->assertEquals($body, $node->getNode('body'));
    }

    public function getTests()
    {
        $tests = [];

        $body = new TextNode('foo', 1);
        $node = new SandboxNode($body, 1);

        $tests[] = [$node, <<<EOF
// line 1
if (!\$alreadySandboxed = \$this->sandbox->isSandboxed()) {
    \$this->sandbox->enableSandbox();
}
try {
    echo "foo";
} finally {
    if (!\$alreadySandboxed) {
        \$this->sandbox->disableSandbox();
    }
}
EOF
        ];

        return $tests;
    }
}
