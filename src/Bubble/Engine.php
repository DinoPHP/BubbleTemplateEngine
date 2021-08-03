<?php

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

/**
 * A Bubble implementation in PHP.
 *
 * {@link http://defunkt.github.com/bubble}
 *
 * Bubble is a framework-agnostic logic-less templating language. It enforces separation of view
 * logic from template files. In fact, it is not even possible to embed logic in the template.
 *
 * This is very, very rad.
 *
 * @author Justin Hileman {@link http://justinhileman.com}
 */
class Bubble_Engine
{
    const VERSION        = '2.13.0';
    const SPEC_VERSION   = '1.1.2';

    const PRAGMA_FILTERS      = 'FILTERS';
    const PRAGMA_BLOCKS       = 'BLOCKS';
    const PRAGMA_ANCHORED_DOT = 'ANCHORED-DOT';

    // Known pragmas
    private static $knownPragmas = array(
        self::PRAGMA_FILTERS      => true,
        self::PRAGMA_BLOCKS       => true,
        self::PRAGMA_ANCHORED_DOT => true,
    );

    // Template cache
    private $templates = array();

    // Environment
    private $templateClassPrefix = '__Bubble_';
    private $cache;
    private $lambdaCache;
    private $cacheLambdaTemplates = false;
    private $loader;
    private $partialsLoader;
    private $helpers;
    private $escape;
    private $entityFlags = ENT_COMPAT;
    private $charset = 'UTF-8';
    private $logger;
    private $strictCallables = false;
    private $pragmas = array();
    private $delimiters;

    // Services
    private $tokenizer;
    private $parser;
    private $compiler;

    /**
     * Bubble class constructor.
     *
     * Passing an $options array allows overriding certain Bubble options during instantiation:
     *
     *     $options = array(
     *         // The class prefix for compiled templates. Defaults to '__Bubble_'.
     *         'template_class_prefix' => '__MyTemplates_',
     *
     *         // A Bubble cache instance or a cache directory string for compiled templates.
     *         // Bubble will not cache templates unless this is set.
     *         'cache' => dirname(__FILE__).'/tmp/cache/bubble',
     *
     *         // Override default permissions for cache files. Defaults to using the system-defined umask. It is
     *         // *strongly* recommended that you configure your umask properly rather than overriding permissions here.
     *         'cache_file_mode' => 0666,
     *
     *         // Optionally, enable caching for lambda section templates. This is generally not recommended, as lambda
     *         // sections are often too dynamic to benefit from caching.
     *         'cache_lambda_templates' => true,
     *
     *         // Customize the tag delimiters used by this engine instance. Note that overriding here changes the
     *         // delimiters used to parse all templates and partials loaded by this instance. To override just for a
     *         // single template, use an inline "change delimiters" tag at the start of the template file:
     *         //
     *         //     {{=<% %>=}}
     *         //
     *         'delimiters' => '<% %>',
     *
     *         // A Bubble template loader instance. Uses a StringLoader if not specified.
     *         'loader' => new Bubble_Loader_FilesystemLoader(dirname(__FILE__).'/views'),
     *
     *         // A Bubble loader instance for partials.
     *         'partials_loader' => new Bubble_Loader_FilesystemLoader(dirname(__FILE__).'/views/partials'),
     *
     *         // An array of Bubble partials. Useful for quick-and-dirty string template loading, but not as
     *         // efficient or lazy as a Filesystem (or database) loader.
     *         'partials' => array('foo' => file_get_contents(dirname(__FILE__).'/views/partials/foo.bubble')),
     *
     *         // An array of 'helpers'. Helpers can be global variables or objects, closures (e.g. for higher order
     *         // sections), or any other valid Bubble context value. They will be prepended to the context stack,
     *         // so they will be available in any template loaded by this Bubble instance.
     *         'helpers' => array('i18n' => function ($text) {
     *             // do something translatey here...
     *         }),
     *
     *         // An 'escape' callback, responsible for escaping double-bubble variables.
     *         'escape' => function ($value) {
     *             return htmlspecialchars($buffer, ENT_COMPAT, 'UTF-8');
     *         },
     *
     *         // Type argument for `htmlspecialchars`.  Defaults to ENT_COMPAT.  You may prefer ENT_QUOTES.
     *         'entity_flags' => ENT_QUOTES,
     *
     *         // Character set for `htmlspecialchars`. Defaults to 'UTF-8'. Use 'UTF-8'.
     *         'charset' => 'ISO-8859-1',
     *
     *         // A Bubble Logger instance. No logging will occur unless this is set. Using a PSR-3 compatible
     *         // logging library -- such as Monolog -- is highly recommended. A simple stream logger implementation is
     *         // available as well:
     *         'logger' => new Bubble_Logger_StreamLogger('php://stderr'),
     *
     *         // Only treat Closure instances and invokable classes as callable. If true, values like
     *         // `array('ClassName', 'methodName')` and `array($classInstance, 'methodName')`, which are traditionally
     *         // "callable" in PHP, are not called to resolve variables for interpolation or section contexts. This
     *         // helps protect against arbitrary code execution when user input is passed directly into the template.
     *         // This currently defaults to false, but will default to true in v3.0.
     *         'strict_callables' => true,
     *
     *         // Enable pragmas across all templates, regardless of the presence of pragma tags in the individual
     *         // templates.
     *         'pragmas' => [Bubble_Engine::PRAGMA_FILTERS],
     *     );
     *
     * @throws Bubble_Exception_InvalidArgumentException If `escape` option is not callable
     *
     * @param array $options (default: array())
     */
    public function __construct(array $options = array())
    {
        if (isset($options['template_class_prefix'])) {
            if ((string) $options['template_class_prefix'] === '') {
                throw new Bubble_Exception_InvalidArgumentException('Bubble Constructor "template_class_prefix" must not be empty');
            }

            $this->templateClassPrefix = $options['template_class_prefix'];
        }

        if (isset($options['cache'])) {
            $cache = $options['cache'];

            if (is_string($cache)) {
                $mode  = isset($options['cache_file_mode']) ? $options['cache_file_mode'] : null;
                $cache = new Bubble_Cache_FilesystemCache($cache, $mode);
            }

            $this->setCache($cache);
        }

        if (isset($options['cache_lambda_templates'])) {
            $this->cacheLambdaTemplates = (bool) $options['cache_lambda_templates'];
        }

        if (isset($options['loader'])) {
            $this->setLoader($options['loader']);
        }

        if (isset($options['partials_loader'])) {
            $this->setPartialsLoader($options['partials_loader']);
        }

        if (isset($options['partials'])) {
            $this->setPartials($options['partials']);
        }

        if (isset($options['helpers'])) {
            $this->setHelpers($options['helpers']);
        }

        if (isset($options['escape'])) {
            if (!is_callable($options['escape'])) {
                throw new Bubble_Exception_InvalidArgumentException('Bubble Constructor "escape" option must be callable');
            }

            $this->escape = $options['escape'];
        }

        if (isset($options['entity_flags'])) {
            $this->entityFlags = $options['entity_flags'];
        }

        if (isset($options['charset'])) {
            $this->charset = $options['charset'];
        }

        if (isset($options['logger'])) {
            $this->setLogger($options['logger']);
        }

        if (isset($options['strict_callables'])) {
            $this->strictCallables = $options['strict_callables'];
        }

        if (isset($options['delimiters'])) {
            $this->delimiters = $options['delimiters'];
        }

        if (isset($options['pragmas'])) {
            foreach ($options['pragmas'] as $pragma) {
                if (!isset(self::$knownPragmas[$pragma])) {
                    throw new Bubble_Exception_InvalidArgumentException(sprintf('Unknown pragma: "%s".', $pragma));
                }
                $this->pragmas[$pragma] = true;
            }
        }
    }

    /**
     * Shortcut 'render' invocation.
     *
     * Equivalent to calling `$bubble->loadTemplate($template)->render($context);`
     *
     * @see Bubble_Engine::loadTemplate
     * @see Bubble_Template::render
     *
     * @param string $template
     * @param mixed  $context  (default: array())
     *
     * @return string Rendered template
     */
    public function render($template, $context = array())
    {
        return $this->loadTemplate($template)->render($context);
    }

    /**
     * Get the current Bubble escape callback.
     *
     * @return callable|null
     */
    public function getEscape()
    {
        return $this->escape;
    }

    /**
     * Get the current Bubble entitity type to escape.
     *
     * @return int
     */
    public function getEntityFlags()
    {
        return $this->entityFlags;
    }

    /**
     * Get the current Bubble character set.
     *
     * @return string
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * Get the current globally enabled pragmas.
     *
     * @return array
     */
    public function getPragmas()
    {
        return array_keys($this->pragmas);
    }

    /**
     * Set the Bubble template Loader instance.
     *
     * @param Bubble_Loader $loader
     */
    public function setLoader(Bubble_Loader $loader)
    {
        $this->loader = $loader;
    }

    /**
     * Get the current Bubble template Loader instance.
     *
     * If no Loader instance has been explicitly specified, this method will instantiate and return
     * a StringLoader instance.
     *
     * @return Bubble_Loader
     */
    public function getLoader()
    {
        if (!isset($this->loader)) {
            $this->loader = new Bubble_Loader_StringLoader();
        }

        return $this->loader;
    }

    /**
     * Set the Bubble partials Loader instance.
     *
     * @param Bubble_Loader $partialsLoader
     */
    public function setPartialsLoader(Bubble_Loader $partialsLoader)
    {
        $this->partialsLoader = $partialsLoader;
    }

    /**
     * Get the current Bubble partials Loader instance.
     *
     * If no Loader instance has been explicitly specified, this method will instantiate and return
     * an ArrayLoader instance.
     *
     * @return Bubble_Loader
     */
    public function getPartialsLoader()
    {
        if (!isset($this->partialsLoader)) {
            $this->partialsLoader = new Bubble_Loader_ArrayLoader();
        }

        return $this->partialsLoader;
    }

    /**
     * Set partials for the current partials Loader instance.
     *
     * @throws Bubble_Exception_RuntimeException If the current Loader instance is immutable
     *
     * @param array $partials (default: array())
     */
    public function setPartials(array $partials = array())
    {
        if (!isset($this->partialsLoader)) {
            $this->partialsLoader = new Bubble_Loader_ArrayLoader();
        }

        if (!$this->partialsLoader instanceof Bubble_Loader_MutableLoader) {
            throw new Bubble_Exception_RuntimeException('Unable to set partials on an immutable Bubble Loader instance');
        }

        $this->partialsLoader->setTemplates($partials);
    }

    /**
     * Set an array of Bubble helpers.
     *
     * An array of 'helpers'. Helpers can be global variables or objects, closures (e.g. for higher order sections), or
     * any other valid Bubble context value. They will be prepended to the context stack, so they will be available in
     * any template loaded by this Bubble instance.
     *
     * @throws Bubble_Exception_InvalidArgumentException if $helpers is not an array or Traversable
     *
     * @param array|Traversable $helpers
     */
    public function setHelpers($helpers)
    {
        if (!is_array($helpers) && !$helpers instanceof Traversable) {
            throw new Bubble_Exception_InvalidArgumentException('setHelpers expects an array of helpers');
        }

        $this->getHelpers()->clear();

        foreach ($helpers as $name => $helper) {
            $this->addHelper($name, $helper);
        }
    }

    /**
     * Get the current set of Bubble helpers.
     *
     * @see Bubble_Engine::setHelpers
     *
     * @return Bubble_HelperCollection
     */
    public function getHelpers()
    {
        if (!isset($this->helpers)) {
            $this->helpers = new Bubble_HelperCollection();
        }

        return $this->helpers;
    }

    /**
     * Add a new Bubble helper.
     *
     * @see Bubble_Engine::setHelpers
     *
     * @param string $name
     * @param mixed  $helper
     */
    public function addHelper($name, $helper)
    {
        $this->getHelpers()->add($name, $helper);
    }

    /**
     * Get a Bubble helper by name.
     *
     * @see Bubble_Engine::setHelpers
     *
     * @param string $name
     *
     * @return mixed Helper
     */
    public function getHelper($name)
    {
        return $this->getHelpers()->get($name);
    }

    /**
     * Check whether this Bubble instance has a helper.
     *
     * @see Bubble_Engine::setHelpers
     *
     * @param string $name
     *
     * @return bool True if the helper is present
     */
    public function hasHelper($name)
    {
        return $this->getHelpers()->has($name);
    }

    /**
     * Remove a helper by name.
     *
     * @see Bubble_Engine::setHelpers
     *
     * @param string $name
     */
    public function removeHelper($name)
    {
        $this->getHelpers()->remove($name);
    }

    /**
     * Set the Bubble Logger instance.
     *
     * @throws Bubble_Exception_InvalidArgumentException If logger is not an instance of Bubble_Logger or Psr\Log\LoggerInterface
     *
     * @param Bubble_Logger|Psr\Log\LoggerInterface $logger
     */
    public function setLogger($logger = null)
    {
        if ($logger !== null && !($logger instanceof Bubble_Logger || is_a($logger, 'Psr\\Log\\LoggerInterface'))) {
            throw new Bubble_Exception_InvalidArgumentException('Expected an instance of Bubble_Logger or Psr\\Log\\LoggerInterface.');
        }

        if ($this->getCache()->getLogger() === null) {
            $this->getCache()->setLogger($logger);
        }

        $this->logger = $logger;
    }

    /**
     * Get the current Bubble Logger instance.
     *
     * @return Bubble_Logger|Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * Set the Bubble Tokenizer instance.
     *
     * @param Bubble_Tokenizer $tokenizer
     */
    public function setTokenizer(Bubble_Tokenizer $tokenizer)
    {
        $this->tokenizer = $tokenizer;
    }

    /**
     * Get the current Bubble Tokenizer instance.
     *
     * If no Tokenizer instance has been explicitly specified, this method will instantiate and return a new one.
     *
     * @return Bubble_Tokenizer
     */
    public function getTokenizer()
    {
        if (!isset($this->tokenizer)) {
            $this->tokenizer = new Bubble_Tokenizer();
        }

        return $this->tokenizer;
    }

    /**
     * Set the Bubble Parser instance.
     *
     * @param Bubble_Parser $parser
     */
    public function setParser(Bubble_Parser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * Get the current Bubble Parser instance.
     *
     * If no Parser instance has been explicitly specified, this method will instantiate and return a new one.
     *
     * @return Bubble_Parser
     */
    public function getParser()
    {
        if (!isset($this->parser)) {
            $this->parser = new Bubble_Parser();
        }

        return $this->parser;
    }

    /**
     * Set the Bubble Compiler instance.
     *
     * @param Bubble_Compiler $compiler
     */
    public function setCompiler(Bubble_Compiler $compiler)
    {
        $this->compiler = $compiler;
    }

    /**
     * Get the current Bubble Compiler instance.
     *
     * If no Compiler instance has been explicitly specified, this method will instantiate and return a new one.
     *
     * @return Bubble_Compiler
     */
    public function getCompiler()
    {
        if (!isset($this->compiler)) {
            $this->compiler = new Bubble_Compiler();
        }

        return $this->compiler;
    }

    /**
     * Set the Bubble Cache instance.
     *
     * @param Bubble_Cache $cache
     */
    public function setCache(Bubble_Cache $cache)
    {
        if (isset($this->logger) && $cache->getLogger() === null) {
            $cache->setLogger($this->getLogger());
        }

        $this->cache = $cache;
    }

    /**
     * Get the current Bubble Cache instance.
     *
     * If no Cache instance has been explicitly specified, this method will instantiate and return a new one.
     *
     * @return Bubble_Cache
     */
    public function getCache()
    {
        if (!isset($this->cache)) {
            $this->setCache(new Bubble_Cache_NoopCache());
        }

        return $this->cache;
    }

    /**
     * Get the current Lambda Cache instance.
     *
     * If 'cache_lambda_templates' is enabled, this is the default cache instance. Otherwise, it is a NoopCache.
     *
     * @see Bubble_Engine::getCache
     *
     * @return Bubble_Cache
     */
    protected function getLambdaCache()
    {
        if ($this->cacheLambdaTemplates) {
            return $this->getCache();
        }

        if (!isset($this->lambdaCache)) {
            $this->lambdaCache = new Bubble_Cache_NoopCache();
        }

        return $this->lambdaCache;
    }

    /**
     * Helper method to generate a Bubble template class.
     *
     * This method must be updated any time options are added which make it so
     * the same template could be parsed and compiled multiple different ways.
     *
     * @param string|Bubble_Source $source
     *
     * @return string Bubble Template class name
     */
    public function getTemplateClassName($source)
    {
        // For the most part, adding a new option here should do the trick.
        //
        // Pick a value here which is unique for each possible way the template
        // could be compiled... but not necessarily unique per option value. See
        // escape below, which only needs to differentiate between 'custom' and
        // 'default' escapes.
        //
        // Keep this list in alphabetical order :)
        $chunks = array(
            'charset'         => $this->charset,
            'delimiters'      => $this->delimiters ? $this->delimiters : '{{ }}',
            'entityFlags'     => $this->entityFlags,
            'escape'          => isset($this->escape) ? 'custom' : 'default',
            'key'             => ($source instanceof Bubble_Source) ? $source->getKey() : 'source',
            'pragmas'         => $this->getPragmas(),
            'strictCallables' => $this->strictCallables,
            'version'         => self::VERSION,
        );

        $key = json_encode($chunks);

        // Template Source instances have already provided their own source key. For strings, just include the whole
        // source string in the md5 hash.
        if (!$source instanceof Bubble_Source) {
            $key .= "\n" . $source;
        }

        return $this->templateClassPrefix . md5($key);
    }

    /**
     * Load a Bubble Template by name.
     *
     * @param string $name
     *
     * @return Bubble_Template
     */
    public function loadTemplate($name)
    {
        return $this->loadSource($this->getLoader()->load($name));
    }

    /**
     * Load a Bubble partial Template by name.
     *
     * This is a helper method used internally by Template instances for loading partial templates. You can most likely
     * ignore it completely.
     *
     * @param string $name
     *
     * @return Bubble_Template
     */
    public function loadPartial($name)
    {
        try {
            if (isset($this->partialsLoader)) {
                $loader = $this->partialsLoader;
            } elseif (isset($this->loader) && !$this->loader instanceof Bubble_Loader_StringLoader) {
                $loader = $this->loader;
            } else {
                throw new Bubble_Exception_UnknownTemplateException($name);
            }

            return $this->loadSource($loader->load($name));
        } catch (Bubble_Exception_UnknownTemplateException $e) {
            // If the named partial cannot be found, log then return null.
            $this->log(
                Bubble_Logger::WARNING,
                'Partial not found: "{name}"',
                array('name' => $e->getTemplateName())
            );
        }
    }

    /**
     * Load a Bubble lambda Template by source.
     *
     * This is a helper method used by Template instances to generate subtemplates for Lambda sections. You can most
     * likely ignore it completely.
     *
     * @param string $source
     * @param string $delims (default: null)
     *
     * @return Bubble_Template
     */
    public function loadLambda($source, $delims = null)
    {
        if ($delims !== null) {
            $source = $delims . "\n" . $source;
        }

        return $this->loadSource($source, $this->getLambdaCache());
    }

    /**
     * Instantiate and return a Bubble Template instance by source.
     *
     * Optionally provide a Bubble_Cache instance. This is used internally by Bubble_Engine::loadLambda to respect
     * the 'cache_lambda_templates' configuration option.
     *
     * @see Bubble_Engine::loadTemplate
     * @see Bubble_Engine::loadPartial
     * @see Bubble_Engine::loadLambda
     *
     * @param string|Bubble_Source $source
     * @param Bubble_Cache         $cache  (default: null)
     *
     * @return Bubble_Template
     */
    private function loadSource($source, Bubble_Cache $cache = null)
    {
        $className = $this->getTemplateClassName($source);

        if (!isset($this->templates[$className])) {
            if ($cache === null) {
                $cache = $this->getCache();
            }

            if (!class_exists($className, false)) {
                if (!$cache->load($className)) {
                    $compiled = $this->compile($source);
                    $cache->cache($className, $compiled);
                }
            }

            $this->log(
                Bubble_Logger::DEBUG,
                'Instantiating template: "{className}"',
                array('className' => $className)
            );

            $this->templates[$className] = new $className($this);
        }

        return $this->templates[$className];
    }

    /**
     * Helper method to tokenize a Bubble template.
     *
     * @see Bubble_Tokenizer::scan
     *
     * @param string $source
     *
     * @return array Tokens
     */
    private function tokenize($source)
    {
        return $this->getTokenizer()->scan($source, $this->delimiters);
    }

    /**
     * Helper method to parse a Bubble template.
     *
     * @see Bubble_Parser::parse
     *
     * @param string $source
     *
     * @return array Token tree
     */
    private function parse($source)
    {
        $parser = $this->getParser();
        $parser->setPragmas($this->getPragmas());

        return $parser->parse($this->tokenize($source));
    }

    /**
     * Helper method to compile a Bubble template.
     *
     * @see Bubble_Compiler::compile
     *
     * @param string|Bubble_Source $source
     *
     * @return string generated Bubble template class code
     */
    private function compile($source)
    {
        $name = $this->getTemplateClassName($source);

        $this->log(
            Bubble_Logger::INFO,
            'Compiling template to "{className}" class',
            array('className' => $name)
        );

        if ($source instanceof Bubble_Source) {
            $source = $source->getSource();
        }
        $tree = $this->parse($source);

        $compiler = $this->getCompiler();
        $compiler->setPragmas($this->getPragmas());

        return $compiler->compile($source, $tree, $name, isset($this->escape), $this->charset, $this->strictCallables, $this->entityFlags);
    }

    /**
     * Add a log record if logging is enabled.
     *
     * @param int    $level   The logging level
     * @param string $message The log message
     * @param array  $context The log context
     */
    private function log($level, $message, array $context = array())
    {
        if (isset($this->logger)) {
            $this->logger->log($level, $message, $context);
        }
    }
}
