<?php

namespace Bubble\Tests\Node;

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

use Bubble\Node\AutoEscapeNode;
use Bubble\Node\Node;
use Bubble\Node\TextNode;
use Bubble\Test\NodeTestCase;

class AutoEscapeTest extends NodeTestCase
{
    public function testConstructor()
    {
        $body = new Node([new TextNode('foo', 1)]);
        $node = new AutoEscapeNode(true, $body, 1);

        $this->assertEquals($body, $node->getNode('body'));
        $this->assertTrue($node->getAttribute('value'));
    }

    public function getTests()
    {
        $body = new Node([new TextNode('foo', 1)]);
        $node = new AutoEscapeNode(true, $body, 1);

        return [
            [$node, "// line 1\necho \"foo\";"],
        ];
    }
}
