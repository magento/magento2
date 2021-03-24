<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Framework\Autoload\AutoloaderRegistry;

/**
 * phpcs:disable PSR1.Files.SideEffects
 * phpcs:disable Squiz.Functions.GlobalFunction
 * phpcs:disable Magento2.Security.IncludeFile
 */
require_once __DIR__ . '/../../../../app/bootstrap.php';
require_once __DIR__ . '/autoload.php';

// phpcs:ignore Magento2.Functions.DiscouragedFunction
$testsBaseDir = dirname(__DIR__);
$fixtureBaseDir = $testsBaseDir. '/testsuite';

if (!defined('TESTS_TEMP_DIR')) {
    define('TESTS_TEMP_DIR', $testsBaseDir . '/tmp');
}

if (!defined('INTEGRATION_TESTS_DIR')) {
    define('INTEGRATION_TESTS_DIR', $testsBaseDir);
}

try {
    setCustomErrorHandler();

    /* Bootstrap the application */
    $settings = new \Magento\TestFramework\Bootstrap\Settings($testsBaseDir, get_defined_constants());

    $testFrameworkDir = __DIR__;
    require_once 'deployTestModules.php';

    if ($settings->get('TESTS_EXTRA_VERBOSE_LOG')) {
        $filesystem = new \Magento\Framework\Filesystem\Driver\File();
        $exceptionHandler = new \Magento\Framework\Logger\Handler\Exception($filesystem);
        $loggerHandlers = [
            'system'    => new \Magento\Framework\Logger\Handler\System($filesystem, $exceptionHandler),
            'debug'     => new \Magento\Framework\Logger\Handler\Debug($filesystem)
        ];
        $shell = new \Magento\Framework\Shell(
            new \Magento\Framework\Shell\CommandRenderer(),
            new \Monolog\Logger('main', $loggerHandlers)
        );
    } else {
        $shell = new \Magento\Framework\Shell(new \Magento\Framework\Shell\CommandRenderer());
    }

    $installConfigFile = $settings->getAsConfigFile('TESTS_INSTALL_CONFIG_FILE');
    // phpcs:ignore Magento2.Functions.DiscouragedFunction
    if (!file_exists($installConfigFile)) {
        $installConfigFile .= '.dist';
    }

    $postInstallSetupConfigFile = $settings->getAsConfigFile('TESTS_POST_INSTALL_SETUP_COMMAND_CONFIG_FILE');

    $globalConfigFile = $settings->getAsConfigFile('TESTS_GLOBAL_CONFIG_FILE');
    // phpcs:ignore Magento2.Functions.DiscouragedFunction
    if (!file_exists($globalConfigFile)) {
        $globalConfigFile .= '.dist';
    }
    $sandboxUniqueId = hash('sha256', sha1_file($installConfigFile));
    $installDir = TESTS_TEMP_DIR . "/sandbox-{$settings->get('TESTS_PARALLEL_THREAD', 0)}-{$sandboxUniqueId}";
    $application = new \Magento\TestFramework\Application(
        $shell,
        $installDir,
        $installConfigFile,
        $globalConfigFile,
        $settings->get('TESTS_GLOBAL_CONFIG_DIR'),
        $settings->get('TESTS_MAGENTO_MODE'),
        AutoloaderRegistry::getAutoloader(),
        true,
        $postInstallSetupConfigFile
    );

    $bootstrap = new \Magento\TestFramework\Bootstrap(
        $settings,
        new \Magento\TestFramework\Bootstrap\Environment(),
        new \Magento\TestFramework\Bootstrap\DocBlock("{$testsBaseDir}/testsuite"),
        new \Magento\TestFramework\Bootstrap\Profiler(new \Magento\Framework\Profiler\Driver\Standard()),
        $shell,
        $application,
        new \Magento\TestFramework\Bootstrap\MemoryFactory($shell)
    );
    $bootstrap->runBootstrap();
    if ($settings->getAsBoolean('TESTS_CLEANUP')) {
        $application->cleanup();
    }
    if (!$application->isInstalled()) {
        $application->install($settings->getAsBoolean('TESTS_CLEANUP'));
    }
    $application->initialize([]);

    \Magento\TestFramework\Helper\Bootstrap::setInstance(new \Magento\TestFramework\Helper\Bootstrap($bootstrap));

    $dirSearch = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
        ->create(\Magento\Framework\Component\DirSearch::class);
    $themePackageList = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
        ->create(\Magento\Framework\View\Design\Theme\ThemePackageList::class);
    \Magento\Framework\App\Utility\Files::setInstance(
        new Magento\Framework\App\Utility\Files(
            new \Magento\Framework\Component\ComponentRegistrar(),
            $dirSearch,
            $themePackageList
        )
    );
    $overrideConfig = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
        Magento\TestFramework\Workaround\Override\Config::class
    );
    $overrideConfig->init();
    Magento\TestFramework\Workaround\Override\Config::setInstance($overrideConfig);
    Magento\TestFramework\Workaround\Override\Fixture\Resolver::setInstance(
        new  \Magento\TestFramework\Workaround\Override\Fixture\Resolver($overrideConfig)
    );
    /* Unset declared global variables to release the PHPUnit from maintaining their values between tests */
    unset($testsBaseDir, $settings, $shell, $application, $bootstrap, $overrideConfig);
} catch (\Exception $e) {
    // phpcs:ignore Magento2.Security.LanguageConstruct.DirectOutput
    echo $e . PHP_EOL;
    // phpcs:ignore Magento2.Security.LanguageConstruct.ExitUsage
    exit(1);
}

/**
 * Set custom error handler
 */
function setCustomErrorHandler()
{
    set_error_handler(
        function ($errNo, $errStr, $errFile, $errLine) {
            if (error_reporting()) {
                $errorNames = [
                    E_ERROR => 'Error',
                    E_WARNING => 'Warning',
                    E_PARSE => 'Parse',
                    E_NOTICE => 'Notice',
                    E_CORE_ERROR => 'Core Error',
                    E_CORE_WARNING => 'Core Warning',
                    E_COMPILE_ERROR => 'Compile Error',
                    E_COMPILE_WARNING => 'Compile Warning',
                    E_USER_ERROR => 'User Error',
                    E_USER_WARNING => 'User Warning',
                    E_USER_NOTICE => 'User Notice',
                    E_STRICT => 'Strict',
                    E_RECOVERABLE_ERROR => 'Recoverable Error',
                    E_DEPRECATED => 'Deprecated',
                    E_USER_DEPRECATED => 'User Deprecated',
                ];

                $errName = isset($errorNames[$errNo]) ? $errorNames[$errNo] : "";

                throw new \PHPUnit\Framework\Exception(
                    sprintf("%s: %s in %s:%s.", $errName, $errStr, $errFile, $errLine),
                    $errNo
                );
            }
        }
    );
}
