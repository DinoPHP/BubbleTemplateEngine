<?php

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

/**
 * @group unit
 */
class Bubble_Test_TokenizerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getTokens
     */
    public function testScan($text, $delimiters, $expected)
    {
        $tokenizer = new Bubble_Tokenizer();
        $this->assertSame($expected, $tokenizer->scan($text, $delimiters));
    }

    /**
     * @expectedException Bubble_Exception_SyntaxException
     */
    public function testUnevenBracesThrowExceptions()
    {
        $tokenizer = new Bubble_Tokenizer();

        $text = '{{{ name }}';
        $tokenizer->scan($text, null);
    }

    /**
     * @expectedException Bubble_Exception_SyntaxException
     */
    public function testUnevenBracesWithCustomDelimiterThrowExceptions()
    {
        $tokenizer = new Bubble_Tokenizer();

        $text = '<%{ name %>';
        $tokenizer->scan($text, '<% %>');
    }

    public function getTokens()
    {
        return array(
            array(
                'text',
                null,
                array(
                    array(
                        Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_TEXT,
                        Bubble_Tokenizer::LINE  => 0,
                        Bubble_Tokenizer::VALUE => 'text',
                    ),
                ),
            ),

            array(
                'text',
                '<<< >>>',
                array(
                    array(
                        Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_TEXT,
                        Bubble_Tokenizer::LINE  => 0,
                        Bubble_Tokenizer::VALUE => 'text',
                    ),
                ),
            ),

            array(
                '{{ name }}',
                null,
                array(
                    array(
                        Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_ESCAPED,
                        Bubble_Tokenizer::NAME  => 'name',
                        Bubble_Tokenizer::OTAG  => '{{',
                        Bubble_Tokenizer::CTAG  => '}}',
                        Bubble_Tokenizer::LINE  => 0,
                        Bubble_Tokenizer::INDEX => 10,
                    ),
                ),
            ),

            array(
                '{{ name }}',
                '<<< >>>',
                array(
                    array(
                        Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_TEXT,
                        Bubble_Tokenizer::LINE  => 0,
                        Bubble_Tokenizer::VALUE => '{{ name }}',
                    ),
                ),
            ),

            array(
                '<<< name >>>',
                '<<< >>>',
                array(
                    array(
                        Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_ESCAPED,
                        Bubble_Tokenizer::NAME  => 'name',
                        Bubble_Tokenizer::OTAG  => '<<<',
                        Bubble_Tokenizer::CTAG  => '>>>',
                        Bubble_Tokenizer::LINE  => 0,
                        Bubble_Tokenizer::INDEX => 12,
                    ),
                ),
            ),

            array(
                "{{{ a }}}\n{{# b }}  \n{{= | | =}}| c ||/ b |\n|{ d }|",
                null,
                array(
                    array(
                        Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_UNESCAPED,
                        Bubble_Tokenizer::NAME  => 'a',
                        Bubble_Tokenizer::OTAG  => '{{',
                        Bubble_Tokenizer::CTAG  => '}}',
                        Bubble_Tokenizer::LINE  => 0,
                        Bubble_Tokenizer::INDEX => 8,
                    ),
                    array(
                        Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_TEXT,
                        Bubble_Tokenizer::LINE  => 0,
                        Bubble_Tokenizer::VALUE => "\n",
                    ),
                    array(
                        Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_SECTION,
                        Bubble_Tokenizer::NAME  => 'b',
                        Bubble_Tokenizer::OTAG  => '{{',
                        Bubble_Tokenizer::CTAG  => '}}',
                        Bubble_Tokenizer::LINE  => 1,
                        Bubble_Tokenizer::INDEX => 18,
                    ),
                    array(
                        Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_TEXT,
                        Bubble_Tokenizer::LINE  => 1,
                        Bubble_Tokenizer::VALUE => "  \n",
                    ),
                    array(
                        Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_DELIM_CHANGE,
                        Bubble_Tokenizer::LINE  => 2,
                    ),
                    array(
                        Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_ESCAPED,
                        Bubble_Tokenizer::NAME  => 'c',
                        Bubble_Tokenizer::OTAG  => '|',
                        Bubble_Tokenizer::CTAG  => '|',
                        Bubble_Tokenizer::LINE  => 2,
                        Bubble_Tokenizer::INDEX => 37,
                    ),
                    array(
                        Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_END_SECTION,
                        Bubble_Tokenizer::NAME  => 'b',
                        Bubble_Tokenizer::OTAG  => '|',
                        Bubble_Tokenizer::CTAG  => '|',
                        Bubble_Tokenizer::LINE  => 2,
                        Bubble_Tokenizer::INDEX => 37,
                    ),
                    array(
                        Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_TEXT,
                        Bubble_Tokenizer::LINE  => 2,
                        Bubble_Tokenizer::VALUE => "\n",
                    ),
                    array(
                        Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_UNESCAPED,
                        Bubble_Tokenizer::NAME  => 'd',
                        Bubble_Tokenizer::OTAG  => '|',
                        Bubble_Tokenizer::CTAG  => '|',
                        Bubble_Tokenizer::LINE  => 3,
                        Bubble_Tokenizer::INDEX => 51,
                    ),

                ),
            ),

            array(
                '{{# a }}0{{/ a }}',
                null,
                array(
                    array(
                        Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_SECTION,
                        Bubble_Tokenizer::NAME  => 'a',
                        Bubble_Tokenizer::OTAG  => '{{',
                        Bubble_Tokenizer::CTAG  => '}}',
                        Bubble_Tokenizer::LINE  => 0,
                        Bubble_Tokenizer::INDEX => 8,
                    ),
                    array(
                        Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_TEXT,
                        Bubble_Tokenizer::LINE  => 0,
                        Bubble_Tokenizer::VALUE => '0',
                    ),
                    array(
                        Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_END_SECTION,
                        Bubble_Tokenizer::NAME  => 'a',
                        Bubble_Tokenizer::OTAG  => '{{',
                        Bubble_Tokenizer::CTAG  => '}}',
                        Bubble_Tokenizer::LINE  => 0,
                        Bubble_Tokenizer::INDEX => 9,
                    ),
                ),
            ),

            // custom delimiters don't swallow the next character, even if it is a }, }}}, or the same delimiter
            array(
                '<% a %>} <% b %>%> <% c %>}}}',
                '<% %>',
                array(
                    array(
                        Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_ESCAPED,
                        Bubble_Tokenizer::NAME  => 'a',
                        Bubble_Tokenizer::OTAG  => '<%',
                        Bubble_Tokenizer::CTAG  => '%>',
                        Bubble_Tokenizer::LINE  => 0,
                        Bubble_Tokenizer::INDEX => 7,
                    ),
                    array(
                        Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_TEXT,
                        Bubble_Tokenizer::LINE  => 0,
                        Bubble_Tokenizer::VALUE => '} ',
                    ),
                    array(
                        Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_ESCAPED,
                        Bubble_Tokenizer::NAME  => 'b',
                        Bubble_Tokenizer::OTAG  => '<%',
                        Bubble_Tokenizer::CTAG  => '%>',
                        Bubble_Tokenizer::LINE  => 0,
                        Bubble_Tokenizer::INDEX => 16,
                    ),
                    array(
                        Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_TEXT,
                        Bubble_Tokenizer::LINE  => 0,
                        Bubble_Tokenizer::VALUE => '%> ',
                    ),
                    array(
                        Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_ESCAPED,
                        Bubble_Tokenizer::NAME  => 'c',
                        Bubble_Tokenizer::OTAG  => '<%',
                        Bubble_Tokenizer::CTAG  => '%>',
                        Bubble_Tokenizer::LINE  => 0,
                        Bubble_Tokenizer::INDEX => 26,
                    ),
                    array(
                        Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_TEXT,
                        Bubble_Tokenizer::LINE  => 0,
                        Bubble_Tokenizer::VALUE => '}}}',
                    ),
                ),
            ),

            // unescaped custom delimiters are properly parsed
            array(
                '<%{ a }%>',
                '<% %>',
                array(
                    array(
                        Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_UNESCAPED,
                        Bubble_Tokenizer::NAME  => 'a',
                        Bubble_Tokenizer::OTAG  => '<%',
                        Bubble_Tokenizer::CTAG  => '%>',
                        Bubble_Tokenizer::LINE  => 0,
                        Bubble_Tokenizer::INDEX => 9,
                    ),
                ),
            ),

            // Ensure that $arg token is not picked up during tokenization
            array(
                '{{$arg}}default{{/arg}}',
                null,
                array(
                    array(
                        Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_BLOCK_VAR,
                        Bubble_Tokenizer::NAME  => 'arg',
                        Bubble_Tokenizer::OTAG  => '{{',
                        Bubble_Tokenizer::CTAG  => '}}',
                        Bubble_Tokenizer::LINE  => 0,
                        Bubble_Tokenizer::INDEX => 8,
                    ),
                    array(
                        Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_TEXT,
                        Bubble_Tokenizer::LINE  => 0,
                        Bubble_Tokenizer::VALUE => 'default',
                    ),
                    array(
                        Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_END_SECTION,
                        Bubble_Tokenizer::NAME  => 'arg',
                        Bubble_Tokenizer::OTAG  => '{{',
                        Bubble_Tokenizer::CTAG  => '}}',
                        Bubble_Tokenizer::LINE  => 0,
                        Bubble_Tokenizer::INDEX => 15,
                    ),
                ),
            ),
        );
    }
}
