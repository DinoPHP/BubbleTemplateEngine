<?php

namespace Bubble\Tests\Profiler\Dumper;

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

use Bubble\Profiler\Dumper\HtmlDumper;

class HtmlTest extends AbstractTest
{
    public function testDump()
    {
        $dumper = new HtmlDumper();
        $this->assertStringMatchesFormat(<<<EOF
<pre>main <span style="color: #d44">%d.%dms/%d%</span>
└ <span style="background-color: #ffd">index.bubble</span> <span style="color: #d44">%d.%dms/%d%</span>
  └ embedded.bubble::block(<span style="background-color: #dfd">body</span>)
  └ <span style="background-color: #ffd">embedded.bubble</span>
  │ └ <span style="background-color: #ffd">included.bubble</span>
  └ index.bubble::macro(<span style="background-color: #ddf">foo</span>)
  └ <span style="background-color: #ffd">embedded.bubble</span>
    └ <span style="background-color: #ffd">included.bubble</span>
</pre>
EOF
        , $dumper->dump($this->getProfile()));
    }
}
