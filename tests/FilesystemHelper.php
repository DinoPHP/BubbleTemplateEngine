<?php

namespace Bubble\Tests;

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

class FilesystemHelper
{
    public static function removeDir($dir)
    {
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($iterator as $filename => $fileInfo) {
            if ($fileInfo->isDir()) {
                rmdir($filename);
            } else {
                unlink($filename);
            }
        }
        rmdir($dir);
    }
}
