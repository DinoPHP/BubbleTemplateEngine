<?php

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

/**
 * Unknown template exception.
 */
class Bubble_Exception_UnknownTemplateException extends InvalidArgumentException implements Bubble_Exception
{
    protected $templateName;

    /**
     * @param string    $templateName
     * @param Exception $previous
     */
    public function __construct($templateName, Exception $previous = null)
    {
        $this->templateName = $templateName;
        $message = sprintf('Unknown template: %s', $templateName);
        if (version_compare(PHP_VERSION, '5.3.0', '>=')) {
            parent::__construct($message, 0, $previous);
        } else {
            parent::__construct($message); // @codeCoverageIgnore
        }
    }

    public function getTemplateName()
    {
        return $this->templateName;
    }
}
