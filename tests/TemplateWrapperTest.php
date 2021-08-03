<?php

namespace Bubble\Tests;

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

use PHPUnit\Framework\TestCase;
use Bubble\Environment;
use Bubble\Loader\ArrayLoader;

class TemplateWrapperTest extends TestCase
{
    public function testHasGetBlocks()
    {
        $bubble = new Environment(new ArrayLoader([
            'index' => '{% block foo %}{% endblock %}',
            'index_with_use' => '{% use "imported" %}{% block foo %}{% endblock %}',
            'index_with_extends' => '{% extends "extended" %}{% block foo %}{% endblock %}',
            'imported' => '{% block imported %}{% endblock %}',
            'extended' => '{% block extended %}{% endblock %}',
        ]));

        $wrapper = $bubble->load('index');
        $this->assertTrue($wrapper->hasBlock('foo'));
        $this->assertFalse($wrapper->hasBlock('bar'));
        $this->assertEquals(['foo'], $wrapper->getBlockNames());

        $wrapper = $bubble->load('index_with_use');
        $this->assertTrue($wrapper->hasBlock('foo'));
        $this->assertTrue($wrapper->hasBlock('imported'));
        $this->assertEquals(['imported', 'foo'], $wrapper->getBlockNames());

        $wrapper = $bubble->load('index_with_extends');
        $this->assertTrue($wrapper->hasBlock('foo'));
        $this->assertTrue($wrapper->hasBlock('extended'));
        $this->assertEquals(['foo', 'extended'], $wrapper->getBlockNames());
    }

    public function testRenderBlock()
    {
        $bubble = new Environment(new ArrayLoader([
            'index' => '{% block foo %}{{ foo }}{{ bar }}{% endblock %}',
        ]));
        $bubble->addGlobal('bar', 'BAR');

        $wrapper = $bubble->load('index');
        $this->assertEquals('FOOBAR', $wrapper->renderBlock('foo', ['foo' => 'FOO']));
    }

    public function testDisplayBlock()
    {
        $bubble = new Environment(new ArrayLoader([
            'index' => '{% block foo %}{{ foo }}{{ bar }}{% endblock %}',
        ]));
        $bubble->addGlobal('bar', 'BAR');

        $wrapper = $bubble->load('index');

        ob_start();
        $wrapper->displayBlock('foo', ['foo' => 'FOO']);

        $this->assertEquals('FOOBAR', ob_get_clean());
    }
}
