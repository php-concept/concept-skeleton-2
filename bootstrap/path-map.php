<?php declare(strict_types=1);

use Concept\App\Foundation\PathName;

/**
 * @return array<string, string>
 */
return [
    PathName::BOOTSTRAP => 'bootstrap',
    PathName::SRC => 'src',
    PathName::CONFIG => 'config',
    PathName::PUBLIC => 'public',
    PathName::STORAGE => 'storage',
    PathName::LOGS => 'storage/logs',
    PathName::CACHE => 'storage/cache',
    PathName::RESOURCES => 'resources',
    PathName::VIEWS => 'resources/views',
    PathName::ERRORS_FALLBACK_VIEWS => 'resources/views/errors/fallback',
];
