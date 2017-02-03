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

$logWriter = new \Zend_Log_Writer_Stream('php://output');
$logWriter->setFormatter(new \Zend_Log_Formatter_Simple('%message%' . PHP_EOL));
$logger = new \Zend_Log($logWriter);

/** Copy test modules to app/code/Magento to make them visible for Magento instance */
$pathToCommittedTestModules = __DIR__ . '/../_files/Magento';
$pathToInstalledMagentoInstanceModules = __DIR__ . '/../../../../app/code/Magento';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($pathToCommittedTestModules));
/** @var SplFileInfo $file */
foreach ($iterator as $file) {
    if (!$file->isDir()) {
        $source = $file->getPathname();
        $relativePath = substr($source, strlen($pathToCommittedTestModules));
        $destination = $pathToInstalledMagentoInstanceModules . $relativePath;
        $targetDir = dirname($destination);
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        copy($source, $destination);
    }
}
unset($iterator, $file);

// Register the modules under '_files/'
$pathPattern = $pathToInstalledMagentoInstanceModules . '/TestModule*/registration.php';
$files = glob($pathPattern, GLOB_NOSORT);
if ($files === false) {
    throw new \RuntimeException('glob() returned error while searching in \'' . $pathPattern . '\'');
}
foreach ($files as $file) {
    include $file;
}

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
    ->create('Magento\Framework\Component\DirSearch');
$themePackageList = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create('Magento\Framework\View\Design\Theme\ThemePackageList');
\Magento\Framework\App\Utility\Files::setInstance(
    new \Magento\Framework\App\Utility\Files(
        new \Magento\Framework\Component\ComponentRegistrar(),
        $dirSearch,
        $themePackageList
    )
);
unset($bootstrap, $application, $settings, $shell);
