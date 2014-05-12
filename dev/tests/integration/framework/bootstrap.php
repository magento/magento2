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
require_once __DIR__ . '/../../../../app/bootstrap.php';
require_once __DIR__ . '/../../static/framework/Magento/TestFramework/Utility/Classes.php';
require_once __DIR__ . '/../../static/framework/Magento/TestFramework/Utility/AggregateInvoker.php';

$testsBaseDir = dirname(__DIR__);
$testsTmpDir = "{$testsBaseDir}/tmp";
$magentoBaseDir = realpath("{$testsBaseDir}/../../../");

(new \Magento\Framework\Autoload\IncludePath())->addIncludePath(
    array("{$testsBaseDir}/framework", "{$testsBaseDir}/testsuite")
);

function tool_autoloader($className)
{
    if (strpos($className, 'Magento\\Tools\\') === false) {
        return false;
    }

    $filePath = str_replace('\\', '/', $className);
    $filePath = BP . '/dev/tools/' . $filePath . '.php';

    if (file_exists($filePath)) {
        include_once $filePath;
    } else {
        return false;
    }
}

spl_autoload_register('tool_autoloader');

/* Bootstrap the application */
$invariantSettings = array('TESTS_LOCAL_CONFIG_EXTRA_FILE' => 'etc/integration-tests-config.xml');
$bootstrap = new \Magento\TestFramework\Bootstrap(
    new \Magento\TestFramework\Bootstrap\Settings($testsBaseDir, $invariantSettings + get_defined_constants()),
    new \Magento\TestFramework\Bootstrap\Environment(),
    new \Magento\TestFramework\Bootstrap\DocBlock("{$testsBaseDir}/testsuite"),
    new \Magento\TestFramework\Bootstrap\Profiler(new \Magento\Framework\Profiler\Driver\Standard()),
    new \Magento\Framework\Shell(new \Magento\Framework\Shell\CommandRenderer()),
    $testsTmpDir
);
$bootstrap->runBootstrap();

\Magento\TestFramework\Helper\Bootstrap::setInstance(new \Magento\TestFramework\Helper\Bootstrap($bootstrap));

Magento\TestFramework\Utility\Files::setInstance(new Magento\TestFramework\Utility\Files($magentoBaseDir));

/* Unset declared global variables to release the PHPUnit from maintaining their values between tests */
unset($bootstrap);
