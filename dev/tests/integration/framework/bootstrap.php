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

require __DIR__ . '/Magento/Test/Bootstrap.php';
require __DIR__ . '/../../static/testsuite/Utility/Classes.php';

Utility_Files::init(new Utility_Files(realpath(__DIR__ . '/../../../..')));

$baseDir = dirname(__DIR__);

/*
 * Setup include path for autoload purpose.
 * Include path setup is intentionally moved out from the phpunit.xml to simplify maintenance of CI builds.
 */
set_include_path(implode(
    PATH_SEPARATOR,
    array(
        "$baseDir/framework",
        "$baseDir/testsuite",
        get_include_path()
    )
));

if (defined('TESTS_CLEANUP_ACTION') && TESTS_CLEANUP_ACTION) {
    $cleanupAction = TESTS_CLEANUP_ACTION;
} else {
    $cleanupAction = Magento_Test_Bootstrap::CLEANUP_NONE;
}

if (defined('TESTS_LOCAL_CONFIG_FILE') && TESTS_LOCAL_CONFIG_FILE) {
    $localXmlFile = "$baseDir/" . TESTS_LOCAL_CONFIG_FILE;
    if (!is_file($localXmlFile) && substr($localXmlFile, -5) != '.dist') {
        $localXmlFile .= '.dist';
    }
} else {
    $localXmlFile = "$baseDir/etc/local-mysql.xml";
}

if (defined('TESTS_GLOBAL_CONFIG_FILES') && TESTS_GLOBAL_CONFIG_FILES) {
    $globalEtcFiles = TESTS_GLOBAL_CONFIG_FILES;
} else {
    $globalEtcFiles = "../../../app/etc/*.xml";
}
$globalEtcFiles .= ';etc/integration-tests-config.xml';

if (defined('TESTS_MODULE_CONFIG_FILES') && TESTS_MODULE_CONFIG_FILES) {
    $moduleEtcFiles = TESTS_MODULE_CONFIG_FILES;
} else {
    $moduleEtcFiles = "../../../app/etc/modules/*.xml";
}

$developerMode = false;
if (defined('TESTS_MAGENTO_DEVELOPER_MODE') && TESTS_MAGENTO_DEVELOPER_MODE == 'enabled') {
    $developerMode = true;
}

Magento_Test_Bootstrap::setInstance(new Magento_Test_Bootstrap(
    realpath("$baseDir/../../../"),
    $localXmlFile,
    $globalEtcFiles,
    $moduleEtcFiles,
    "$baseDir/tmp",
    $cleanupAction,
    $developerMode
));

/* Enable profiler if necessary */
if (defined('TESTS_PROFILER_FILE') && TESTS_PROFILER_FILE) {
    Magento_Profiler::registerOutput(
        new Magento_Profiler_Output_Csvfile($baseDir . DIRECTORY_SEPARATOR . TESTS_PROFILER_FILE)
    );
}

/* Enable profiler with bamboo friendly output format */
if (defined('TESTS_BAMBOO_PROFILER_FILE') && defined('TESTS_BAMBOO_PROFILER_METRICS_FILE')) {
    Magento_Profiler::registerOutput(new Magento_Test_Profiler_OutputBamboo(
        $baseDir . DIRECTORY_SEPARATOR . TESTS_BAMBOO_PROFILER_FILE,
        require($baseDir . DIRECTORY_SEPARATOR . TESTS_BAMBOO_PROFILER_METRICS_FILE)
    ));
}

/* Activate custom annotations in doc comments */
/*
 * Note: order of registering (and applying) annotations is important.
 * To allow config fixtures to deal with fixture stores, data fixtures should be processed before config fixtures.
 */
Magento_Test_Listener::registerObserver('Magento_Test_Listener_Annotation_Isolation');
Magento_Test_Listener::registerObserver('Magento_Test_Listener_Annotation_Fixture');
Magento_Test_Listener::registerObserver('Magento_Test_Listener_Annotation_Config');

/* Unset declared global variables to release PHPUnit from maintaining their values between tests */
unset($baseDir, $localXmlFile, $globalEtcFiles, $moduleEtcFiles);
