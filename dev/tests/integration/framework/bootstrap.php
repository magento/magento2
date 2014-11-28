<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
use Magento\Framework\Autoload\AutoloaderRegistry;

require_once __DIR__ . '/../../../../app/bootstrap.php';
require_once __DIR__ . '/autoload.php';

$testsBaseDir = dirname(__DIR__);
$testsTmpDir = "{$testsBaseDir}/tmp";
$magentoBaseDir = realpath("{$testsBaseDir}/../../../");

try {
    /* Bootstrap the application */
    $settings = new \Magento\TestFramework\Bootstrap\Settings($testsBaseDir, get_defined_constants());

    if ($settings->get('TESTS_EXTRA_VERBOSE_LOG')) {
        $logWriter = new \Zend_Log_Writer_Stream('php://output');
        $logWriter->setFormatter(new \Zend_Log_Formatter_Simple('%message%' . PHP_EOL));
        $shell = new \Magento\Framework\Shell(new \Magento\Framework\Shell\CommandRenderer, new \Zend_Log($logWriter));
    } else {
        $shell = new \Magento\Framework\Shell(new \Magento\Framework\Shell\CommandRenderer);
    }

    $installConfigFile = $settings->getAsConfigFile('TESTS_INSTALL_CONFIG_FILE');
    if (!file_exists($installConfigFile)) {
        $installConfigFile = $installConfigFile . '.dist';
    }
    $sandboxUniqueId = md5(sha1_file($installConfigFile));
    $installDir = "{$testsTmpDir}/sandbox-{$sandboxUniqueId}";
    $application = new \Magento\TestFramework\Application(
        $shell,
        $installDir,
        $installConfigFile,
        $settings->get('TESTS_GLOBAL_CONFIG_DIR'),
        $settings->get('TESTS_MAGENTO_MODE'),
        AutoloaderRegistry::getAutoloader()
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
    $application->initialize();

    \Magento\TestFramework\Helper\Bootstrap::setInstance(new \Magento\TestFramework\Helper\Bootstrap($bootstrap));

    \Magento\Framework\Test\Utility\Files::setInstance(new Magento\Framework\Test\Utility\Files($magentoBaseDir));

    /* Unset declared global variables to release the PHPUnit from maintaining their values between tests */
    unset($testsBaseDir, $testsTmpDir, $magentoBaseDir, $logWriter, $settings, $shell, $application, $bootstrap);
} catch (\Exception $e) {
    echo $e . PHP_EOL;
    exit(1);
}
