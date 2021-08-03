<?php

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

/**
 * Unknown helper exception.
 */
class Bubble_Exception_UnknownHelperException extends InvalidArgumentException implements Bubble_Exception
{
    protected $helperName;

    /**
     * @param string    $helperName
     * @param Exception $previous
     */
    public function __construct($helperName, Exception $previous = null)
    {
        $this->helperName = $helperName;
        $message = sprintf('Unknown helper: %s', $helperName);
        if (version_compare(PHP_VERSION, '5.3.0', '>=')) {
            parent::__construct($message, 0, $previous);
        } else {
            parent::__construct($message); // @codeCoverageIgnore
        }
    }

    public function getHelperName()
    {
        return $this->helperName;
    }
}
