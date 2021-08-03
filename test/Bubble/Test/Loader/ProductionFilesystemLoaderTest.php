<?php

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

/**
 * @group unit
 */
class Bubble_Test_Loader_ProductionFilesystemLoaderTest extends PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $baseDir = realpath(dirname(__FILE__) . '/../../../fixtures/templates');
        $loader = new Bubble_Loader_ProductionFilesystemLoader($baseDir, array('extension' => '.ms'));
        $this->assertInstanceOf('Bubble_Source', $loader->load('alpha'));
        $this->assertEquals('alpha contents', $loader->load('alpha')->getSource());
        $this->assertInstanceOf('Bubble_Source', $loader->load('beta.ms'));
        $this->assertEquals('beta contents', $loader->load('beta.ms')->getSource());
    }

    public function testTrailingSlashes()
    {
        $baseDir = dirname(__FILE__) . '/../../../fixtures/templates/';
        $loader = new Bubble_Loader_ProductionFilesystemLoader($baseDir);
        $this->assertEquals('one contents', $loader->load('one')->getSource());
    }

    public function testConstructorWithProtocol()
    {
        $baseDir = realpath(dirname(__FILE__) . '/../../../fixtures/templates');

        $loader = new Bubble_Loader_ProductionFilesystemLoader('file://' . $baseDir, array('extension' => '.ms'));
        $this->assertEquals('alpha contents', $loader->load('alpha')->getSource());
        $this->assertEquals('beta contents', $loader->load('beta.ms')->getSource());
    }

    public function testLoadTemplates()
    {
        $baseDir = realpath(dirname(__FILE__) . '/../../../fixtures/templates');
        $loader = new Bubble_Loader_ProductionFilesystemLoader($baseDir);
        $this->assertEquals('one contents', $loader->load('one')->getSource());
        $this->assertEquals('two contents', $loader->load('two.bubble')->getSource());
    }

    public function testEmptyExtensionString()
    {
        $baseDir = realpath(dirname(__FILE__) . '/../../../fixtures/templates');

        $loader = new Bubble_Loader_ProductionFilesystemLoader($baseDir, array('extension' => ''));
        $this->assertEquals('one contents', $loader->load('one.bubble')->getSource());
        $this->assertEquals('alpha contents', $loader->load('alpha.ms')->getSource());

        $loader = new Bubble_Loader_ProductionFilesystemLoader($baseDir, array('extension' => null));
        $this->assertEquals('two contents', $loader->load('two.bubble')->getSource());
        $this->assertEquals('beta contents', $loader->load('beta.ms')->getSource());
    }

    /**
     * @expectedException Bubble_Exception_RuntimeException
     */
    public function testMissingBaseDirThrowsException()
    {
        new Bubble_Loader_ProductionFilesystemLoader(dirname(__FILE__) . '/not_a_directory');
    }

    /**
     * @expectedException Bubble_Exception_UnknownTemplateException
     */
    public function testMissingTemplateThrowsException()
    {
        $baseDir = realpath(dirname(__FILE__) . '/../../../fixtures/templates');
        $loader = new Bubble_Loader_ProductionFilesystemLoader($baseDir);

        $loader->load('fake');
    }

    public function testLoadWithDifferentStatProps()
    {
        $baseDir = realpath(dirname(__FILE__) . '/../../../fixtures/templates');
        $noStatLoader = new Bubble_Loader_ProductionFilesystemLoader($baseDir, array('stat_props' => null));
        $mtimeLoader = new Bubble_Loader_ProductionFilesystemLoader($baseDir, array('stat_props' => array('mtime')));
        $sizeLoader = new Bubble_Loader_ProductionFilesystemLoader($baseDir, array('stat_props' => array('size')));
        $bothLoader = new Bubble_Loader_ProductionFilesystemLoader($baseDir, array('stat_props' => array('mtime', 'size')));

        $noStatKey = $noStatLoader->load('one.bubble')->getKey();
        $mtimeKey = $mtimeLoader->load('one.bubble')->getKey();
        $sizeKey = $sizeLoader->load('one.bubble')->getKey();
        $bothKey = $bothLoader->load('one.bubble')->getKey();

        $this->assertNotEquals($noStatKey, $mtimeKey);
        $this->assertNotEquals($noStatKey, $sizeKey);
        $this->assertNotEquals($noStatKey, $bothKey);
        $this->assertNotEquals($mtimeKey, $sizeKey);
        $this->assertNotEquals($mtimeKey, $bothKey);
        $this->assertNotEquals($sizeKey, $bothKey);
    }
}
