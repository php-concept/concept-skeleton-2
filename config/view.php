<?php declare(strict_types=1);

use Concept\App\View\Twig\TwigAppExtension;

return [
    'view' => [
        'paths' => [
            'frontend' => '/resources/views/frontend',
        ],
        'extensions' => [
            TwigAppExtension::class,
        ],
        'route_namespace' => [
        ],
        'cache_dir' => 'views',
    ],
];
