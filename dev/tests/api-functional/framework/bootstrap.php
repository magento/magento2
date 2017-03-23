<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Autoload\AutoloaderRegistry;

require_once __DIR__ . '/../../../../app/bootstrap.php';
require_once __DIR__ . '/autoload.php';

$testsBaseDir = dirname(__DIR__);
$integrationTestsDir = realpath("{$testsBaseDir}/../integration");
$fixtureBaseDir = $integrationTestsDir . '/testsuite';

setCustomErrorHandler();

$logWriter = new \Zend_Log_Writer_Stream('php://output');
$logWriter->setFormatter(new \Zend_Log_Formatter_Simple('%message%' . PHP_EOL));
$logger = new \Zend_Log($logWriter);

$testFrameworkDir = __DIR__;
require_once  __DIR__ . '/../../integration/framework/deployTestModules.php';

/* Bootstrap the application */
$settings = new \Magento\TestFramework\Bootstrap\Settings($testsBaseDir, get_defined_constants());
$shell = new \Magento\Framework\Shell(new \Magento\Framework\Shell\CommandRenderer(), $logger);

$installConfigFile = $settings->getAsConfigFile('TESTS_INSTALL_CONFIG_FILE');
if (!file_exists($installConfigFile)) {
    $installConfigFile = $installConfigFile . '.dist';
}
$globalConfigFile = $settings->getAsConfigFile('TESTS_GLOBAL_CONFIG_FILE');
if (!file_exists($installConfigFile)) {
    $installConfigFile = $installConfigFile . '.dist';
}
$dirList = new \Magento\Framework\App\Filesystem\DirectoryList(BP);
$application =  new \Magento\TestFramework\WebApiApplication(
    $shell,
    $dirList->getPath(DirectoryList::VAR_DIR),
    $installConfigFile,
    $globalConfigFile,
    BP . '/app/etc/',
    $settings->get('TESTS_MAGENTO_MODE'),
    AutoloaderRegistry::getAutoloader()
);

if (defined('TESTS_MAGENTO_INSTALLATION') && TESTS_MAGENTO_INSTALLATION === 'enabled') {
    if (defined('TESTS_CLEANUP') && TESTS_CLEANUP === 'enabled') {
        $application->cleanup();
    }
    $application->install();
}

$bootstrap = new \Magento\TestFramework\Bootstrap(
    $settings,
    new \Magento\TestFramework\Bootstrap\Environment(),
    new \Magento\TestFramework\Bootstrap\WebapiDocBlock("{$integrationTestsDir}/testsuite"),
    new \Magento\TestFramework\Bootstrap\Profiler(new \Magento\Framework\Profiler\Driver\Standard()),
    $shell,
    $application,
    new \Magento\TestFramework\Bootstrap\MemoryFactory($shell)
);
$bootstrap->runBootstrap();
$application->initialize();

\Magento\TestFramework\Helper\Bootstrap::setInstance(new \Magento\TestFramework\Helper\Bootstrap($bootstrap));
$dirSearch = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\Framework\Component\DirSearch::class);
$themePackageList = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\Framework\View\Design\Theme\ThemePackageList::class);
\Magento\Framework\App\Utility\Files::setInstance(
    new \Magento\Framework\App\Utility\Files(
        new \Magento\Framework\Component\ComponentRegistrar(),
        $dirSearch,
        $themePackageList
    )
);
unset($bootstrap, $application, $settings, $shell);

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

                throw new \PHPUnit_Framework_Exception(
                    sprintf("%s: %s in %s:%s.", $errName, $errStr, $errFile, $errLine),
                    $errNo
                );
            }
        }
    );
}
