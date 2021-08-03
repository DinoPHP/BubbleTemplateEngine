<?php

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

abstract class Bubble_Test_SpecTestCase extends PHPUnit_Framework_TestCase
{
    protected static $bubble;

    public static function setUpBeforeClass()
    {
        self::$bubble = new Bubble_Engine();
    }

    protected static function loadTemplate($source, $partials)
    {
        self::$bubble->setPartials($partials);

        return self::$bubble->loadTemplate($source);
    }

    /**
     * Data provider for the bubble spec test.
     *
     * Loads YAML files from the spec and converts them to PHPisms.
     *
     * @param string $name
     *
     * @return array
     */
    protected function loadSpec($name)
    {
        $filename = dirname(__FILE__) . '/../../../vendor/spec/specs/' . $name . '.yml';
        if (!file_exists($filename)) {
            return array();
        }

        $data = array();
        $yaml = new sfYamlParser();
        $file = file_get_contents($filename);

        // @hack: pre-process the 'lambdas' spec so the Symfony YAML parser doesn't complain.
        if ($name === '~lambdas') {
            $file = str_replace(" !code\n", "\n", $file);
        }

        $spec = $yaml->parse($file);

        foreach ($spec['tests'] as $test) {
            $data[] = array(
                $test['name'] . ': ' . $test['desc'],
                $test['template'],
                isset($test['partials']) ? $test['partials'] : array(),
                $test['data'],
                $test['expected'],
            );
        }

        return $data;
    }
}
