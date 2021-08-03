<?php

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

/**
 * Bubble Template array Loader implementation.
 *
 * An ArrayLoader instance loads Bubble Template source by name from an initial array:
 *
 *     $loader = new ArrayLoader(
 *         'foo' => '{{ bar }}',
 *         'baz' => 'Hey {{ qux }}!'
 *     );
 *
 *     $tpl = $loader->load('foo'); // '{{ bar }}'
 *
 * The ArrayLoader is used internally as a partials loader by Bubble_Engine instance when an array of partials
 * is set. It can also be used as a quick-and-dirty Template loader.
 */
class Bubble_Loader_ArrayLoader implements Bubble_Loader, Bubble_Loader_MutableLoader
{
    private $templates;

    /**
     * ArrayLoader constructor.
     *
     * @param array $templates Associative array of Template source (default: array())
     */
    public function __construct(array $templates = array())
    {
        $this->templates = $templates;
    }

    /**
     * Load a Template.
     *
     * @throws Bubble_Exception_UnknownTemplateException If a template file is not found
     *
     * @param string $name
     *
     * @return string Bubble Template source
     */
    public function load($name)
    {
        if (!isset($this->templates[$name])) {
            throw new Bubble_Exception_UnknownTemplateException($name);
        }

        return $this->templates[$name];
    }

    /**
     * Set an associative array of Template sources for this loader.
     *
     * @param array $templates
     */
    public function setTemplates(array $templates)
    {
        $this->templates = $templates;
    }

    /**
     * Set a Template source by name.
     *
     * @param string $name
     * @param string $template Bubble Template source
     */
    public function setTemplate($name, $template)
    {
        $this->templates[$name] = $template;
    }
}
