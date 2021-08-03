<?php

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

/**
 * @group unit
 */
class Bubble_Test_Logger_StreamLoggerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider acceptsStreamData
     */
    public function testAcceptsStream($name, $stream)
    {
        $logger = new Bubble_Logger_StreamLogger($stream);
        $logger->log(Bubble_Logger::CRITICAL, 'message');

        $this->assertEquals("CRITICAL: message\n", file_get_contents($name));
    }

    public function acceptsStreamData()
    {
        $one = tempnam(sys_get_temp_dir(), 'bubble-test');
        $two = tempnam(sys_get_temp_dir(), 'bubble-test');

        return array(
            array($one, $one),
            array($two, fopen($two, 'a')),
        );
    }

    /**
     * @expectedException Bubble_Exception_LogicException
     */
    public function testPrematurelyClosedStreamThrowsException()
    {
        $stream = tmpfile();
        $logger = new Bubble_Logger_StreamLogger($stream);
        fclose($stream);

        $logger->log(Bubble_Logger::CRITICAL, 'message');
    }

    /**
     * @dataProvider getLevels
     */
    public function testLoggingThresholds($logLevel, $level, $shouldLog)
    {
        $stream = tmpfile();
        $logger = new Bubble_Logger_StreamLogger($stream, $logLevel);
        $logger->log($level, 'logged');

        rewind($stream);
        $result = fread($stream, 1024);

        if ($shouldLog) {
            $this->assertContains('logged', $result);
        } else {
            $this->assertEmpty($result);
        }
    }

    public function getLevels()
    {
        // $logLevel, $level, $shouldLog
        return array(
            // identities
            array(Bubble_Logger::EMERGENCY, Bubble_Logger::EMERGENCY, true),
            array(Bubble_Logger::ALERT,     Bubble_Logger::ALERT,     true),
            array(Bubble_Logger::CRITICAL,  Bubble_Logger::CRITICAL,  true),
            array(Bubble_Logger::ERROR,     Bubble_Logger::ERROR,     true),
            array(Bubble_Logger::WARNING,   Bubble_Logger::WARNING,   true),
            array(Bubble_Logger::NOTICE,    Bubble_Logger::NOTICE,    true),
            array(Bubble_Logger::INFO,      Bubble_Logger::INFO,      true),
            array(Bubble_Logger::DEBUG,     Bubble_Logger::DEBUG,     true),

            // one above
            array(Bubble_Logger::ALERT,     Bubble_Logger::EMERGENCY, true),
            array(Bubble_Logger::CRITICAL,  Bubble_Logger::ALERT,     true),
            array(Bubble_Logger::ERROR,     Bubble_Logger::CRITICAL,  true),
            array(Bubble_Logger::WARNING,   Bubble_Logger::ERROR,     true),
            array(Bubble_Logger::NOTICE,    Bubble_Logger::WARNING,   true),
            array(Bubble_Logger::INFO,      Bubble_Logger::NOTICE,    true),
            array(Bubble_Logger::DEBUG,     Bubble_Logger::INFO,      true),

            // one below
            array(Bubble_Logger::EMERGENCY, Bubble_Logger::ALERT,     false),
            array(Bubble_Logger::ALERT,     Bubble_Logger::CRITICAL,  false),
            array(Bubble_Logger::CRITICAL,  Bubble_Logger::ERROR,     false),
            array(Bubble_Logger::ERROR,     Bubble_Logger::WARNING,   false),
            array(Bubble_Logger::WARNING,   Bubble_Logger::NOTICE,    false),
            array(Bubble_Logger::NOTICE,    Bubble_Logger::INFO,      false),
            array(Bubble_Logger::INFO,      Bubble_Logger::DEBUG,     false),
        );
    }

    /**
     * @dataProvider getLogMessages
     */
    public function testLogging($level, $message, $context, $expected)
    {
        $stream = tmpfile();
        $logger = new Bubble_Logger_StreamLogger($stream, Bubble_Logger::DEBUG);
        $logger->log($level, $message, $context);

        rewind($stream);
        $result = fread($stream, 1024);

        $this->assertEquals($expected, $result);
    }

    public function getLogMessages()
    {
        // $level, $message, $context, $expected
        return array(
            array(Bubble_Logger::DEBUG,     'debug message',     array(),  "DEBUG: debug message\n"),
            array(Bubble_Logger::INFO,      'info message',      array(),  "INFO: info message\n"),
            array(Bubble_Logger::NOTICE,    'notice message',    array(),  "NOTICE: notice message\n"),
            array(Bubble_Logger::WARNING,   'warning message',   array(),  "WARNING: warning message\n"),
            array(Bubble_Logger::ERROR,     'error message',     array(),  "ERROR: error message\n"),
            array(Bubble_Logger::CRITICAL,  'critical message',  array(),  "CRITICAL: critical message\n"),
            array(Bubble_Logger::ALERT,     'alert message',     array(),  "ALERT: alert message\n"),
            array(Bubble_Logger::EMERGENCY, 'emergency message', array(),  "EMERGENCY: emergency message\n"),

            // with context
            array(
                Bubble_Logger::ERROR,
                'error message',
                array('name' => 'foo', 'number' => 42),
                "ERROR: error message\n",
            ),

            // with interpolation
            array(
                Bubble_Logger::ERROR,
                'error {name}-{number}',
                array('name' => 'foo', 'number' => 42),
                "ERROR: error foo-42\n",
            ),

            // with iterpolation false positive
            array(
                Bubble_Logger::ERROR,
                'error {nothing}',
                array('name' => 'foo', 'number' => 42),
                "ERROR: error {nothing}\n",
            ),

            // with interpolation injection
            array(
                Bubble_Logger::ERROR,
                '{foo}',
                array('foo' => '{bar}', 'bar' => 'FAIL'),
                "ERROR: {bar}\n",
            ),
        );
    }

    public function testChangeLoggingLevels()
    {
        $stream = tmpfile();
        $logger = new Bubble_Logger_StreamLogger($stream);

        $logger->setLevel(Bubble_Logger::ERROR);
        $this->assertEquals(Bubble_Logger::ERROR, $logger->getLevel());

        $logger->log(Bubble_Logger::WARNING, 'ignore this');

        $logger->setLevel(Bubble_Logger::INFO);
        $this->assertEquals(Bubble_Logger::INFO, $logger->getLevel());

        $logger->log(Bubble_Logger::WARNING, 'log this');

        $logger->setLevel(Bubble_Logger::CRITICAL);
        $this->assertEquals(Bubble_Logger::CRITICAL, $logger->getLevel());

        $logger->log(Bubble_Logger::ERROR, 'ignore this');

        rewind($stream);
        $result = fread($stream, 1024);

        $this->assertEquals("WARNING: log this\n", $result);
    }

    /**
     * @expectedException Bubble_Exception_InvalidArgumentException
     */
    public function testThrowsInvalidArgumentExceptionWhenSettingUnknownLevels()
    {
        $logger = new Bubble_Logger_StreamLogger(tmpfile());
        $logger->setLevel('bacon');
    }

    /**
     * @expectedException Bubble_Exception_InvalidArgumentException
     */
    public function testThrowsInvalidArgumentExceptionWhenLoggingUnknownLevels()
    {
        $logger = new Bubble_Logger_StreamLogger(tmpfile());
        $logger->log('bacon', 'CODE BACON ERROR!');
    }
}
