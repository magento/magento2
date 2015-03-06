<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Autoload\AutoloaderRegistry;

require_once __DIR__ . '/../../../../app/bootstrap.php';
require_once __DIR__ . '/autoload.php';

$testsBaseDir = dirname(__DIR__);
$integrationTestsDir = realpath("{$testsBaseDir}/../integration");

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

/* Bootstrap the application */
$settings = new \Magento\TestFramework\Bootstrap\Settings($testsBaseDir, get_defined_constants());
$shell = new \Magento\Framework\Shell(new \Magento\Framework\Shell\CommandRenderer(), $logger);

$installConfigFile = $settings->getAsConfigFile('TESTS_INSTALL_CONFIG_FILE');
if (!file_exists($installConfigFile)) {
    $installConfigFile = $installConfigFile . '.dist';
}
$dirList = new \Magento\Framework\App\Filesystem\DirectoryList(BP);
$application =  new \Magento\TestFramework\WebApiApplication(
    $shell,
    $dirList->getPath(DirectoryList::VAR_DIR),
    $installConfigFile,
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
\Magento\Framework\Test\Utility\Files::setInstance(new \Magento\Framework\Test\Utility\Files(BP));
unset($bootstrap, $application, $settings, $shell);
