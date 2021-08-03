<?php

namespace Bubble\Tests;

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

use PHPUnit\Framework\TestCase;
use Bubble\FileExtensionEscapingStrategy;

class FileExtensionEscapingStrategyTest extends TestCase
{
    /**
     * @dataProvider getGuessData
     */
    public function testGuess($strategy, $filename)
    {
        $this->assertSame($strategy, FileExtensionEscapingStrategy::guess($filename));
    }

    public function getGuessData()
    {
        return [
            // default
            ['html', 'foo.html'],
            ['html', 'foo.html.bubble'],
            ['html', 'foo'],
            ['html', 'foo.bar.bubble'],
            ['html', 'foo.txt/foo'],
            ['html', 'foo.txt/foo.js/'],

            // css
            ['css', 'foo.css'],
            ['css', 'foo.css.bubble'],
            ['css', 'foo.bubble.css'],
            ['css', 'foo.js.css'],
            ['css', 'foo.js.css.bubble'],

            // js
            ['js', 'foo.js'],
            ['js', 'foo.js.bubble'],
            ['js', 'foo.txt/foo.js'],
            ['js', 'foo.txt.bubble/foo.js'],

            // txt
            [false, 'foo.txt'],
            [false, 'foo.txt.bubble'],
        ];
    }
}
