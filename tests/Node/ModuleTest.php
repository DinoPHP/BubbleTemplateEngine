<?php

namespace Bubble\Tests\Node;

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

use Bubble\Environment;
use Bubble\Loader\LoaderInterface;
use Bubble\Node\Expression\AssignNameExpression;
use Bubble\Node\Expression\ConditionalExpression;
use Bubble\Node\Expression\ConstantExpression;
use Bubble\Node\ImportNode;
use Bubble\Node\ModuleNode;
use Bubble\Node\Node;
use Bubble\Node\SetNode;
use Bubble\Node\TextNode;
use Bubble\Source;
use Bubble\Test\NodeTestCase;

class ModuleTest extends NodeTestCase
{
    public function testConstructor()
    {
        $body = new TextNode('foo', 1);
        $parent = new ConstantExpression('layout.bubble', 1);
        $blocks = new Node();
        $macros = new Node();
        $traits = new Node();
        $source = new Source('{{ foo }}', 'foo.bubble');
        $node = new ModuleNode($body, $parent, $blocks, $macros, $traits, new Node([]), $source);

        $this->assertEquals($body, $node->getNode('body'));
        $this->assertEquals($blocks, $node->getNode('blocks'));
        $this->assertEquals($macros, $node->getNode('macros'));
        $this->assertEquals($parent, $node->getNode('parent'));
        $this->assertEquals($source->getName(), $node->getTemplateName());
    }

    public function getTests()
    {
        $bubble = new Environment($this->createMock(LoaderInterface::class));

        $tests = [];

        $body = new TextNode('foo', 1);
        $extends = null;
        $blocks = new Node();
        $macros = new Node();
        $traits = new Node();
        $source = new Source('{{ foo }}', 'foo.bubble');

        $node = new ModuleNode($body, $extends, $blocks, $macros, $traits, new Node([]), $source);
        $tests[] = [$node, <<<EOF
<?php

use Bubble\Environment;
use Bubble\Error\LoaderError;
use Bubble\Error\RuntimeError;
use Bubble\Extension\SandboxExtension;
use Bubble\Markup;
use Bubble\Sandbox\SecurityError;
use Bubble\Sandbox\SecurityNotAllowedTagError;
use Bubble\Sandbox\SecurityNotAllowedFilterError;
use Bubble\Sandbox\SecurityNotAllowedFunctionError;
use Bubble\Source;
use Bubble\Template;

/* foo.bubble */
class __BubbleTemplate_%x extends Template
{
    private \$source;
    private \$macros = [];

    public function __construct(Environment \$env)
    {
        parent::__construct(\$env);

        \$this->source = \$this->getSourceContext();

        \$this->parent = false;

        \$this->blocks = [
        ];
    }

    protected function doDisplay(array \$context, array \$blocks = [])
    {
        \$macros = \$this->macros;
        // line 1
        echo "foo";
    }

    public function getTemplateName()
    {
        return "foo.bubble";
    }

    public function getDebugInfo()
    {
        return array (  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("", "foo.bubble", "");
    }
}
EOF
        , $bubble, true];

        $import = new ImportNode(new ConstantExpression('foo.bubble', 1), new AssignNameExpression('macro', 1), 2);

        $body = new Node([$import]);
        $extends = new ConstantExpression('layout.bubble', 1);

        $node = new ModuleNode($body, $extends, $blocks, $macros, $traits, new Node([]), $source);
        $tests[] = [$node, <<<EOF
<?php

use Bubble\Environment;
use Bubble\Error\LoaderError;
use Bubble\Error\RuntimeError;
use Bubble\Extension\SandboxExtension;
use Bubble\Markup;
use Bubble\Sandbox\SecurityError;
use Bubble\Sandbox\SecurityNotAllowedTagError;
use Bubble\Sandbox\SecurityNotAllowedFilterError;
use Bubble\Sandbox\SecurityNotAllowedFunctionError;
use Bubble\Source;
use Bubble\Template;

/* foo.bubble */
class __BubbleTemplate_%x extends Template
{
    private \$source;
    private \$macros = [];

    public function __construct(Environment \$env)
    {
        parent::__construct(\$env);

        \$this->source = \$this->getSourceContext();

        \$this->blocks = [
        ];
    }

    protected function doGetParent(array \$context)
    {
        // line 1
        return "layout.bubble";
    }

    protected function doDisplay(array \$context, array \$blocks = [])
    {
        \$macros = \$this->macros;
        // line 2
        \$macros["macro"] = \$this->macros["macro"] = \$this->loadTemplate("foo.bubble", "foo.bubble", 2)->unwrap();
        // line 1
        \$this->parent = \$this->loadTemplate("layout.bubble", "foo.bubble", 1);
        \$this->parent->display(\$context, array_merge(\$this->blocks, \$blocks));
    }

    public function getTemplateName()
    {
        return "foo.bubble";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  43 => 1,  41 => 2,  34 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("", "foo.bubble", "");
    }
}
EOF
        , $bubble, true];

        $set = new SetNode(false, new Node([new AssignNameExpression('foo', 4)]), new Node([new ConstantExpression('foo', 4)]), 4);
        $body = new Node([$set]);
        $extends = new ConditionalExpression(
                        new ConstantExpression(true, 2),
                        new ConstantExpression('foo', 2),
                        new ConstantExpression('foo', 2),
                        2
                    );

        $bubble = new Environment($this->createMock(LoaderInterface::class), ['debug' => true]);
        $node = new ModuleNode($body, $extends, $blocks, $macros, $traits, new Node([]), $source);
        $tests[] = [$node, <<<EOF
<?php

use Bubble\Environment;
use Bubble\Error\LoaderError;
use Bubble\Error\RuntimeError;
use Bubble\Extension\SandboxExtension;
use Bubble\Markup;
use Bubble\Sandbox\SecurityError;
use Bubble\Sandbox\SecurityNotAllowedTagError;
use Bubble\Sandbox\SecurityNotAllowedFilterError;
use Bubble\Sandbox\SecurityNotAllowedFunctionError;
use Bubble\Source;
use Bubble\Template;

/* foo.bubble */
class __BubbleTemplate_%x extends Template
{
    private \$source;
    private \$macros = [];

    public function __construct(Environment \$env)
    {
        parent::__construct(\$env);

        \$this->source = \$this->getSourceContext();

        \$this->blocks = [
        ];
    }

    protected function doGetParent(array \$context)
    {
        // line 2
        return \$this->loadTemplate(((true) ? ("foo") : ("foo")), "foo.bubble", 2);
    }

    protected function doDisplay(array \$context, array \$blocks = [])
    {
        \$macros = \$this->macros;
        // line 4
        \$context["foo"] = "foo";
        // line 2
        \$this->getParent(\$context)->display(\$context, array_merge(\$this->blocks, \$blocks));
    }

    public function getTemplateName()
    {
        return "foo.bubble";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  43 => 2,  41 => 4,  34 => 2,);
    }

    public function getSourceContext()
    {
        return new Source("{{ foo }}", "foo.bubble", "");
    }
}
EOF
        , $bubble, true];

        return $tests;
    }
}
