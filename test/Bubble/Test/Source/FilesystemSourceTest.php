<?php

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

/**
 * @group unit
 */
class Bubble_Test_Source_FilesystemSourceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException RuntimeException
     */
    public function testMissingTemplateThrowsException()
    {
        $source = new Bubble_Source_FilesystemSource(dirname(__FILE__) . '/not_a_file.bubble', array('mtime'));
        $source->getKey();
    }
}
