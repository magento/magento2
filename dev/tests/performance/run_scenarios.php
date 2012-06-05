<?php
/**
 * JMeter scenarios execution script
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

$baseDir = realpath(__DIR__ . '/../../../');

$configFile = __DIR__ . '/config.php';
$configFile = file_exists($configFile) ? $configFile : "$configFile.dist";
$config = require($configFile);
$installOptions = isset($config['install_options']) ? $config['install_options'] : array();
$scenarioFilesPattern = __DIR__ . '/' . $config['scenarios'];
$scenarioFiles = glob($scenarioFilesPattern, GLOB_BRACE);
$scenarioParams = $config['scenario_params'];
$fixtureFiles = glob(__DIR__ . '/' . $config['fixtures'], GLOB_BRACE);
$reportDir = __DIR__ . '/' . $config['report_dir'];

/* Validate scenarios existence */
if (!$scenarioFiles) {
    echo "No scenario files match '$scenarioFilesPattern' pattern." . PHP_EOL;
    exit(1);
}

/* Validate scenario params */
if (empty($scenarioParams['host']) || empty($scenarioParams['path'])) {
    echo "Scenario parameters must specify 'host' and 'path'." . PHP_EOL;
    exit(1);
}

/* Validate JMeter command presence */
$jMeterJarFile = getenv('jmeter_jar_file') ?: 'ApacheJMeter.jar';
$jMeterExecutable = 'java -jar ' . escapeshellarg($jMeterJarFile);
exec("$jMeterExecutable --version 2>&1", $jMeterOutput, $exitCode);
if ($exitCode) {
    echo implode(PHP_EOL, $jMeterOutput);
    exit($exitCode);
}

/* Install application */
if ($installOptions) {
    $baseUrl = 'http://' . $scenarioParams['host'] . $scenarioParams['path'];
    $installOptions['url'] = $baseUrl;
    $installOptions['secure_base_url'] = $baseUrl;
    $installCmd = sprintf('php -f %s --', escapeshellarg("$baseDir/dev/shell/install.php"));
    passthru("$installCmd --uninstall", $exitCode);
    if ($exitCode) {
        exit($exitCode);
    }
    foreach ($installOptions as $optionName => $optionValue) {
        $installCmd .= sprintf(' --%s %s', $optionName, escapeshellarg($optionValue));
    }
    passthru($installCmd, $exitCode);
    if ($exitCode) {
        exit($exitCode);
    }
}

/* Initialize Magento application */
require_once __DIR__ . '/../../../app/bootstrap.php';
Mage::app();

/* Clean reports */
Varien_Io_File::rmdirRecursive($reportDir);

/* Apply fixtures */
foreach ($fixtureFiles as $fixture) {
    require_once $fixture;
}

/* Run all indexer processes */
/** @var $indexer Mage_Index_Model_Indexer */
$indexer = Mage::getModel('Mage_Index_Model_Indexer');
/** @var $process Mage_Index_Model_Process */
foreach ($indexer->getProcessesCollection() as $process) {
    if ($process->getIndexer()->isVisible()) {
        $process->reindexEverything();
    }
}

/* Execute each scenario couple times to populate cache (if any) before measuring performance */
$scenarioDryRunParams = array_merge($scenarioParams, array('users' => 1, 'loops' => 2));
foreach ($scenarioFiles as $scenarioFile) {
    $scenarioCmd = buildJMeterCmd($jMeterExecutable, $scenarioFile, $scenarioDryRunParams);
    passthru($scenarioCmd, $exitCode);
    if ($exitCode) {
        exit($exitCode);
    }
}

/* Execute scenarios and collect failures */
$failures = array();
foreach ($scenarioFiles as $scenarioFile) {
    $scenarioLogFile = $reportDir . DIRECTORY_SEPARATOR . basename($scenarioFile, '.jmx') . '.jtl';
    $scenarioCmd = buildJMeterCmd($jMeterExecutable, $scenarioFile, $scenarioParams, $scenarioLogFile);
    passthru($scenarioCmd, $exitCode);
    if ($exitCode) {
        exit($exitCode);
    }
    $scenarioLogXml = simplexml_load_file($scenarioLogFile);
    $failedAssertions = $scenarioLogXml->xpath('//assertionResult[failure[text()="true"] or error[text()="true"]]');
    if ($failedAssertions) {
        foreach ($failedAssertions as $assertionResult) {
            if (isset($assertionResult->failureMessage)) {
                $failures[$scenarioFile][] = (string)$assertionResult->failureMessage;
            }
            if (isset($assertionResult->errorMessage)) {
                $failures[$scenarioFile][] = (string)$assertionResult->errorMessage;
            }
        }
    }
}

/* Handle failures */
if ($failures) {
    foreach ($failures as $scenarioFile => $failureMessages) {
        echo "Scenario '$scenarioFile' has failed!" . PHP_EOL;
        echo implode(PHP_EOL, $failureMessages);
    }
    exit(1);
}


/**
 * Build JMeter command
 *
 * @param string $jMeterExecutable
 * @param string $testPlanFile
 * @param array $localProperties
 * @param string|null $sampleLogFile
 * @return string
 */
function buildJMeterCmd($jMeterExecutable, $testPlanFile, array $localProperties = array(), $sampleLogFile = null) {
    $result = $jMeterExecutable . ' -n -t ' . escapeshellarg($testPlanFile);
    if ($sampleLogFile) {
        $result .= ' -l ' . escapeshellarg($sampleLogFile);
    }
    foreach ($localProperties as $key => $value) {
        $result .= " -J$key=$value";
    }
    return $result;
}
