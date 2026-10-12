<?php declare(strict_types=1);

namespace Concept\App\Bootstrap;

use Concept\App\Foundation\AppConfig;
use Concept\App\Foundation\ConfigKey;
use Concept\App\Foundation\PathName;
use Concept\Core\Container\ContainerDependency;
use Concept\Extensions\Config\Contracts\ConfigInterface;
use Concept\Extensions\PathManager\PathManager;
use Concept\Extensions\ValidationRakit\Contracts\RuleInterface;
use Concept\Stack\Bricks\Casting\CastingBrick;
use Concept\Stack\Bricks\Console\ConsoleBrick;
use Concept\Stack\Bricks\ErrorHandling\ErrorHandlingBrick;
use Concept\Stack\Bricks\Http\HttpBrick;
use Concept\Stack\Bricks\Logging\LoggingBrick;
use Concept\Stack\Bricks\Session\SessionBrick;
use Concept\Stack\Bricks\Validation\ValidationBrick;
use Concept\Stack\Bricks\View\ViewBrick;
use Concept\Stack\ConceptStack;
use League\Container\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler;
use Whoops\Handler\PrettyPageHandler;

final class ApplicationStackBootstrap
{
    public function boot(Container $container): void
    {
        $pathManager = ContainerDependency::get($container, PathManager::class);
        $config = ContainerDependency::get($container, ConfigInterface::class);
        $app = new AppConfig($config);

        $debug = $config->getBool(ConfigKey::APP_DEBUG);
        $stack = ConceptStack::create();

        $appLogFileName = $config->getString(ConfigKey::LOG_FILE, 'app.log');
        $stack->add(new LoggingBrick())
            ->builder()
            ->setLevel($config->getString(ConfigKey::LOG_LEVEL, 'ERROR'))
            ->setChannel($config->getString(ConfigKey::LOG_NAME, 'app'))
            ->toRotatingFile(
                $pathManager->get(PathName::LOGS, $appLogFileName),
                $config->getInt(ConfigKey::LOG_MAX_FILES, 7),
            );

        $casterCacheDir = $config->getString(ConfigKey::CASTER_CACHE_DIR, 'valinor');
        $stack->add(new CastingBrick())
            ->builder()
            ->setTransformers($config->getClassStringList(ConfigKey::CASTER_TRANSFORMERS))
            ->setCacheDir($pathManager->get(PathName::CACHE, $casterCacheDir))
            ->setDebug($debug);

        $stack->add(new ValidationBrick())
            ->builder()
            ->setRules($config->getClassMap(ConfigKey::VALIDATOR_RULES, RuleInterface::class))
            ->setGlobalExcept($config->getStringList(ConfigKey::FORM_REQUEST_GLOBAL_EXCEPT));

        $sessionFilePath = $config->get(ConfigKey::SESSION_FILE_PATH);
        $stack->add(new SessionBrick())
            ->builder()
            ->setOptions($app->sessionOptions())
            ->setHandlerFactory(function () use ($pathManager, $sessionFilePath): NativeFileSessionHandler {
                return is_string($sessionFilePath) && $sessionFilePath !== ''
                    ? new NativeFileSessionHandler($pathManager->root($sessionFilePath))
                    : new NativeFileSessionHandler();
            })
            ->withCsrf();

        $stack->add(new HttpBrick())
            ->builder()
            ->setRoutes($pathManager->rootList($config->getStringList(ConfigKey::ROUTES_LIST)))
            ->withFormRequests()
            ->withTypedRouteParameters();

        $viewExtensions = $config->getClassStringList(ConfigKey::VIEW_EXTENSIONS);
        $viewCacheDir = $config->getString(ConfigKey::VIEW_CACHE_DIR, 'views');
        $stack->add(new ViewBrick())
            ->builder()
            ->setPaths($pathManager->rootMap($config->getStringMap(ConfigKey::VIEW_PATHS)))
            ->setExtensions($viewExtensions)
            ->setRouteNamespaces($config->getStringMap(ConfigKey::VIEW_ROUTE_NAMESPACE))
            ->withTwig()
            ->setViewsPath($pathManager->get(PathName::VIEWS))
            ->setCacheDir($pathManager->get(PathName::CACHE, $viewCacheDir))
            ->setDebug($debug);

        $stack->add(new ConsoleBrick())
            ->builder()
            ->setName($config->getString(ConfigKey::APP_NAME, 'Concept Skeleton'))
            ->setVersion($config->getString(ConfigKey::APP_VERSION, '1.0.0'))
            ->setCommands($config->getClassList(ConfigKey::COMMANDS, Command::class));

        $stack->add(new ErrorHandlingBrick())
            ->builder()
            ->setDebug($debug, new PrettyPageHandler())
            ->setLogReporting()
            ->withViewRenderer($pathManager->get(PathName::ERRORS_FALLBACK_VIEWS));

        foreach ($stack->providers() as $provider) {
            $container->addServiceProvider($provider);
        }
    }
}
