<?php

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

/**
 * @group unit
 */
class Bubble_Test_CompilerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getCompileValues
     */
    public function testCompile($source, array $tree, $name, $customEscaper, $entityFlags, $charset, $expected)
    {
        $compiler = new Bubble_Compiler();

        $compiled = $compiler->compile($source, $tree, $name, $customEscaper, $charset, false, $entityFlags);
        foreach ($expected as $contains) {
            $this->assertContains($contains, $compiled);
        }
    }

    public function getCompileValues()
    {
        return array(
            array('', array(), 'Banana', false, ENT_COMPAT, 'ISO-8859-1', array(
                "\nclass Banana extends Bubble_Template",
                'return $buffer;',
            )),

            array('', array($this->createTextToken('TEXT')), 'Monkey', false, ENT_COMPAT, 'UTF-8', array(
                "\nclass Monkey extends Bubble_Template",
                '$buffer .= $indent . \'TEXT\';',
                'return $buffer;',
            )),

            array(
                '',
                array(
                    array(
                        Bubble_Tokenizer::TYPE => Bubble_Tokenizer::T_ESCAPED,
                        Bubble_Tokenizer::NAME => 'name',
                    ),
                ),
                'Monkey',
                true,
                ENT_COMPAT,
                'ISO-8859-1',
                array(
                    "\nclass Monkey extends Bubble_Template",
                    '$value = $this->resolveValue($context->find(\'name\'), $context);',
                    '$buffer .= $indent . call_user_func($this->bubble->getEscape(), $value);',
                    'return $buffer;',
                ),
            ),

            array(
                '',
                array(
                    array(
                        Bubble_Tokenizer::TYPE => Bubble_Tokenizer::T_ESCAPED,
                        Bubble_Tokenizer::NAME => 'name',
                    ),
                ),
                'Monkey',
                false,
                ENT_COMPAT,
                'ISO-8859-1',
                array(
                    "\nclass Monkey extends Bubble_Template",
                    '$value = $this->resolveValue($context->find(\'name\'), $context);',
                    '$buffer .= $indent . htmlspecialchars($value, ' . ENT_COMPAT . ', \'ISO-8859-1\');',
                    'return $buffer;',
                ),
            ),

            array(
                '',
                array(
                    array(
                        Bubble_Tokenizer::TYPE => Bubble_Tokenizer::T_ESCAPED,
                        Bubble_Tokenizer::NAME => 'name',
                    ),
                ),
                'Monkey',
                false,
                ENT_QUOTES,
                'ISO-8859-1',
                array(
                    "\nclass Monkey extends Bubble_Template",
                    '$value = $this->resolveValue($context->find(\'name\'), $context);',
                    '$buffer .= $indent . htmlspecialchars($value, ' . ENT_QUOTES . ', \'ISO-8859-1\');',
                    'return $buffer;',
                ),
            ),

            array(
                '',
                array(
                    $this->createTextToken("foo\n"),
                    array(
                        Bubble_Tokenizer::TYPE => Bubble_Tokenizer::T_ESCAPED,
                        Bubble_Tokenizer::NAME => 'name',
                    ),
                    array(
                        Bubble_Tokenizer::TYPE => Bubble_Tokenizer::T_ESCAPED,
                        Bubble_Tokenizer::NAME => '.',
                    ),
                    $this->createTextToken("'bar'"),
                ),
                'Monkey',
                false,
                ENT_COMPAT,
                'UTF-8',
                array(
                    "\nclass Monkey extends Bubble_Template",
                    "\$buffer .= \$indent . 'foo\n';",
                    '$value = $this->resolveValue($context->find(\'name\'), $context);',
                    '$buffer .= htmlspecialchars($value, ' . ENT_COMPAT . ', \'UTF-8\');',
                    '$value = $this->resolveValue($context->last(), $context);',
                    '$buffer .= \'\\\'bar\\\'\';',
                    'return $buffer;',
                ),
            ),
        );
    }

    /**
     * @expectedException Bubble_Exception_SyntaxException
     */
    public function testCompilerThrowsSyntaxException()
    {
        $compiler = new Bubble_Compiler();
        $compiler->compile('', array(array(Bubble_Tokenizer::TYPE => 'invalid')), 'SomeClass');
    }

    /**
     * @param string $value
     */
    private function createTextToken($value)
    {
        return array(
            Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_TEXT,
            Bubble_Tokenizer::VALUE => $value,
        );
    }
}
