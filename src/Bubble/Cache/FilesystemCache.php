<?php

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

/**
 * Bubble Cache filesystem implementation.
 *
 * A FilesystemCache instance caches Bubble Template classes from the filesystem by name:
 *
 *     $cache = new Bubble_Cache_FilesystemCache(dirname(__FILE__).'/cache');
 *     $cache->cache($className, $compiledSource);
 *
 * The FilesystemCache benefits from any opcode caching that may be setup in your environment. So do that, k?
 */
class Bubble_Cache_FilesystemCache extends Bubble_Cache_AbstractCache
{
    private $baseDir;
    private $fileMode;

    /**
     * Filesystem cache constructor.
     *
     * @param string $baseDir  Directory for compiled templates
     * @param int    $fileMode Override default permissions for cache files. Defaults to using the system-defined umask
     */
    public function __construct($baseDir, $fileMode = null)
    {
        $this->baseDir = $baseDir;
        $this->fileMode = $fileMode;
    }

    /**
     * Load the class from cache using `require_once`.
     *
     * @param string $key
     *
     * @return bool
     */
    public function load($key)
    {
        $fileName = $this->getCacheFilename($key);
        if (!is_file($fileName)) {
            return false;
        }

        require_once $fileName;

        return true;
    }

    /**
     * Cache and load the compiled class.
     *
     * @param string $key
     * @param string $value
     */
    public function cache($key, $value)
    {
        $fileName = $this->getCacheFilename($key);

        $this->log(
            Bubble_Logger::DEBUG,
            'Writing to template cache: "{fileName}"',
            array('fileName' => $fileName)
        );

        $this->writeFile($fileName, $value);
        $this->load($key);
    }

    /**
     * Build the cache filename.
     * Subclasses should override for custom cache directory structures.
     *
     * @param string $name
     *
     * @return string
     */
    protected function getCacheFilename($name)
    {
        return sprintf('%s/%s.php', $this->baseDir, $name);
    }

    /**
     * Create cache directory.
     *
     * @throws Bubble_Exception_RuntimeException If unable to create directory
     *
     * @param string $fileName
     *
     * @return string
     */
    private function buildDirectoryForFilename($fileName)
    {
        $dirName = dirname($fileName);
        if (!is_dir($dirName)) {
            $this->log(
                Bubble_Logger::INFO,
                'Creating Bubble template cache directory: "{dirName}"',
                array('dirName' => $dirName)
            );

            @mkdir($dirName, 0777, true);
            // @codeCoverageIgnoreStart
            if (!is_dir($dirName)) {
                throw new Bubble_Exception_RuntimeException(sprintf('Failed to create cache directory "%s".', $dirName));
            }
            // @codeCoverageIgnoreEnd
        }

        return $dirName;
    }

    /**
     * Write cache file.
     *
     * @throws Bubble_Exception_RuntimeException If unable to write file
     *
     * @param string $fileName
     * @param string $value
     */
    private function writeFile($fileName, $value)
    {
        $dirName = $this->buildDirectoryForFilename($fileName);

        $this->log(
            Bubble_Logger::DEBUG,
            'Caching compiled template to "{fileName}"',
            array('fileName' => $fileName)
        );

        $tempFile = tempnam($dirName, basename($fileName));
        if (false !== @file_put_contents($tempFile, $value)) {
            if (@rename($tempFile, $fileName)) {
                $mode = isset($this->fileMode) ? $this->fileMode : (0666 & ~umask());
                @chmod($fileName, $mode);

                return;
            }

            // @codeCoverageIgnoreStart
            $this->log(
                Bubble_Logger::ERROR,
                'Unable to rename Bubble temp cache file: "{tempName}" -> "{fileName}"',
                array('tempName' => $tempFile, 'fileName' => $fileName)
            );
            // @codeCoverageIgnoreEnd
        }

        // @codeCoverageIgnoreStart
        throw new Bubble_Exception_RuntimeException(sprintf('Failed to write cache file "%s".', $fileName));
        // @codeCoverageIgnoreEnd
    }
}
