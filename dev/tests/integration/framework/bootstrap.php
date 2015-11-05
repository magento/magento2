<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Framework\Autoload\AutoloaderRegistry;

require_once __DIR__ . '/../../../../app/bootstrap.php';
require_once __DIR__ . '/autoload.php';

$updateAppBootstrap = __DIR__ . '/../../../../update/app/bootstrap.php';
if (file_exists($updateAppBootstrap)) {
    require_once $updateAppBootstrap;
}

$testsBaseDir = dirname(__DIR__);
$fixtureBaseDir = $testsBaseDir. '/testsuite';

if (!defined('TESTS_TEMP_DIR')) {
    define('TESTS_TEMP_DIR', $testsBaseDir . '/tmp');
}

try {
    /* Bootstrap the application */
    $settings = new \Magento\TestFramework\Bootstrap\Settings($testsBaseDir, get_defined_constants());

    if ($settings->get('TESTS_EXTRA_VERBOSE_LOG')) {
        $logWriter = new \Zend_Log_Writer_Stream('php://output');
        $logWriter->setFormatter(new \Zend_Log_Formatter_Simple('%message%' . PHP_EOL));
        $shell = new \Magento\Framework\Shell(
            new \Magento\Framework\Shell\CommandRenderer(),
            new \Zend_Log($logWriter)
        );
    } else {
        $shell = new \Magento\Framework\Shell(new \Magento\Framework\Shell\CommandRenderer());
    }

    $installConfigFile = $settings->getAsConfigFile('TESTS_INSTALL_CONFIG_FILE');
    if (!file_exists($installConfigFile)) {
        $installConfigFile .= '.dist';
    }
    $globalConfigFile = $settings->getAsConfigFile('TESTS_GLOBAL_CONFIG_FILE');
    if (!file_exists($globalConfigFile)) {
        $globalConfigFile .= '.dist';
    }
    $sandboxUniqueId = md5(sha1_file($installConfigFile));
    $installDir = TESTS_TEMP_DIR . "/sandbox-{$settings->get('TESTS_PARALLEL_THREAD', 0)}-{$sandboxUniqueId}";
    $application = new \Magento\TestFramework\Application(
        $shell,
        $installDir,
        $installConfigFile,
        $globalConfigFile,
        $settings->get('TESTS_GLOBAL_CONFIG_DIR'),
        $settings->get('TESTS_MAGENTO_MODE'),
        AutoloaderRegistry::getAutoloader(),
        true
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
        $application->install();
    }
    $application->initialize([]);

    \Magento\TestFramework\Helper\Bootstrap::setInstance(new \Magento\TestFramework\Helper\Bootstrap($bootstrap));

    $dirSearch = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
        ->create('Magento\Framework\Component\DirSearch');
    $themePackageList = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
        ->create('Magento\Framework\View\Design\Theme\ThemePackageList');
    \Magento\Framework\App\Utility\Files::setInstance(
        new Magento\Framework\App\Utility\Files(
            new \Magento\Framework\Component\ComponentRegistrar(),
            $dirSearch,
            $themePackageList
        )
    );

    /* Unset declared global variables to release the PHPUnit from maintaining their values between tests */
    unset($testsBaseDir, $logWriter, $settings, $shell, $application, $bootstrap);
} catch (\Exception $e) {
    echo $e . PHP_EOL;
    exit(1);
}
