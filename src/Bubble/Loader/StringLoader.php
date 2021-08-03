<?php

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

/**
 * Bubble Template string Loader implementation.
 *
 * A StringLoader instance is essentially a noop. It simply passes the 'name' argument straight through:
 *
 *     $loader = new StringLoader;
 *     $tpl = $loader->load('{{ foo }}'); // '{{ foo }}'
 *
 * This is the default Template Loader instance used by Bubble:
 *
 *     $m = new Bubble;
 *     $tpl = $m->loadTemplate('{{ foo }}');
 *     echo $tpl->render(array('foo' => 'bar')); // "bar"
 */
class Bubble_Loader_StringLoader implements Bubble_Loader
{
    /**
     * Load a Template by source.
     *
     * @param string $name Bubble Template source
     *
     * @return string Bubble Template source
     */
    public function load($name)
    {
        return $name;
    }
}
