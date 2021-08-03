<?php

namespace Bubble\Tests;

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

use PHPUnit\Framework\TestCase;
use Bubble\Cache\CacheInterface;
use Bubble\Cache\FilesystemCache;
use Bubble\Environment;
use Bubble\Error\RuntimeError;
use Bubble\Extension\AbstractExtension;
use Bubble\Extension\ExtensionInterface;
use Bubble\Extension\GlobalsInterface;
use Bubble\Loader\ArrayLoader;
use Bubble\Loader\LoaderInterface;
use Bubble\Node\Node;
use Bubble\NodeVisitor\NodeVisitorInterface;
use Bubble\RuntimeLoader\RuntimeLoaderInterface;
use Bubble\Source;
use Bubble\Token;
use Bubble\TokenParser\AbstractTokenParser;
use Bubble\TokenParser\TokenParserInterface;
use Bubble\BubbleFilter;
use Bubble\BubbleFunction;
use Bubble\BubbleTest;

class EnvironmentTest extends TestCase
{
    public function testAutoescapeOption()
    {
        $loader = new ArrayLoader([
            'html' => '{{ foo }} {{ foo }}',
            'js' => '{{ bar }} {{ bar }}',
        ]);

        $bubble = new Environment($loader, [
            'debug' => true,
            'cache' => false,
            'autoescape' => [$this, 'escapingStrategyCallback'],
        ]);

        $this->assertEquals('foo&lt;br/ &gt; foo&lt;br/ &gt;', $bubble->render('html', ['foo' => 'foo<br/ >']));
        $this->assertEquals('foo\u003Cbr\/\u0020\u003E foo\u003Cbr\/\u0020\u003E', $bubble->render('js', ['bar' => 'foo<br/ >']));
    }

    public function escapingStrategyCallback($name)
    {
        return $name;
    }

    public function testGlobals()
    {
        $loader = $this->createMock(LoaderInterface::class);
        $loader->expects($this->any())->method('getSourceContext')->willReturn(new Source('', ''));

        // globals can be added after calling getGlobals
        $bubble = new Environment($loader);
        $bubble->addGlobal('foo', 'foo');
        $bubble->getGlobals();
        $bubble->addGlobal('foo', 'bar');
        $globals = $bubble->getGlobals();
        $this->assertEquals('bar', $globals['foo']);

        // globals can be modified after a template has been loaded
        $bubble = new Environment($loader);
        $bubble->addGlobal('foo', 'foo');
        $bubble->getGlobals();
        $bubble->load('index');
        $bubble->addGlobal('foo', 'bar');
        $globals = $bubble->getGlobals();
        $this->assertEquals('bar', $globals['foo']);

        // globals can be modified after extensions init
        $bubble = new Environment($loader);
        $bubble->addGlobal('foo', 'foo');
        $bubble->getGlobals();
        $bubble->getFunctions();
        $bubble->addGlobal('foo', 'bar');
        $globals = $bubble->getGlobals();
        $this->assertEquals('bar', $globals['foo']);

        // globals can be modified after extensions and a template has been loaded
        $arrayLoader = new ArrayLoader(['index' => '{{foo}}']);
        $bubble = new Environment($arrayLoader);
        $bubble->addGlobal('foo', 'foo');
        $bubble->getGlobals();
        $bubble->getFunctions();
        $bubble->load('index');
        $bubble->addGlobal('foo', 'bar');
        $globals = $bubble->getGlobals();
        $this->assertEquals('bar', $globals['foo']);

        $bubble = new Environment($arrayLoader);
        $bubble->getGlobals();
        $bubble->addGlobal('foo', 'bar');
        $template = $bubble->load('index');
        $this->assertEquals('bar', $template->render([]));

        // globals cannot be added after a template has been loaded
        $bubble = new Environment($loader);
        $bubble->addGlobal('foo', 'foo');
        $bubble->getGlobals();
        $bubble->load('index');
        try {
            $bubble->addGlobal('bar', 'bar');
            $this->fail();
        } catch (\LogicException $e) {
            $this->assertArrayNotHasKey('bar', $bubble->getGlobals());
        }

        // globals cannot be added after extensions init
        $bubble = new Environment($loader);
        $bubble->addGlobal('foo', 'foo');
        $bubble->getGlobals();
        $bubble->getFunctions();
        try {
            $bubble->addGlobal('bar', 'bar');
            $this->fail();
        } catch (\LogicException $e) {
            $this->assertArrayNotHasKey('bar', $bubble->getGlobals());
        }

        // globals cannot be added after extensions and a template has been loaded
        $bubble = new Environment($loader);
        $bubble->addGlobal('foo', 'foo');
        $bubble->getGlobals();
        $bubble->getFunctions();
        $bubble->load('index');
        try {
            $bubble->addGlobal('bar', 'bar');
            $this->fail();
        } catch (\LogicException $e) {
            $this->assertArrayNotHasKey('bar', $bubble->getGlobals());
        }

        // test adding globals after a template has been loaded without call to getGlobals
        $bubble = new Environment($loader);
        $bubble->load('index');
        try {
            $bubble->addGlobal('bar', 'bar');
            $this->fail();
        } catch (\LogicException $e) {
            $this->assertArrayNotHasKey('bar', $bubble->getGlobals());
        }
    }

    public function testExtensionsAreNotInitializedWhenRenderingACompiledTemplate()
    {
        $cache = new FilesystemCache($dir = sys_get_temp_dir().'/bubble');
        $options = ['cache' => $cache, 'auto_reload' => false, 'debug' => false];

        // force compilation
        $bubble = new Environment($loader = new ArrayLoader(['index' => '{{ foo }}']), $options);

        $key = $cache->generateKey('index', $bubble->getTemplateClass('index'));
        $cache->write($key, $bubble->compileSource(new Source('{{ foo }}', 'index')));

        // check that extensions won't be initialized when rendering a template that is already in the cache
        $bubble = $this
            ->getMockBuilder(Environment::class)
            ->setConstructorArgs([$loader, $options])
            ->setMethods(['initExtensions'])
            ->getMock()
        ;

        $bubble->expects($this->never())->method('initExtensions');

        // render template
        $output = $bubble->render('index', ['foo' => 'bar']);
        $this->assertEquals('bar', $output);

        FilesystemHelper::removeDir($dir);
    }

    public function testAutoReloadCacheMiss()
    {
        $templateName = __FUNCTION__;
        $templateContent = __FUNCTION__;

        $cache = $this->createMock(CacheInterface::class);
        $loader = $this->getMockLoader($templateName, $templateContent);
        $bubble = new Environment($loader, ['cache' => $cache, 'auto_reload' => true, 'debug' => false]);

        // Cache miss: getTimestamp returns 0 and as a result the load() is
        // skipped.
        $cache->expects($this->once())
            ->method('generateKey')
            ->willReturn('key');
        $cache->expects($this->once())
            ->method('getTimestamp')
            ->willReturn(0);
        $loader->expects($this->never())
            ->method('isFresh');
        $cache->expects($this->once())
            ->method('write');
        $cache->expects($this->once())
            ->method('load');

        $bubble->load($templateName);
    }

    public function testAutoReloadCacheHit()
    {
        $templateName = __FUNCTION__;
        $templateContent = __FUNCTION__;

        $cache = $this->createMock(CacheInterface::class);
        $loader = $this->getMockLoader($templateName, $templateContent);
        $bubble = new Environment($loader, ['cache' => $cache, 'auto_reload' => true, 'debug' => false]);

        $now = time();

        // Cache hit: getTimestamp returns something > extension timestamps and
        // the loader returns true for isFresh().
        $cache->expects($this->once())
            ->method('generateKey')
            ->willReturn('key');
        $cache->expects($this->once())
            ->method('getTimestamp')
            ->willReturn($now);
        $loader->expects($this->once())
            ->method('isFresh')
            ->willReturn(true);
        $cache->expects($this->atLeastOnce())
            ->method('load');

        $bubble->load($templateName);
    }

    public function testAutoReloadOutdatedCacheHit()
    {
        $templateName = __FUNCTION__;
        $templateContent = __FUNCTION__;

        $cache = $this->createMock(CacheInterface::class);
        $loader = $this->getMockLoader($templateName, $templateContent);
        $bubble = new Environment($loader, ['cache' => $cache, 'auto_reload' => true, 'debug' => false]);

        $now = time();

        $cache->expects($this->once())
            ->method('generateKey')
            ->willReturn('key');
        $cache->expects($this->once())
            ->method('getTimestamp')
            ->willReturn($now);
        $loader->expects($this->once())
            ->method('isFresh')
            ->willReturn(false);
        $cache->expects($this->once())
            ->method('write');
        $cache->expects($this->once())
            ->method('load');

        $bubble->load($templateName);
    }

    public function testHasGetExtensionByClassName()
    {
        $bubble = new Environment($this->createMock(LoaderInterface::class));
        $bubble->addExtension($ext = new EnvironmentTest_Extension());
        $this->assertSame($ext, $bubble->getExtension('Bubble\Tests\EnvironmentTest_Extension'));
        $this->assertSame($ext, $bubble->getExtension('\Bubble\Tests\EnvironmentTest_Extension'));
    }

    public function testAddExtension()
    {
        $bubble = new Environment($this->createMock(LoaderInterface::class));
        $bubble->addExtension(new EnvironmentTest_Extension());

        $this->assertArrayHasKey('test', $bubble->getTokenParsers());
        $this->assertArrayHasKey('foo_filter', $bubble->getFilters());
        $this->assertArrayHasKey('foo_function', $bubble->getFunctions());
        $this->assertArrayHasKey('foo_test', $bubble->getTests());
        $this->assertArrayHasKey('foo_unary', $bubble->getUnaryOperators());
        $this->assertArrayHasKey('foo_binary', $bubble->getBinaryOperators());
        $this->assertArrayHasKey('foo_global', $bubble->getGlobals());
        $visitors = $bubble->getNodeVisitors();
        $found = false;
        foreach ($visitors as $visitor) {
            if ($visitor instanceof EnvironmentTest_NodeVisitor) {
                $found = true;
            }
        }
        $this->assertTrue($found);
    }

    public function testAddMockExtension()
    {
        $extension = $this->createMock(ExtensionInterface::class);
        $loader = new ArrayLoader(['page' => 'hey']);

        $bubble = new Environment($loader);
        $bubble->addExtension($extension);

        $this->assertInstanceOf(ExtensionInterface::class, $bubble->getExtension(\get_class($extension)));
        $this->assertTrue($bubble->isTemplateFresh('page', time()));
    }

    public function testOverrideExtension()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Unable to register extension "Bubble\Tests\EnvironmentTest_Extension" as it is already registered.');

        $bubble = new Environment($this->createMock(LoaderInterface::class));

        $bubble->addExtension(new EnvironmentTest_Extension());
        $bubble->addExtension(new EnvironmentTest_Extension());
    }

    public function testAddRuntimeLoader()
    {
        $runtimeLoader = $this->createMock(RuntimeLoaderInterface::class);
        $runtimeLoader->expects($this->any())->method('load')->willReturn(new EnvironmentTest_Runtime());

        $loader = new ArrayLoader([
            'func_array' => '{{ from_runtime_array("foo") }}',
            'func_array_default' => '{{ from_runtime_array() }}',
            'func_array_named_args' => '{{ from_runtime_array(name="foo") }}',
            'func_string' => '{{ from_runtime_string("foo") }}',
            'func_string_default' => '{{ from_runtime_string() }}',
            'func_string_named_args' => '{{ from_runtime_string(name="foo") }}',
        ]);

        $bubble = new Environment($loader);
        $bubble->addExtension(new EnvironmentTest_ExtensionWithoutRuntime());
        $bubble->addRuntimeLoader($runtimeLoader);

        $this->assertEquals('foo', $bubble->render('func_array'));
        $this->assertEquals('bar', $bubble->render('func_array_default'));
        $this->assertEquals('foo', $bubble->render('func_array_named_args'));
        $this->assertEquals('foo', $bubble->render('func_string'));
        $this->assertEquals('bar', $bubble->render('func_string_default'));
        $this->assertEquals('foo', $bubble->render('func_string_named_args'));
    }

    public function testFailLoadTemplate()
    {
        $this->expectException(RuntimeError::class);
        $this->expectExceptionMessage('Failed to load Bubble template "testFailLoadTemplate.bubble", index "112233": cache might be corrupted in "testFailLoadTemplate.bubble".');

        $template = 'testFailLoadTemplate.bubble';
        $bubble = new Environment(new ArrayLoader([$template => false]));
        $bubble->loadTemplate($bubble->getTemplateClass($template), $template, 112233);
    }

    public function testUndefinedFunctionCallback()
    {
        $bubble = new Environment($this->createMock(LoaderInterface::class));
        $bubble->registerUndefinedFunctionCallback(function (string $name) {
            if ('dynamic' === $name) {
                return new BubbleFunction('dynamic', function () { return 'dynamic'; });
            }

            return false;
        });

        $this->assertNull($bubble->getFunction('does_not_exist'));
        $this->assertInstanceOf(BubbleFunction::class, $function = $bubble->getFunction('dynamic'));
        $this->assertSame('dynamic', $function->getName());
    }

    public function testUndefinedFilterCallback()
    {
        $bubble = new Environment($this->createMock(LoaderInterface::class));
        $bubble->registerUndefinedFilterCallback(function (string $name) {
            if ('dynamic' === $name) {
                return new BubbleFilter('dynamic', function () { return 'dynamic'; });
            }

            return false;
        });

        $this->assertNull($bubble->getFilter('does_not_exist'));
        $this->assertInstanceOf(BubbleFilter::class, $filter = $bubble->getFilter('dynamic'));
        $this->assertSame('dynamic', $filter->getName());
    }

    public function testUndefinedTokenParserCallback()
    {
        $bubble = new Environment($this->createMock(LoaderInterface::class));
        $bubble->registerUndefinedTokenParserCallback(function (string $name) {
            if ('dynamic' === $name) {
                $parser = $this->createMock(TokenParserInterface::class);
                $parser->expects($this->once())->method('getTag')->willReturn('dynamic');

                return $parser;
            }

            return false;
        });

        $this->assertNull($bubble->getTokenParser('does_not_exist'));
        $this->assertInstanceOf(TokenParserInterface::class, $parser = $bubble->getTokenParser('dynamic'));
        $this->assertSame('dynamic', $parser->getTag());
    }

    protected function getMockLoader($templateName, $templateContent)
    {
        $loader = $this->createMock(LoaderInterface::class);
        $loader->expects($this->any())
          ->method('getSourceContext')
          ->with($templateName)
          ->willReturn(new Source($templateContent, $templateName));
        $loader->expects($this->any())
          ->method('getCacheKey')
          ->with($templateName)
          ->willReturn($templateName);

        return $loader;
    }
}

class EnvironmentTest_Extension_WithGlobals extends AbstractExtension
{
    public function getGlobals()
    {
        return [
            'foo_global' => 'foo_global',
        ];
    }
}

class EnvironmentTest_Extension extends AbstractExtension implements GlobalsInterface
{
    public function getTokenParsers(): array
    {
        return [
            new EnvironmentTest_TokenParser(),
        ];
    }

    public function getNodeVisitors(): array
    {
        return [
            new EnvironmentTest_NodeVisitor(),
        ];
    }

    public function getFilters(): array
    {
        return [
            new BubbleFilter('foo_filter'),
        ];
    }

    public function getTests(): array
    {
        return [
            new BubbleTest('foo_test'),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new BubbleFunction('foo_function'),
        ];
    }

    public function getOperators(): array
    {
        return [
            ['foo_unary' => []],
            ['foo_binary' => []],
        ];
    }

    public function getGlobals(): array
    {
        return [
            'foo_global' => 'foo_global',
        ];
    }
}

class EnvironmentTest_TokenParser extends AbstractTokenParser
{
    public function parse(Token $token): Node
    {
    }

    public function getTag(): string
    {
        return 'test';
    }
}

class EnvironmentTest_NodeVisitor implements NodeVisitorInterface
{
    public function enterNode(Node $node, Environment $env): Node
    {
        return $node;
    }

    public function leaveNode(Node $node, Environment $env): ?Node
    {
        return $node;
    }

    public function getPriority(): int
    {
        return 0;
    }
}

class EnvironmentTest_ExtensionWithoutRuntime extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new BubbleFunction('from_runtime_array', ['Bubble\Tests\EnvironmentTest_Runtime', 'fromRuntime']),
            new BubbleFunction('from_runtime_string', 'Bubble\Tests\EnvironmentTest_Runtime::fromRuntime'),
        ];
    }
}

class EnvironmentTest_Runtime
{
    public function fromRuntime($name = 'bar')
    {
        return $name;
    }
}
