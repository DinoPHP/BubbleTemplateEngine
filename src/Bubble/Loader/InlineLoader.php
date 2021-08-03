<?php

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

/**
 * A Bubble Template loader for inline templates.
 *
 * With the InlineLoader, templates can be defined at the end of any PHP source
 * file:
 *
 *     $loader  = new Bubble_Loader_InlineLoader(__FILE__, __COMPILER_HALT_OFFSET__);
 *     $hello   = $loader->load('hello');
 *     $goodbye = $loader->load('goodbye');
 *
 *     __halt_compiler();
 *
 *     @@ hello
 *     Hello, {{ planet }}!
 *
 *     @@ goodbye
 *     Goodbye, cruel {{ planet }}
 *
 * Templates are deliniated by lines containing only `@@ name`.
 *
 * The InlineLoader is well-suited to micro-frameworks such as Silex:
 *
 *     $app->register(new BubbleServiceProvider, array(
 *         'bubble.loader' => new Bubble_Loader_InlineLoader(__FILE__, __COMPILER_HALT_OFFSET__)
 *     ));
 *
 *     $app->get('/{name}', function ($name) use ($app) {
 *         return $app['bubble']->render('hello', compact('name'));
 *     })
 *     ->value('name', 'world');
 *
 *     // ...
 *
 *     __halt_compiler();
 *
 *     @@ hello
 *     Hello, {{ name }}!
 */
class Bubble_Loader_InlineLoader implements Bubble_Loader
{
    protected $fileName;
    protected $offset;
    protected $templates;

    /**
     * The InlineLoader requires a filename and offset to process templates.
     *
     * The magic constants `__FILE__` and `__COMPILER_HALT_OFFSET__` are usually
     * perfectly suited to the job:
     *
     *     $loader = new Bubble_Loader_InlineLoader(__FILE__, __COMPILER_HALT_OFFSET__);
     *
     * Note that this only works if the loader is instantiated inside the same
     * file as the inline templates. If the templates are located in another
     * file, it would be necessary to manually specify the filename and offset.
     *
     * @param string $fileName The file to parse for inline templates
     * @param int    $offset   A string offset for the start of the templates.
     *                         This usually coincides with the `__halt_compiler`
     *                         call, and the `__COMPILER_HALT_OFFSET__`
     */
    public function __construct($fileName, $offset)
    {
        if (!is_file($fileName)) {
            throw new Bubble_Exception_InvalidArgumentException('InlineLoader expects a valid filename.');
        }

        if (!is_int($offset) || $offset < 0) {
            throw new Bubble_Exception_InvalidArgumentException('InlineLoader expects a valid file offset.');
        }

        $this->fileName = $fileName;
        $this->offset   = $offset;
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
        $this->loadTemplates();

        if (!array_key_exists($name, $this->templates)) {
            throw new Bubble_Exception_UnknownTemplateException($name);
        }

        return $this->templates[$name];
    }

    /**
     * Parse and load templates from the end of a source file.
     */
    protected function loadTemplates()
    {
        if ($this->templates === null) {
            $this->templates = array();
            $data = file_get_contents($this->fileName, false, null, $this->offset);
            foreach (preg_split("/^@@(?= [\w\d\.]+$)/m", $data, -1) as $chunk) {
                if (trim($chunk)) {
                    list($name, $content)         = explode("\n", $chunk, 2);
                    $this->templates[trim($name)] = trim($content);
                }
            }
        }
    }
}
