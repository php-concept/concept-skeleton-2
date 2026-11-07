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
use Concept\Stack\Bricks\ViewTwig\ViewTwigBrick;
use Concept\Stack\ConceptStack;
use League\Container\Container;
use Symfony\Component\Console\Command\Command;
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

        $stack->add(new SessionBrick())
            ->builder()
            ->setOptions($app->sessionOptions())
            ->setHandlerFactory($app->getSessionHandlerFactory($pathManager))
            ->withCsrf();

        $stack->add(new HttpBrick())
            ->builder()
            ->setRoutes($pathManager->rootList($config->getStringList(ConfigKey::ROUTES_LIST)))
            ->withFormRequests()
            ->withTypedRouteParameters();

        $viewCacheDir = $config->getString(ConfigKey::VIEW_CACHE_DIR, 'views');
        $twig = new ViewTwigBrick();
        $twig->builder()
            ->setViewsPath($pathManager->get(PathName::VIEWS))
            ->setCacheDir($pathManager->get(PathName::CACHE, $viewCacheDir))
            ->setDebug($debug);

        $viewExtensions = $config->getClassStringList(ConfigKey::VIEW_EXTENSIONS);
        $stack->add(new ViewBrick())
            ->builder()
            ->setPaths($pathManager->rootMap($config->getStringMap(ConfigKey::VIEW_PATHS)))
            ->setExtensions($viewExtensions)
            ->setRouteNamespaces($config->getStringMap(ConfigKey::VIEW_ROUTE_NAMESPACE))
            ->withEngine($twig);

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
