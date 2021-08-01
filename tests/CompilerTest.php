<?php

namespace Bubble\Tests;

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

use PHPUnit\Framework\TestCase;
use Bubble\Compiler;
use Bubble\Environment;
use Bubble\Loader\LoaderInterface;

class CompilerTest extends TestCase
{
    public function testReprNumericValueWithLocale()
    {
        $compiler = new Compiler(new Environment($this->createMock(LoaderInterface::class)));

        $locale = setlocale(\LC_NUMERIC, 0);
        if (false === $locale) {
            $this->markTestSkipped('Your platform does not support locales.');
        }

        $required_locales = ['fr_FR.UTF-8', 'fr_FR.UTF8', 'fr_FR.utf-8', 'fr_FR.utf8', 'French_France.1252'];
        if (false === setlocale(\LC_NUMERIC, $required_locales)) {
            $this->markTestSkipped('Could not set any of required locales: '.implode(', ', $required_locales));
        }

        $this->assertEquals('1.2', $compiler->repr(1.2)->getSource());
        $this->assertStringContainsString('fr', strtolower(setlocale(\LC_NUMERIC, 0)));

        setlocale(\LC_NUMERIC, $locale);
    }
}
