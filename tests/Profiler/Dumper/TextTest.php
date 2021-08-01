<?php

namespace Bubble\Tests\Profiler\Dumper;

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

use Bubble\Profiler\Dumper\TextDumper;

class TextTest extends AbstractTest
{
    public function testDump()
    {
        $dumper = new TextDumper();
        $this->assertStringMatchesFormat(<<<EOF
main %d.%dms/%d%
└ index.bubble %d.%dms/%d%
  └ embedded.bubble::block(body)
  └ embedded.bubble
  │ └ included.bubble
  └ index.bubble::macro(foo)
  └ embedded.bubble
    └ included.bubble

EOF
        , $dumper->dump($this->getProfile()));
    }
}
