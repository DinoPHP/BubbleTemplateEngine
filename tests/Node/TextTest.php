<?php

namespace Bubble\Tests\Node;

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

use Bubble\Node\TextNode;
use Bubble\Test\NodeTestCase;

class TextTest extends NodeTestCase
{
    public function testConstructor()
    {
        $node = new TextNode('foo', 1);

        $this->assertEquals('foo', $node->getAttribute('data'));
    }

    public function getTests()
    {
        $tests = [];
        $tests[] = [new TextNode('foo', 1), "// line 1\necho \"foo\";"];

        return $tests;
    }
}
