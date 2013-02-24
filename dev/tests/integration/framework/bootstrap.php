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
 * @category    Magento
 * @package     Magento
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require_once __DIR__ . '/../../../../app/bootstrap.php';
require_once __DIR__ . '/../../static/testsuite/Utility/Classes.php';

$testsBaseDir = dirname(__DIR__);
$testsTmpDir = "$testsBaseDir/tmp";
$magentoBaseDir = realpath("$testsBaseDir/../../../");

Magento_Autoload_IncludePath::addIncludePath(array(
    "$testsBaseDir/framework",
    "$testsBaseDir/testsuite",
));

/* Bootstrap the application */
$invariantSettings = array(
    'TESTS_LOCAL_CONFIG_EXTRA_FILE' => 'etc/integration-tests-config.xml',
);
$bootstrap = new Magento_Test_Bootstrap(
    new Magento_Test_Bootstrap_Settings($testsBaseDir, $invariantSettings + get_defined_constants()),
    new Magento_Test_Bootstrap_Environment(),
    new Magento_Test_Bootstrap_DocBlock("$testsBaseDir/testsuite"),
    new Magento_Test_Bootstrap_Profiler(new Magento_Profiler_Driver_Standard()),
    new Magento_Shell(),
    $testsTmpDir
);
$bootstrap->runBootstrap();

Magento_Test_Helper_Bootstrap::setInstance(new Magento_Test_Helper_Bootstrap($bootstrap));

Utility_Files::init(new Utility_Files($magentoBaseDir));

/* Unset declared global variables to release the PHPUnit from maintaining their values between tests */
unset($bootstrap);
