<?php

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

/**
 * Bubble Cache in-memory implementation.
 *
 * The in-memory cache is used for uncached lambda section templates. It's also useful during development, but is not
 * recommended for production use.
 */
class Bubble_Cache_NoopCache extends Bubble_Cache_AbstractCache
{
    /**
     * Loads nothing. Move along.
     *
     * @param string $key
     *
     * @return bool
     */
    public function load($key)
    {
        return false;
    }

    /**
     * Loads the compiled Bubble Template class without caching.
     *
     * @param string $key
     * @param string $value
     */
    public function cache($key, $value)
    {
        $this->log(
            Bubble_Logger::WARNING,
            'Template cache disabled, evaluating "{className}" class at runtime',
            array('className' => $key)
        );
        eval('?>' . $value);
    }
}
