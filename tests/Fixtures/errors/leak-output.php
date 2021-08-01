<?php

namespace Bubble\Tests\Fixtures\errors;

require __DIR__.'/../../../vendor/autoload.php';

use Bubble\Environment;
use Bubble\Extension\AbstractExtension;
use Bubble\Loader\ArrayLoader;
use Bubble\BubbleFilter;

class BrokenExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new BubbleFilter('broken', [$this, 'broken']),
        ];
    }

    public function broken()
    {
        exit('OOPS');
    }
}

$loader = new ArrayLoader([
    'index.html.bubble' => 'Hello {{ "world"|broken }}',
]);
$bubble = new Environment($loader, ['debug' => isset($argv[1])]);
$bubble->addExtension(new BrokenExtension());

echo $bubble->render('index.html.bubble');
