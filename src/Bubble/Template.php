<?php

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

/**
 * Abstract Bubble Template class.
 *
 * @abstract
 */
abstract class Bubble_Template
{
    /**
     * @var Bubble_Engine
     */
    protected $bubble;

    /**
     * @var bool
     */
    protected $strictCallables = false;

    /**
     * Bubble Template constructor.
     *
     * @param Bubble_Engine $bubble
     */
    public function __construct(Bubble_Engine $bubble)
    {
        $this->bubble = $bubble;
    }

    /**
     * Bubble Template instances can be treated as a function and rendered by simply calling them.
     *
     *     $m = new Bubble_Engine;
     *     $tpl = $m->loadTemplate('Hello, {{ name }}!');
     *     echo $tpl(array('name' => 'World')); // "Hello, World!"
     *
     * @see Bubble_Template::render
     *
     * @param mixed $context Array or object rendering context (default: array())
     *
     * @return string Rendered template
     */
    public function __invoke($context = array())
    {
        return $this->render($context);
    }

    /**
     * Render this template given the rendering context.
     *
     * @param mixed $context Array or object rendering context (default: array())
     *
     * @return string Rendered template
     */
    public function render($context = array())
    {
        return $this->renderInternal(
            $this->prepareContextStack($context)
        );
    }

    /**
     * Internal rendering method implemented by Bubble Template concrete subclasses.
     *
     * This is where the magic happens :)
     *
     * NOTE: This method is not part of the Bubble.php public API.
     *
     * @param Bubble_Context $context
     * @param string           $indent  (default: '')
     *
     * @return string Rendered template
     */
    abstract public function renderInternal(Bubble_Context $context, $indent = '');

    /**
     * Tests whether a value should be iterated over (e.g. in a section context).
     *
     * In most languages there are two distinct array types: list and hash (or whatever you want to call them). Lists
     * should be iterated, hashes should be treated as objects. Bubble follows this paradigm for Ruby, Javascript,
     * Java, Python, etc.
     *
     * PHP, however, treats lists and hashes as one primitive type: array. So Bubble.php needs a way to distinguish
     * between between a list of things (numeric, normalized array) and a set of variables to be used as section context
     * (associative array). In other words, this will be iterated over:
     *
     *     $items = array(
     *         array('name' => 'foo'),
     *         array('name' => 'bar'),
     *         array('name' => 'baz'),
     *     );
     *
     * ... but this will be used as a section context block:
     *
     *     $items = array(
     *         1        => array('name' => 'foo'),
     *         'banana' => array('name' => 'bar'),
     *         42       => array('name' => 'baz'),
     *     );
     *
     * @param mixed $value
     *
     * @return bool True if the value is 'iterable'
     */
    protected function isIterable($value)
    {
        switch (gettype($value)) {
            case 'object':
                return $value instanceof Traversable;

            case 'array':
                $i = 0;
                foreach ($value as $k => $v) {
                    if ($k !== $i++) {
                        return false;
                    }
                }

                return true;

            default:
                return false;
        }
    }

    /**
     * Helper method to prepare the Context stack.
     *
     * Adds the Bubble HelperCollection to the stack's top context frame if helpers are present.
     *
     * @param mixed $context Optional first context frame (default: null)
     *
     * @return Bubble_Context
     */
    protected function prepareContextStack($context = null)
    {
        $stack = new Bubble_Context();

        $helpers = $this->bubble->getHelpers();
        if (!$helpers->isEmpty()) {
            $stack->push($helpers);
        }

        if (!empty($context)) {
            $stack->push($context);
        }

        return $stack;
    }

    /**
     * Resolve a context value.
     *
     * Invoke the value if it is callable, otherwise return the value.
     *
     * @param mixed            $value
     * @param Bubble_Context $context
     *
     * @return string
     */
    protected function resolveValue($value, Bubble_Context $context)
    {
        if (($this->strictCallables ? is_object($value) : !is_string($value)) && is_callable($value)) {
            return $this->bubble
                ->loadLambda((string) call_user_func($value))
                ->renderInternal($context);
        }

        return $value;
    }
}
