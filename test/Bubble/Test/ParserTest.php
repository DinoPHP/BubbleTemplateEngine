<?php

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

/**
 * @group unit
 */
class Bubble_Test_ParserTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getTokenSets
     */
    public function testParse($tokens, $expected)
    {
        $parser = new Bubble_Parser();
        $this->assertEquals($expected, $parser->parse($tokens));
    }

    public function getTokenSets()
    {
        return array(
            array(
                array(),
                array(),
            ),

            array(
                array(array(
                    Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_TEXT,
                    Bubble_Tokenizer::LINE  => 0,
                    Bubble_Tokenizer::VALUE => 'text',
                )),
                array(array(
                    Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_TEXT,
                    Bubble_Tokenizer::LINE  => 0,
                    Bubble_Tokenizer::VALUE => 'text',
                )),
            ),

            array(
                array(array(
                    Bubble_Tokenizer::TYPE => Bubble_Tokenizer::T_ESCAPED,
                    Bubble_Tokenizer::LINE => 0,
                    Bubble_Tokenizer::NAME => 'name',
                )),
                array(array(
                    Bubble_Tokenizer::TYPE => Bubble_Tokenizer::T_ESCAPED,
                    Bubble_Tokenizer::LINE => 0,
                    Bubble_Tokenizer::NAME => 'name',
                )),
            ),

            array(
                array(
                    array(
                        Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_TEXT,
                        Bubble_Tokenizer::LINE  => 0,
                        Bubble_Tokenizer::VALUE => 'foo',
                    ),
                    array(
                        Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_INVERTED,
                        Bubble_Tokenizer::LINE  => 0,
                        Bubble_Tokenizer::INDEX => 123,
                        Bubble_Tokenizer::NAME  => 'parent',
                    ),
                    array(
                        Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_ESCAPED,
                        Bubble_Tokenizer::LINE  => 0,
                        Bubble_Tokenizer::NAME  => 'name',
                    ),
                    array(
                        Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_END_SECTION,
                        Bubble_Tokenizer::LINE  => 0,
                        Bubble_Tokenizer::INDEX => 456,
                        Bubble_Tokenizer::NAME  => 'parent',
                    ),
                    array(
                        Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_TEXT,
                        Bubble_Tokenizer::LINE  => 0,
                        Bubble_Tokenizer::VALUE => 'bar',
                    ),
                ),

                array(
                    array(
                        Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_TEXT,
                        Bubble_Tokenizer::LINE  => 0,
                        Bubble_Tokenizer::VALUE => 'foo',
                    ),
                    array(
                        Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_INVERTED,
                        Bubble_Tokenizer::NAME  => 'parent',
                        Bubble_Tokenizer::LINE  => 0,
                        Bubble_Tokenizer::INDEX => 123,
                        Bubble_Tokenizer::END   => 456,
                        Bubble_Tokenizer::NODES => array(
                            array(
                                Bubble_Tokenizer::TYPE => Bubble_Tokenizer::T_ESCAPED,
                                Bubble_Tokenizer::LINE => 0,
                                Bubble_Tokenizer::NAME => 'name',
                            ),
                        ),
                    ),
                    array(
                        Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_TEXT,
                        Bubble_Tokenizer::LINE  => 0,
                        Bubble_Tokenizer::VALUE => 'bar',
                    ),
                ),
            ),

            // This *would* be an invalid inheritance parse tree, but that pragma
            // isn't enabled so it'll thunk it back into an "escaped" token:
            array(
                array(
                    array(
                        Bubble_Tokenizer::TYPE => Bubble_Tokenizer::T_BLOCK_VAR,
                        Bubble_Tokenizer::NAME => 'foo',
                        Bubble_Tokenizer::OTAG => '{{',
                        Bubble_Tokenizer::CTAG => '}}',
                        Bubble_Tokenizer::LINE => 0,
                    ),
                    array(
                        Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_TEXT,
                        Bubble_Tokenizer::LINE  => 0,
                        Bubble_Tokenizer::VALUE => 'bar',
                    ),
                ),
                array(
                    array(
                        Bubble_Tokenizer::TYPE => Bubble_Tokenizer::T_ESCAPED,
                        Bubble_Tokenizer::NAME => '$foo',
                        Bubble_Tokenizer::OTAG => '{{',
                        Bubble_Tokenizer::CTAG => '}}',
                        Bubble_Tokenizer::LINE => 0,
                    ),
                    array(
                        Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_TEXT,
                        Bubble_Tokenizer::LINE  => 0,
                        Bubble_Tokenizer::VALUE => 'bar',
                    ),
                ),
            ),

            array(
                array(
                    array(
                        Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_TEXT,
                        Bubble_Tokenizer::LINE  => 0,
                        Bubble_Tokenizer::VALUE => '  ',
                    ),
                    array(
                        Bubble_Tokenizer::TYPE => Bubble_Tokenizer::T_DELIM_CHANGE,
                        Bubble_Tokenizer::LINE => 0,
                    ),
                    array(
                        Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_TEXT,
                        Bubble_Tokenizer::LINE  => 0,
                        Bubble_Tokenizer::VALUE => "  \n",
                    ),
                    array(
                        Bubble_Tokenizer::TYPE => Bubble_Tokenizer::T_ESCAPED,
                        Bubble_Tokenizer::NAME => 'foo',
                        Bubble_Tokenizer::OTAG => '[[',
                        Bubble_Tokenizer::CTAG => ']]',
                        Bubble_Tokenizer::LINE => 1,
                    ),
                ),
                array(
                    array(
                        Bubble_Tokenizer::TYPE => Bubble_Tokenizer::T_ESCAPED,
                        Bubble_Tokenizer::NAME => 'foo',
                        Bubble_Tokenizer::OTAG => '[[',
                        Bubble_Tokenizer::CTAG => ']]',
                        Bubble_Tokenizer::LINE => 1,
                    ),
                ),
            ),

        );
    }

    /**
     * @dataProvider getInheritanceTokenSets
     */
    public function testParseWithInheritance($tokens, $expected)
    {
        $parser = new Bubble_Parser();
        $parser->setPragmas(array(Bubble_Engine::PRAGMA_BLOCKS));
        $this->assertEquals($expected, $parser->parse($tokens));
    }

    public function getInheritanceTokenSets()
    {
        return array(
            array(
                array(
                    array(
                        Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_PARENT,
                        Bubble_Tokenizer::NAME  => 'foo',
                        Bubble_Tokenizer::OTAG  => '{{',
                        Bubble_Tokenizer::CTAG  => '}}',
                        Bubble_Tokenizer::LINE  => 0,
                        Bubble_Tokenizer::INDEX => 8,
                    ),
                    array(
                        Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_BLOCK_VAR,
                        Bubble_Tokenizer::NAME  => 'bar',
                        Bubble_Tokenizer::OTAG  => '{{',
                        Bubble_Tokenizer::CTAG  => '}}',
                        Bubble_Tokenizer::LINE  => 0,
                        Bubble_Tokenizer::INDEX => 16,
                    ),
                    array(
                        Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_TEXT,
                        Bubble_Tokenizer::LINE  => 0,
                        Bubble_Tokenizer::VALUE => 'baz',
                    ),
                    array(
                        Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_END_SECTION,
                        Bubble_Tokenizer::NAME  => 'bar',
                        Bubble_Tokenizer::OTAG  => '{{',
                        Bubble_Tokenizer::CTAG  => '}}',
                        Bubble_Tokenizer::LINE  => 0,
                        Bubble_Tokenizer::INDEX => 19,
                    ),
                    array(
                        Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_END_SECTION,
                        Bubble_Tokenizer::NAME  => 'foo',
                        Bubble_Tokenizer::OTAG  => '{{',
                        Bubble_Tokenizer::CTAG  => '}}',
                        Bubble_Tokenizer::LINE  => 0,
                        Bubble_Tokenizer::INDEX => 27,
                    ),
                ),
                array(
                    array(
                        Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_PARENT,
                        Bubble_Tokenizer::NAME  => 'foo',
                        Bubble_Tokenizer::OTAG  => '{{',
                        Bubble_Tokenizer::CTAG  => '}}',
                        Bubble_Tokenizer::LINE  => 0,
                        Bubble_Tokenizer::INDEX => 8,
                        Bubble_Tokenizer::END   => 27,
                        Bubble_Tokenizer::NODES => array(
                            array(
                                Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_BLOCK_ARG,
                                Bubble_Tokenizer::NAME  => 'bar',
                                Bubble_Tokenizer::OTAG  => '{{',
                                Bubble_Tokenizer::CTAG  => '}}',
                                Bubble_Tokenizer::LINE  => 0,
                                Bubble_Tokenizer::INDEX => 16,
                                Bubble_Tokenizer::END   => 19,
                                Bubble_Tokenizer::NODES => array(
                                    array(
                                        Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_TEXT,
                                        Bubble_Tokenizer::LINE  => 0,
                                        Bubble_Tokenizer::VALUE => 'baz',
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),

            array(
                array(
                    array(
                        Bubble_Tokenizer::TYPE => Bubble_Tokenizer::T_BLOCK_VAR,
                        Bubble_Tokenizer::NAME => 'foo',
                        Bubble_Tokenizer::OTAG => '{{',
                        Bubble_Tokenizer::CTAG => '}}',
                        Bubble_Tokenizer::LINE => 0,
                    ),
                    array(
                        Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_TEXT,
                        Bubble_Tokenizer::LINE  => 0,
                        Bubble_Tokenizer::VALUE => 'bar',
                    ),
                    array(
                        Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_END_SECTION,
                        Bubble_Tokenizer::NAME  => 'foo',
                        Bubble_Tokenizer::OTAG  => '{{',
                        Bubble_Tokenizer::CTAG  => '}}',
                        Bubble_Tokenizer::LINE  => 0,
                        Bubble_Tokenizer::INDEX => 11,
                    ),
                ),
                array(
                    array(
                        Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_BLOCK_VAR,
                        Bubble_Tokenizer::NAME  => 'foo',
                        Bubble_Tokenizer::OTAG  => '{{',
                        Bubble_Tokenizer::CTAG  => '}}',
                        Bubble_Tokenizer::LINE  => 0,
                        Bubble_Tokenizer::END   => 11,
                        Bubble_Tokenizer::NODES => array(
                            array(
                                Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_TEXT,
                                Bubble_Tokenizer::LINE  => 0,
                                Bubble_Tokenizer::VALUE => 'bar',
                            ),
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * @dataProvider getBadParseTrees
     * @expectedException Bubble_Exception_SyntaxException
     */
    public function testParserThrowsExceptions($tokens)
    {
        $parser = new Bubble_Parser();
        $parser->parse($tokens);
    }

    public function getBadParseTrees()
    {
        return array(
            // no close
            array(
                array(
                    array(
                        Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_SECTION,
                        Bubble_Tokenizer::NAME  => 'parent',
                        Bubble_Tokenizer::LINE  => 0,
                        Bubble_Tokenizer::INDEX => 123,
                    ),
                ),
            ),

            // no close inverted
            array(
                array(
                    array(
                        Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_INVERTED,
                        Bubble_Tokenizer::NAME  => 'parent',
                        Bubble_Tokenizer::LINE  => 0,
                        Bubble_Tokenizer::INDEX => 123,
                    ),
                ),
            ),

            // no opening inverted
            array(
                array(
                    array(
                        Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_END_SECTION,
                        Bubble_Tokenizer::NAME  => 'parent',
                        Bubble_Tokenizer::LINE  => 0,
                        Bubble_Tokenizer::INDEX => 123,
                    ),
                ),
            ),

            // weird nesting
            array(
                array(
                    array(
                        Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_SECTION,
                        Bubble_Tokenizer::NAME  => 'parent',
                        Bubble_Tokenizer::LINE  => 0,
                        Bubble_Tokenizer::INDEX => 123,
                    ),
                    array(
                        Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_SECTION,
                        Bubble_Tokenizer::NAME  => 'child',
                        Bubble_Tokenizer::LINE  => 0,
                        Bubble_Tokenizer::INDEX => 123,
                    ),
                    array(
                        Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_END_SECTION,
                        Bubble_Tokenizer::NAME  => 'parent',
                        Bubble_Tokenizer::LINE  => 0,
                        Bubble_Tokenizer::INDEX => 123,
                    ),
                    array(
                        Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_END_SECTION,
                        Bubble_Tokenizer::NAME  => 'child',
                        Bubble_Tokenizer::LINE  => 0,
                        Bubble_Tokenizer::INDEX => 123,
                    ),
                ),
            ),

            // This *would* be a valid inheritance parse tree, but that pragma
            // isn't enabled here so it's going to fail :)
            array(
                array(
                    array(
                        Bubble_Tokenizer::TYPE => Bubble_Tokenizer::T_BLOCK_VAR,
                        Bubble_Tokenizer::NAME => 'foo',
                        Bubble_Tokenizer::OTAG => '{{',
                        Bubble_Tokenizer::CTAG => '}}',
                        Bubble_Tokenizer::LINE => 0,
                    ),
                    array(
                        Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_TEXT,
                        Bubble_Tokenizer::LINE  => 0,
                        Bubble_Tokenizer::VALUE => 'bar',
                    ),
                    array(
                        Bubble_Tokenizer::TYPE  => Bubble_Tokenizer::T_END_SECTION,
                        Bubble_Tokenizer::NAME  => 'foo',
                        Bubble_Tokenizer::OTAG  => '{{',
                        Bubble_Tokenizer::CTAG  => '}}',
                        Bubble_Tokenizer::LINE  => 0,
                        Bubble_Tokenizer::INDEX => 11,
                    ),
                ),
            ),
        );
    }
}
