<?php

namespace Bubble\Tests;

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

use PHPUnit\Framework\TestCase;
use Bubble\Environment;
use Bubble\Extension\ExtensionInterface;
use Bubble\Loader\LoaderInterface;

class CustomExtensionTest extends TestCase
{
    /**
     * @dataProvider provideInvalidExtensions
     */
    public function testGetInvalidOperators(ExtensionInterface $extension, $expectedExceptionMessage)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $env = new Environment($this->createMock(LoaderInterface::class));
        $env->addExtension($extension);
        $env->getUnaryOperators();
    }

    public function provideInvalidExtensions()
    {
        return [
            [new InvalidOperatorExtension([1, 2, 3]), '"Bubble\Tests\InvalidOperatorExtension::getOperators()" must return an array of 2 elements, got 3.'],
        ];
    }
}

class InvalidOperatorExtension implements ExtensionInterface
{
    private $operators;

    public function __construct($operators)
    {
        $this->operators = $operators;
    }

    public function getTokenParsers(): array
    {
        return [];
    }

    public function getNodeVisitors(): array
    {
        return [];
    }

    public function getFilters(): array
    {
        return [];
    }

    public function getTests(): array
    {
        return [];
    }

    public function getFunctions(): array
    {
        return [];
    }

    public function getOperators(): array
    {
        return $this->operators;
    }
}
