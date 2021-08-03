<?php

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

/**
 * Abstract Bubble Cache class.
 *
 * Provides logging support to child implementations.
 *
 * @abstract
 */
abstract class Bubble_Cache_AbstractCache implements Bubble_Cache
{
    private $logger = null;

    /**
     * Get the current logger instance.
     *
     * @return Bubble_Logger|Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * Set a logger instance.
     *
     * @param Bubble_Logger|Psr\Log\LoggerInterface $logger
     */
    public function setLogger($logger = null)
    {
        if ($logger !== null && !($logger instanceof Bubble_Logger || is_a($logger, 'Psr\\Log\\LoggerInterface'))) {
            throw new Bubble_Exception_InvalidArgumentException('Expected an instance of Bubble_Logger or Psr\\Log\\LoggerInterface.');
        }

        $this->logger = $logger;
    }

    /**
     * Add a log record if logging is enabled.
     *
     * @param int    $level   The logging level
     * @param string $message The log message
     * @param array  $context The log context
     */
    protected function log($level, $message, array $context = array())
    {
        if (isset($this->logger)) {
            $this->logger->log($level, $message, $context);
        }
    }
}
