<?php

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

/**
 * Bubble Lambda Helper.
 *
 * Passed as the second argument to section lambdas (higher order sections),
 * giving them access to a `render` method for rendering a string with the
 * current context.
 */
class Bubble_LambdaHelper
{
    private $bubble;
    private $context;
    private $delims;

    /**
     * Bubble Lambda Helper constructor.
     *
     * @param Bubble_Engine  $bubble Bubble engine instance
     * @param Bubble_Context $context  Rendering context
     * @param string           $delims   Optional custom delimiters, in the format `{{= <% %> =}}`. (default: null)
     */
    public function __construct(Bubble_Engine $bubble, Bubble_Context $context, $delims = null)
    {
        $this->bubble = $bubble;
        $this->context  = $context;
        $this->delims   = $delims;
    }

    /**
     * Render a string as a Bubble template with the current rendering context.
     *
     * @param string $string
     *
     * @return string Rendered template
     */
    public function render($string)
    {
        return $this->bubble
            ->loadLambda((string) $string, $this->delims)
            ->renderInternal($this->context);
    }

    /**
     * Render a string as a Bubble template with the current rendering context.
     *
     * @param string $string
     *
     * @return string Rendered template
     */
    public function __invoke($string)
    {
        return $this->render($string);
    }

    /**
     * Get a Lambda Helper with custom delimiters.
     *
     * @param string $delims Custom delimiters, in the format `{{= <% %> =}}`
     *
     * @return Bubble_LambdaHelper
     */
    public function withDelimiters($delims)
    {
        return new self($this->bubble, $this->context, $delims);
    }
}
