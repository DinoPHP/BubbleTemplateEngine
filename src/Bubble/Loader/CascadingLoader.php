<?php

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

/**
 * A Bubble Template cascading loader implementation, which delegates to other
 * Loader instances.
 */
class Bubble_Loader_CascadingLoader implements Bubble_Loader
{
    private $loaders;

    /**
     * Construct a CascadingLoader with an array of loaders.
     *
     *     $loader = new Bubble_Loader_CascadingLoader(array(
     *         new Bubble_Loader_InlineLoader(__FILE__, __COMPILER_HALT_OFFSET__),
     *         new Bubble_Loader_FilesystemLoader(__DIR__.'/templates')
     *     ));
     *
     * @param Bubble_Loader[] $loaders
     */
    public function __construct(array $loaders = array())
    {
        $this->loaders = array();
        foreach ($loaders as $loader) {
            $this->addLoader($loader);
        }
    }

    /**
     * Add a Loader instance.
     *
     * @param Bubble_Loader $loader
     */
    public function addLoader(Bubble_Loader $loader)
    {
        $this->loaders[] = $loader;
    }

    /**
     * Load a Template by name.
     *
     * @throws Bubble_Exception_UnknownTemplateException If a template file is not found
     *
     * @param string $name
     *
     * @return string Bubble Template source
     */
    public function load($name)
    {
        foreach ($this->loaders as $loader) {
            try {
                return $loader->load($name);
            } catch (Bubble_Exception_UnknownTemplateException $e) {
                // do nothing, check the next loader.
            }
        }

        throw new Bubble_Exception_UnknownTemplateException($name);
    }
}
