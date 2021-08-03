<?php

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

require dirname(__FILE__) . '/../src/Bubble/Autoloader.php';
Bubble_Autoloader::register();
Bubble_Autoloader::register(dirname(__FILE__) . '/../test');

require dirname(__FILE__) . '/../vendor/yaml/lib/sfYamlParser.php';

/**
 * Minimal stream wrapper to test protocol-based access to templates.
 */
class TestStream
{
    private $filehandle;

    /**
     * Always returns false.
     *
     * @param string $path
     * @param int    $flags
     *
     * @return array
     */
    public function url_stat($path, $flags)
    {
        return false;
    }

    /**
     * Open the file.
     *
     * @param string $path
     * @param string $mode
     *
     * @return bool
     */
    public function stream_open($path, $mode)
    {
        $path = preg_replace('-^test://-', '', $path);
        $this->filehandle = fopen($path, $mode);

        return $this->filehandle !== false;
    }

    /**
     * @return array
     */
    public function stream_stat()
    {
        return array();
    }

    /**
     * @param int $count
     *
     * @return string
     */
    public function stream_read($count)
    {
        return fgets($this->filehandle, $count);
    }

    /**
     * @return bool
     */
    public function stream_eof()
    {
        return feof($this->filehandle);
    }

    /**
     * @return bool
     */
    public function stream_close()
    {
        return fclose($this->filehandle);
    }
}

if (!stream_wrapper_register('test', 'TestStream')) {
    die('Failed to register protocol');
}