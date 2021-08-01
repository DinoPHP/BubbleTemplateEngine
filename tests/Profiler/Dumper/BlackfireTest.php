<?php

namespace Bubble\Tests\Profiler\Dumper;

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

use Bubble\Profiler\Dumper\BlackfireDumper;

class BlackfireTest extends AbstractTest
{
    public function testDump()
    {
        $dumper = new BlackfireDumper();

        $this->assertStringMatchesFormat(<<<EOF
file-format: BlackfireProbe
cost-dimensions: wt mu pmu
request-start: %d.%d

main()//1 %d %d %d
main()==>index.bubble//1 %d %d %d
index.bubble==>embedded.bubble::block(body)//1 %d %d 0
index.bubble==>embedded.bubble//2 %d %d %d
embedded.bubble==>included.bubble//2 %d %d %d
index.bubble==>index.bubble::macro(foo)//1 %d %d %d
EOF
        , $dumper->dump($this->getProfile()));
    }
}
