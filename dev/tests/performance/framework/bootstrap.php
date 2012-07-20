<?php
/**
 * Performance framework bootstrap script
 *
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
 * @category    Magento
 * @package     performance_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$testsBaseDir = realpath(__DIR__ . '/..');
$magentoBaseDir = realpath($testsBaseDir . '/../../../');

require_once "$magentoBaseDir/app/bootstrap.php";
Magento_Autoload::getInstance()->addIncludePath("$testsBaseDir/framework");

$configFile = "$testsBaseDir/config.php";
$configFile = file_exists($configFile) ? $configFile : "$configFile.dist";
$configData = require($configFile);
$config = new Magento_Config($configData, $testsBaseDir);

$installOptions = $config->getInstallOptions();
if ($installOptions) {
    $baseUrl = 'http://' . $config->getApplicationUrlHost() . $config->getApplicationUrlPath();
    $installOptions = array_merge($installOptions, array('url' => $baseUrl, 'secure_base_url' => $baseUrl));
    $installer = new Magento_Installer($magentoBaseDir . '/dev/shell/install.php', new Magento_Shell(true));
    echo 'Uninstalling application' . PHP_EOL;
    $installer->uninstall();
    echo "Installing application at '$baseUrl'" . PHP_EOL;
    $installer->install($installOptions, $config->getFixtureFiles());
    echo PHP_EOL;
}

$reportDir = $config->getReportDir();
if (file_exists($reportDir) && !Varien_Io_File::rmdirRecursive($reportDir)) {
    throw new Magento_Exception("Cannot cleanup reports directory '$reportDir'.");
}

return $config;
