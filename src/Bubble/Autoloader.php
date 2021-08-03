<?php

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

/**
 * Bubble class autoloader.
 */
class Bubble_Autoloader
{
    private $baseDir;

    /**
     * An array where the key is the baseDir and the key is an instance of this
     * class.
     *
     * @var array
     */
    private static $instances;

    /**
     * Autoloader constructor.
     *
     * @param string $baseDir Bubble library base directory (default: dirname(__FILE__).'/..')
     */
    public function __construct($baseDir = null)
    {
        if ($baseDir === null) {
            $baseDir = dirname(__FILE__) . '/..';
        }

        // realpath doesn't always work, for example, with stream URIs
        $realDir = realpath($baseDir);
        if (is_dir($realDir)) {
            $this->baseDir = $realDir;
        } else {
            $this->baseDir = $baseDir;
        }
    }

    /**
     * Register a new instance as an SPL autoloader.
     *
     * @param string $baseDir Bubble library base directory (default: dirname(__FILE__).'/..')
     *
     * @return Bubble_Autoloader Registered Autoloader instance
     */
    public static function register($baseDir = null)
    {
        $key = $baseDir ? $baseDir : 0;

        if (!isset(self::$instances[$key])) {
            self::$instances[$key] = new self($baseDir);
        }

        $loader = self::$instances[$key];
        spl_autoload_register(array($loader, 'autoload'));

        return $loader;
    }

    /**
     * Autoload Bubble classes.
     *
     * @param string $class
     */
    public function autoload($class)
    {
        if ($class[0] === '\\') {
            $class = substr($class, 1);
        }

        if (strpos($class, 'Bubble') !== 0) {
            return;
        }

        $file = sprintf('%s/%s.php', $this->baseDir, str_replace('_', '/', $class));
        if (is_file($file)) {
            require $file;
        }
    }
}
