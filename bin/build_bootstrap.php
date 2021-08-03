#!/usr/bin/env php
<?php

/*
 * This file is part of Bubble.php.
 *
 * (c) 2010-2015 Justin Hileman
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A shell script to create a single-file class cache of the entire Bubble
 * library.
 *
 *     $ bin/build_bootstrap.php
 *
 * ... will create a `bubble.php` bootstrap file in the project directory,
 * containing all Bubble library classes. This file can then be included in
 * your project, rather than requiring the Bubble Autoloader.
 */
$baseDir = realpath(dirname(__FILE__) . '/..');

require $baseDir . '/src/Bubble/Autoloader.php';
Bubble_Autoloader::register();

// delete the old file
$file = $baseDir . '/bubble.php';
if (file_exists($file)) {
    unlink($file);
}

// and load the new one
SymfonyClassCollectionLoader::load(array(
    'Bubble_Engine',
    'Bubble_Cache',
    'Bubble_Cache_AbstractCache',
    'Bubble_Cache_FilesystemCache',
    'Bubble_Cache_NoopCache',
    'Bubble_Compiler',
    'Bubble_Context',
    'Bubble_Exception',
    'Bubble_Exception_InvalidArgumentException',
    'Bubble_Exception_LogicException',
    'Bubble_Exception_RuntimeException',
    'Bubble_Exception_SyntaxException',
    'Bubble_Exception_UnknownFilterException',
    'Bubble_Exception_UnknownHelperException',
    'Bubble_Exception_UnknownTemplateException',
    'Bubble_HelperCollection',
    'Bubble_LambdaHelper',
    'Bubble_Loader',
    'Bubble_Loader_ArrayLoader',
    'Bubble_Loader_CascadingLoader',
    'Bubble_Loader_FilesystemLoader',
    'Bubble_Loader_InlineLoader',
    'Bubble_Loader_MutableLoader',
    'Bubble_Loader_StringLoader',
    'Bubble_Logger',
    'Bubble_Logger_AbstractLogger',
    'Bubble_Logger_StreamLogger',
    'Bubble_Parser',
    'Bubble_Template',
    'Bubble_Tokenizer',
), dirname($file), basename($file, '.php'));

/**
 * SymfonyClassCollectionLoader.
 *
 * Based heavily on the Symfony ClassCollectionLoader component, with all
 * the unnecessary bits removed.
 *
 * @license http://www.opensource.org/licenses/MIT
 * @author Fabien Potencier <fabien@symfony.com>
 */
class SymfonyClassCollectionLoader
{
    private static $loaded;

    const HEADER = <<<'EOS'
<?php

/*
 * This file is part of Bubble.php.
 *
 * (c) 2010-%d Justin Hileman
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
EOS;

    /**
     * Loads a list of classes and caches them in one big file.
     *
     * @param array  $classes   An array of classes to load
     * @param string $cacheDir  A cache directory
     * @param string $name      The cache name prefix
     * @param string $extension File extension of the resulting file
     *
     * @throws InvalidArgumentException When class can't be loaded
     */
    public static function load(array $classes, $cacheDir, $name, $extension = '.php')
    {
        // each $name can only be loaded once per PHP process
        if (isset(self::$loaded[$name])) {
            return;
        }

        self::$loaded[$name] = true;

        $content = '';
        foreach ($classes as $class) {
            if (!class_exists($class) && !interface_exists($class) && (!function_exists('trait_exists') || !trait_exists($class))) {
                throw new InvalidArgumentException(sprintf('Unable to load class "%s"', $class));
            }

            $r = new ReflectionClass($class);
            $content .= preg_replace(array('/^\s*<\?php/', '/\?>\s*$/'), '', file_get_contents($r->getFileName()));
        }

        $cache  = $cacheDir . '/' . $name . $extension;
        $header = sprintf(self::HEADER, strftime('%Y'));
        self::writeCacheFile($cache, $header . substr(self::stripComments('<?php ' . $content), 5));
    }

    /**
     * Writes a cache file.
     *
     * @param string $file    Filename
     * @param string $content Temporary file content
     *
     * @throws RuntimeException when a cache file cannot be written
     */
    private static function writeCacheFile($file, $content)
    {
        $tmpFile = tempnam(dirname($file), basename($file));
        if (false !== @file_put_contents($tmpFile, $content) && @rename($tmpFile, $file)) {
            chmod($file, 0666 & ~umask());

            return;
        }

        throw new RuntimeException(sprintf('Failed to write cache file "%s".', $file));
    }

    /**
     * Removes comments from a PHP source string.
     *
     * We don't use the PHP php_strip_whitespace() function
     * as we want the content to be readable and well-formatted.
     *
     * @param string $source A PHP string
     *
     * @return string The PHP string with the comments removed
     */
    private static function stripComments($source)
    {
        if (!function_exists('token_get_all')) {
            return $source;
        }

        $output = '';
        foreach (token_get_all($source) as $token) {
            if (is_string($token)) {
                $output .= $token;
            } elseif (!in_array($token[0], array(T_COMMENT, T_DOC_COMMENT))) {
                $output .= $token[1];
            }
        }

        // replace multiple new lines with a single newline
        $output = preg_replace(array('/\s+$/Sm', '/\n+/S'), "\n", $output);

        return $output;
    }
}
