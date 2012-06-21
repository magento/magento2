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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require_once __DIR__ . '/../../../../app/bootstrap.php';
require_once __DIR__ . '/../../static/testsuite/Utility/Classes.php';

$testsBaseDir = dirname(__DIR__);
$testsTmpDir = "$testsBaseDir/tmp";
$magentoBaseDir = realpath("$testsBaseDir/../../../");

/*
 * Setup include path for autoload purpose.
 * Include path setup is intentionally moved out from the phpunit.xml to simplify maintenance of CI builds.
 */
set_include_path(implode(
    PATH_SEPARATOR,
    array(
        "$testsBaseDir/framework",
        "$testsBaseDir/testsuite",
        get_include_path()
    )
));

if (defined('TESTS_CLEANUP_ACTION') && TESTS_CLEANUP_ACTION) {
    $cleanupAction = TESTS_CLEANUP_ACTION;
} else {
    $cleanupAction = Magento_Test_Bootstrap::CLEANUP_NONE;
}

if (defined('TESTS_LOCAL_CONFIG_FILE') && TESTS_LOCAL_CONFIG_FILE) {
    $localXmlFile = "$testsBaseDir/" . TESTS_LOCAL_CONFIG_FILE;
    if (!is_file($localXmlFile) && substr($localXmlFile, -5) != '.dist') {
        $localXmlFile .= '.dist';
    }
} else {
    $localXmlFile = "$testsBaseDir/etc/local-mysql.xml";
}

if (defined('TESTS_GLOBAL_CONFIG_FILES') && TESTS_GLOBAL_CONFIG_FILES) {
    $globalEtcFiles = TESTS_GLOBAL_CONFIG_FILES;
} else {
    $globalEtcFiles = "../../../app/etc/*.xml";
}

if (defined('TESTS_MODULE_CONFIG_FILES') && TESTS_MODULE_CONFIG_FILES) {
    $moduleEtcFiles = TESTS_MODULE_CONFIG_FILES;
} else {
    $moduleEtcFiles = "../../../app/etc/modules/*.xml";
}

$developerMode = false;
if (defined('TESTS_MAGENTO_DEVELOPER_MODE') && TESTS_MAGENTO_DEVELOPER_MODE == 'enabled') {
    $developerMode = true;
}

/* Enable profiler if necessary */
if (defined('TESTS_PROFILER_FILE') && TESTS_PROFILER_FILE) {
    Magento_Profiler::registerOutput(
        new Magento_Profiler_Output_Csvfile($testsBaseDir . DIRECTORY_SEPARATOR . TESTS_PROFILER_FILE)
    );
}

/* Enable profiler with bamboo friendly output format */
if (defined('TESTS_BAMBOO_PROFILER_FILE') && defined('TESTS_BAMBOO_PROFILER_METRICS_FILE')) {
    Magento_Profiler::registerOutput(new Magento_Test_Profiler_OutputBamboo(
        $testsBaseDir . DIRECTORY_SEPARATOR . TESTS_BAMBOO_PROFILER_FILE,
        require($testsBaseDir . DIRECTORY_SEPARATOR . TESTS_BAMBOO_PROFILER_METRICS_FILE)
    ));
}

/*
 * Activate custom DocBlock annotations.
 * Note: order of registering (and applying) annotations is important.
 * To allow config fixtures to deal with fixture stores, data fixtures should be processed before config fixtures.
 */
$eventManager = new Magento_Test_EventManager(array(
    new Magento_Test_Annotation_AppIsolation(),
    new Magento_Test_Event_Transaction(new Magento_Test_EventManager(array(
        new Magento_Test_Annotation_DbIsolation(),
        new Magento_Test_Annotation_DataFixture("$testsBaseDir/testsuite"),
    ))),
    new Magento_Test_Annotation_ConfigFixture(),
));
Magento_Test_Event_PhpUnit::setDefaultEventManager($eventManager);
Magento_Test_Event_Magento::setDefaultEventManager($eventManager);

/* Bootstrap the application */
Magento_Test_Bootstrap::setInstance(new Magento_Test_Bootstrap(
    $magentoBaseDir,
    $localXmlFile,
    $globalEtcFiles,
    $moduleEtcFiles,
     'etc/integration-tests-config.xml',
    $testsTmpDir,
    $cleanupAction,
    $developerMode
));

Utility_Files::init(new Utility_Files($magentoBaseDir));

/* Unset declared global variables to release PHPUnit from maintaining their values between tests */
unset($testsBaseDir, $testsTmpDir, $magentoBaseDir, $localXmlFile, $globalEtcFiles, $moduleEtcFiles, $eventManager);
