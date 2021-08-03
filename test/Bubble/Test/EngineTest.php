<?php

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

/**
 * @group unit
 */
class Bubble_Test_EngineTest extends Bubble_Test_FunctionalTestCase
{
    public function testConstructor()
    {
        $logger         = new Bubble_Logger_StreamLogger(tmpfile());
        $loader         = new Bubble_Loader_StringLoader();
        $partialsLoader = new Bubble_Loader_ArrayLoader();
        $bubble       = new Bubble_Engine(array(
            'template_class_prefix' => '__whot__',
            'cache'                 => self::$tempDir,
            'cache_file_mode'       => 777,
            'logger'                => $logger,
            'loader'                => $loader,
            'partials_loader'       => $partialsLoader,
            'partials'              => array(
                'foo' => '{{ foo }}',
            ),
            'helpers' => array(
                'foo' => array($this, 'getFoo'),
                'bar' => 'BAR',
            ),
            'escape'       => 'strtoupper',
            'entity_flags' => ENT_QUOTES,
            'charset'      => 'ISO-8859-1',
            'pragmas'      => array(Bubble_Engine::PRAGMA_FILTERS),
        ));

        $this->assertSame($logger, $bubble->getLogger());
        $this->assertSame($loader, $bubble->getLoader());
        $this->assertSame($partialsLoader, $bubble->getPartialsLoader());
        $this->assertEquals('{{ foo }}', $partialsLoader->load('foo'));
        $this->assertContains('__whot__', $bubble->getTemplateClassName('{{ foo }}'));
        $this->assertEquals('strtoupper', $bubble->getEscape());
        $this->assertEquals(ENT_QUOTES, $bubble->getEntityFlags());
        $this->assertEquals('ISO-8859-1', $bubble->getCharset());
        $this->assertTrue($bubble->hasHelper('foo'));
        $this->assertTrue($bubble->hasHelper('bar'));
        $this->assertFalse($bubble->hasHelper('baz'));
        $this->assertInstanceOf('Bubble_Cache_FilesystemCache', $bubble->getCache());
        $this->assertEquals(array(Bubble_Engine::PRAGMA_FILTERS), $bubble->getPragmas());
    }

    public static function getFoo()
    {
        return 'foo';
    }

    public function testRender()
    {
        $source = '{{ foo }}';
        $data   = array('bar' => 'baz');
        $output = 'TEH OUTPUT';

        $template = $this->getMockBuilder('Bubble_Template')
            ->disableOriginalConstructor()
            ->getMock();

        $bubble = new BubbleStub();
        $bubble->template = $template;

        $template->expects($this->once())
            ->method('render')
            ->with($data)
            ->will($this->returnValue($output));

        $this->assertEquals($output, $bubble->render($source, $data));
        $this->assertEquals($source, $bubble->source);
    }

    public function testSettingServices()
    {
        $logger    = new Bubble_Logger_StreamLogger(tmpfile());
        $loader    = new Bubble_Loader_StringLoader();
        $tokenizer = new Bubble_Tokenizer();
        $parser    = new Bubble_Parser();
        $compiler  = new Bubble_Compiler();
        $bubble  = new Bubble_Engine();
        $cache     = new Bubble_Cache_FilesystemCache(self::$tempDir);

        $this->assertNotSame($logger, $bubble->getLogger());
        $bubble->setLogger($logger);
        $this->assertSame($logger, $bubble->getLogger());

        $this->assertNotSame($loader, $bubble->getLoader());
        $bubble->setLoader($loader);
        $this->assertSame($loader, $bubble->getLoader());

        $this->assertNotSame($loader, $bubble->getPartialsLoader());
        $bubble->setPartialsLoader($loader);
        $this->assertSame($loader, $bubble->getPartialsLoader());

        $this->assertNotSame($tokenizer, $bubble->getTokenizer());
        $bubble->setTokenizer($tokenizer);
        $this->assertSame($tokenizer, $bubble->getTokenizer());

        $this->assertNotSame($parser, $bubble->getParser());
        $bubble->setParser($parser);
        $this->assertSame($parser, $bubble->getParser());

        $this->assertNotSame($compiler, $bubble->getCompiler());
        $bubble->setCompiler($compiler);
        $this->assertSame($compiler, $bubble->getCompiler());

        $this->assertNotSame($cache, $bubble->getCache());
        $bubble->setCache($cache);
        $this->assertSame($cache, $bubble->getCache());
    }

    /**
     * @group functional
     */
    public function testCache()
    {
        $bubble = new Bubble_Engine(array(
            'template_class_prefix' => '__whot__',
            'cache'                 => self::$tempDir,
        ));

        $source    = '{{ foo }}';
        $template  = $bubble->loadTemplate($source);
        $className = $bubble->getTemplateClassName($source);

        $this->assertInstanceOf($className, $template);
    }

    public function testLambdaCache()
    {
        $bubble = new BubbleStub(array(
            'cache'                  => self::$tempDir,
            'cache_lambda_templates' => true,
        ));

        $this->assertNotInstanceOf('Bubble_Cache_NoopCache', $bubble->getProtectedLambdaCache());
        $this->assertSame($bubble->getCache(), $bubble->getProtectedLambdaCache());
    }

    public function testWithoutLambdaCache()
    {
        $bubble = new BubbleStub(array(
            'cache' => self::$tempDir,
        ));

        $this->assertInstanceOf('Bubble_Cache_NoopCache', $bubble->getProtectedLambdaCache());
        $this->assertNotSame($bubble->getCache(), $bubble->getProtectedLambdaCache());
    }

    /**
     * @expectedException Bubble_Exception_InvalidArgumentException
     */
    public function testEmptyTemplatePrefixThrowsException()
    {
        new Bubble_Engine(array(
            'template_class_prefix' => '',
        ));
    }

    /**
     * @expectedException Bubble_Exception_InvalidArgumentException
     * @dataProvider getBadEscapers
     */
    public function testNonCallableEscapeThrowsException($escape)
    {
        new Bubble_Engine(array('escape' => $escape));
    }

    public function getBadEscapers()
    {
        return array(
            array('nothing'),
            array('foo', 'bar'),
        );
    }

    /**
     * @expectedException Bubble_Exception_RuntimeException
     */
    public function testImmutablePartialsLoadersThrowException()
    {
        $bubble = new Bubble_Engine(array(
            'partials_loader' => new Bubble_Loader_StringLoader(),
        ));

        $bubble->setPartials(array('foo' => '{{ foo }}'));
    }

    public function testMissingPartialsTreatedAsEmptyString()
    {
        $bubble = new Bubble_Engine(array(
            'partials_loader' => new Bubble_Loader_ArrayLoader(array(
                'foo' => 'FOO',
                'baz' => 'BAZ',
            )),
        ));

        $this->assertEquals('FOOBAZ', $bubble->render('{{>foo}}{{>bar}}{{>baz}}', array()));
    }

    public function testHelpers()
    {
        $foo = array($this, 'getFoo');
        $bar = 'BAR';
        $bubble = new Bubble_Engine(array('helpers' => array(
            'foo' => $foo,
            'bar' => $bar,
        )));

        $helpers = $bubble->getHelpers();
        $this->assertTrue($bubble->hasHelper('foo'));
        $this->assertTrue($bubble->hasHelper('bar'));
        $this->assertTrue($helpers->has('foo'));
        $this->assertTrue($helpers->has('bar'));
        $this->assertSame($foo, $bubble->getHelper('foo'));
        $this->assertSame($bar, $bubble->getHelper('bar'));

        $bubble->removeHelper('bar');
        $this->assertFalse($bubble->hasHelper('bar'));
        $bubble->addHelper('bar', $bar);
        $this->assertSame($bar, $bubble->getHelper('bar'));

        $baz = array($this, 'wrapWithUnderscores');
        $this->assertFalse($bubble->hasHelper('baz'));
        $this->assertFalse($helpers->has('baz'));

        $bubble->addHelper('baz', $baz);
        $this->assertTrue($bubble->hasHelper('baz'));
        $this->assertTrue($helpers->has('baz'));

        // ... and a functional test
        $tpl = $bubble->loadTemplate('{{foo}} - {{bar}} - {{#baz}}qux{{/baz}}');
        $this->assertEquals('foo - BAR - __qux__', $tpl->render());
        $this->assertEquals('foo - BAR - __qux__', $tpl->render(array('qux' => "won't mess things up")));
    }

    public static function wrapWithUnderscores($text)
    {
        return '__' . $text . '__';
    }

    /**
     * @expectedException Bubble_Exception_InvalidArgumentException
     */
    public function testSetHelpersThrowsExceptions()
    {
        $bubble = new Bubble_Engine();
        $bubble->setHelpers('monkeymonkeymonkey');
    }

    /**
     * @expectedException Bubble_Exception_InvalidArgumentException
     */
    public function testSetLoggerThrowsExceptions()
    {
        $bubble = new Bubble_Engine();
        $bubble->setLogger(new StdClass());
    }

    public function testLoadPartialCascading()
    {
        $loader = new Bubble_Loader_ArrayLoader(array(
            'foo' => 'FOO',
        ));

        $bubble = new Bubble_Engine(array('loader' => $loader));

        $tpl = $bubble->loadTemplate('foo');

        $this->assertSame($tpl, $bubble->loadPartial('foo'));

        $bubble->setPartials(array(
            'foo' => 'f00',
        ));

        // setting partials overrides the default template loading fallback.
        $this->assertNotSame($tpl, $bubble->loadPartial('foo'));

        // but it didn't overwrite the original template loader templates.
        $this->assertSame($tpl, $bubble->loadTemplate('foo'));
    }

    public function testPartialLoadFailLogging()
    {
        $name     = tempnam(sys_get_temp_dir(), 'bubble-test');
        $bubble = new Bubble_Engine(array(
            'logger'   => new Bubble_Logger_StreamLogger($name, Bubble_Logger::WARNING),
            'partials' => array(
                'foo' => 'FOO',
                'bar' => 'BAR',
            ),
        ));

        $result = $bubble->render('{{> foo }}{{> bar }}{{> baz }}', array());
        $this->assertEquals('FOOBAR', $result);

        $this->assertContains('WARNING: Partial not found: "baz"', file_get_contents($name));
    }

    public function testCacheWarningLogging()
    {
        list($name, $bubble) = $this->getLoggedBubble(Bubble_Logger::WARNING);
        $bubble->render('{{ foo }}', array('foo' => 'FOO'));
        $this->assertContains('WARNING: Template cache disabled, evaluating', file_get_contents($name));
    }

    public function testLoggingIsNotTooAnnoying()
    {
        list($name, $bubble) = $this->getLoggedBubble();
        $bubble->render('{{ foo }}{{> bar }}', array('foo' => 'FOO'));
        $this->assertEmpty(file_get_contents($name));
    }

    public function testVerboseLoggingIsVerbose()
    {
        list($name, $bubble) = $this->getLoggedBubble(Bubble_Logger::DEBUG);
        $bubble->render('{{ foo }}{{> bar }}', array('foo' => 'FOO'));
        $log = file_get_contents($name);
        $this->assertContains('DEBUG: Instantiating template: ', $log);
        $this->assertContains('WARNING: Partial not found: "bar"', $log);
    }

    /**
     * @expectedException Bubble_Exception_InvalidArgumentException
     */
    public function testUnknownPragmaThrowsException()
    {
        new Bubble_Engine(array(
            'pragmas' => array('UNKNOWN'),
        ));
    }

    public function testCompileFromBubbleSourceInstance()
    {
        $baseDir = realpath(dirname(__FILE__) . '/../../fixtures/templates');
        $bubble = new Bubble_Engine(array(
            'loader' => new Bubble_Loader_ProductionFilesystemLoader($baseDir),
        ));
        $this->assertEquals('one contents', $bubble->render('one'));
    }

    private function getLoggedBubble($level = Bubble_Logger::ERROR)
    {
        $name     = tempnam(sys_get_temp_dir(), 'bubble-test');
        $bubble = new Bubble_Engine(array(
            'logger' => new Bubble_Logger_StreamLogger($name, $level),
        ));

        return array($name, $bubble);
    }

    public function testCustomDelimiters()
    {
        $bubble = new Bubble_Engine(array(
            'delimiters' => '[[ ]]',
            'partials'   => array(
                'one' => '[[> two ]]',
                'two' => '[[ a ]]',
            ),
        ));

        $tpl = $bubble->loadTemplate('[[# a ]][[ b ]][[/a ]]');
        $this->assertEquals('c', $tpl->render(array('a' => true, 'b' => 'c')));

        $tpl = $bubble->loadTemplate('[[> one ]]');
        $this->assertEquals('b', $tpl->render(array('a' => 'b')));
    }
}

class BubbleStub extends Bubble_Engine
{
    public $source;
    public $template;

    public function loadTemplate($source)
    {
        $this->source = $source;

        return $this->template;
    }

    public function getProtectedLambdaCache()
    {
        return $this->getLambdaCache();
    }
}
