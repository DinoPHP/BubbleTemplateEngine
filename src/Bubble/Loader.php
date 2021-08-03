<?php

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

/**
 * Bubble Template Loader interface.
 */
interface Bubble_Loader
{
    /**
     * Load a Template by name.
     *
     * @throws Bubble_Exception_UnknownTemplateException If a template file is not found
     *
     * @param string $name
     *
     * @return string|Bubble_Source Bubble Template source
     */
    public function load($name);
}
