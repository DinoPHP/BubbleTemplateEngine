<?php

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace Bubble\Tests\Extension;

use PHPUnit\Framework\TestCase;
use Bubble\Environment;
use Bubble\Extension\StringLoaderExtension;

class StringLoaderExtensionTest extends TestCase
{
    public function testIncludeWithTemplateStringAndNoSandbox()
    {
        $bubble = new Environment($this->createMock('\Bubble\Loader\LoaderInterface'));
        $bubble->addExtension(new StringLoaderExtension());
        $this->assertSame('something', bubble_include($bubble, [], bubble_template_from_string($bubble, 'something')));
    }
}
