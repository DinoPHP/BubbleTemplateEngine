<?php

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

abstract class Bubble_Test_FunctionalTestCase extends PHPUnit_Framework_TestCase
{
    protected static $tempDir;

    public static function setUpBeforeClass()
    {
        self::$tempDir = sys_get_temp_dir() . '/bubble_test';
        if (file_exists(self::$tempDir)) {
            self::rmdir(self::$tempDir);
        }
    }

    /**
     * @param string $path
     */
    protected static function rmdir($path)
    {
        $path = rtrim($path, '/') . '/';
        $handle = opendir($path);
        while (($file = readdir($handle)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $fullpath = $path . $file;
            if (is_dir($fullpath)) {
                self::rmdir($fullpath);
            } else {
                unlink($fullpath);
            }
        }

        closedir($handle);
        rmdir($path);
    }
}
