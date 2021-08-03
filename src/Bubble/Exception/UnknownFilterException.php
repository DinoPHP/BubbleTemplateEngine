<?php

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

/**
 * Unknown filter exception.
 */
class Bubble_Exception_UnknownFilterException extends UnexpectedValueException implements Bubble_Exception
{
    protected $filterName;

    /**
     * @param string    $filterName
     * @param Exception $previous
     */
    public function __construct($filterName, Exception $previous = null)
    {
        $this->filterName = $filterName;
        $message = sprintf('Unknown filter: %s', $filterName);
        if (version_compare(PHP_VERSION, '5.3.0', '>=')) {
            parent::__construct($message, 0, $previous);
        } else {
            parent::__construct($message); // @codeCoverageIgnore
        }
    }

    public function getFilterName()
    {
        return $this->filterName;
    }
}
