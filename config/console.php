<?php declare(strict_types=1);

use Concept\Extensions\Http\Console\Commands\RouteListCommand;
use Concept\Extensions\ViewTwig\Console\Commands\ViewClearCommand;

return [
    'console' => [
        'commands' => [
            RouteListCommand::class,
            ViewClearCommand::class,
        ],
    ],
];
