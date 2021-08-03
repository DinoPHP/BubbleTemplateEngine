<?php

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

/**
 * @group unit
 */
class Bubble_Test_Logger_AbstractLoggerTest extends PHPUnit_Framework_TestCase
{
    public function testEverything()
    {
        $logger = new Bubble_Test_Logger_TestLogger();

        $logger->emergency('emergency message');
        $logger->alert('alert message');
        $logger->critical('critical message');
        $logger->error('error message');
        $logger->warning('warning message');
        $logger->notice('notice message');
        $logger->info('info message');
        $logger->debug('debug message');

        $expected = array(
            array(Bubble_Logger::EMERGENCY, 'emergency message', array()),
            array(Bubble_Logger::ALERT, 'alert message', array()),
            array(Bubble_Logger::CRITICAL, 'critical message', array()),
            array(Bubble_Logger::ERROR, 'error message', array()),
            array(Bubble_Logger::WARNING, 'warning message', array()),
            array(Bubble_Logger::NOTICE, 'notice message', array()),
            array(Bubble_Logger::INFO, 'info message', array()),
            array(Bubble_Logger::DEBUG, 'debug message', array()),
        );

        $this->assertEquals($expected, $logger->log);
    }
}

class Bubble_Test_Logger_TestLogger extends Bubble_Logger_AbstractLogger
{
    public $log = array();

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     */
    public function log($level, $message, array $context = array())
    {
        $this->log[] = array($level, $message, $context);
    }
}
