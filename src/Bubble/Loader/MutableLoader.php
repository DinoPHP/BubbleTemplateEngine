<?php

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

/**
 * Bubble Template mutable Loader interface.
 */
interface Bubble_Loader_MutableLoader
{
    /**
     * Set an associative array of Template sources for this loader.
     *
     * @param array $templates
     */
    public function setTemplates(array $templates);

    /**
     * Set a Template source by name.
     *
     * @param string $name
     * @param string $template Bubble Template source
     */
    public function setTemplate($name, $template);
}
