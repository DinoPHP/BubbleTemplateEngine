<?php

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

/**
 * @group unit
 */
class Bubble_Test_Loader_FilesystemLoaderTest extends PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $baseDir = realpath(dirname(__FILE__) . '/../../../fixtures/templates');
        $loader = new Bubble_Loader_FilesystemLoader($baseDir, array('extension' => '.ms'));
        $this->assertEquals('alpha contents', $loader->load('alpha'));
        $this->assertEquals('beta contents', $loader->load('beta.ms'));
    }

    public function testTrailingSlashes()
    {
        $baseDir = dirname(__FILE__) . '/../../../fixtures/templates/';
        $loader = new Bubble_Loader_FilesystemLoader($baseDir);
        $this->assertEquals('one contents', $loader->load('one'));
    }

    public function testConstructorWithProtocol()
    {
        $baseDir = realpath(dirname(__FILE__) . '/../../../fixtures/templates');

        $loader = new Bubble_Loader_FilesystemLoader('test://' . $baseDir, array('extension' => '.ms'));
        $this->assertEquals('alpha contents', $loader->load('alpha'));
        $this->assertEquals('beta contents', $loader->load('beta.ms'));
    }

    public function testLoadTemplates()
    {
        $baseDir = realpath(dirname(__FILE__) . '/../../../fixtures/templates');
        $loader = new Bubble_Loader_FilesystemLoader($baseDir);
        $this->assertEquals('one contents', $loader->load('one'));
        $this->assertEquals('two contents', $loader->load('two.bubble'));
    }

    public function testEmptyExtensionString()
    {
        $baseDir = realpath(dirname(__FILE__) . '/../../../fixtures/templates');

        $loader = new Bubble_Loader_FilesystemLoader($baseDir, array('extension' => ''));
        $this->assertEquals('one contents', $loader->load('one.bubble'));
        $this->assertEquals('alpha contents', $loader->load('alpha.ms'));

        $loader = new Bubble_Loader_FilesystemLoader($baseDir, array('extension' => null));
        $this->assertEquals('two contents', $loader->load('two.bubble'));
        $this->assertEquals('beta contents', $loader->load('beta.ms'));
    }

    /**
     * @expectedException Bubble_Exception_RuntimeException
     */
    public function testMissingBaseDirThrowsException()
    {
        new Bubble_Loader_FilesystemLoader(dirname(__FILE__) . '/not_a_directory');
    }

    /**
     * @expectedException Bubble_Exception_UnknownTemplateException
     */
    public function testMissingTemplateThrowsException()
    {
        $baseDir = realpath(dirname(__FILE__) . '/../../../fixtures/templates');
        $loader = new Bubble_Loader_FilesystemLoader($baseDir);

        $loader->load('fake');
    }
}
