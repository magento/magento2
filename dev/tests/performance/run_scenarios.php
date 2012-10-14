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

/** @var $bootstrap Magento_Performance_Config */
$config = require_once __DIR__ . '/framework/bootstrap.php';

$shell = new Magento_Shell(true);
$scenarioHandler = new Magento_Performance_Scenario_Handler_Statistics(
    new Magento_Performance_Scenario_Handler_Aggregate(array(
        new Magento_Performance_Scenario_Handler_Jmeter($shell),
        new Magento_Performance_Scenario_Handler_Php($shell),
    ))
);

$scenarioTotalCount = count($config->getScenarios());
$scenarioCount = 1;
$scenarioHandler->onScenarioFirstRun(function ($scenarioFile) use (&$scenarioCount, $scenarioTotalCount) {
    echo "Scenario $scenarioCount of $scenarioTotalCount: '$scenarioFile'" . PHP_EOL;
    $scenarioCount++;
});
$scenarioHandler->onScenarioFailure(function ($scenarioFile, Magento_Performance_Scenario_FailureException $failure) {
    echo "Scenario '$scenarioFile' has failed!" . PHP_EOL . $failure->getMessage() . PHP_EOL . PHP_EOL;
});

$testsuite = new Magento_Performance_Testsuite($config, new Magento_Application($config, $shell), $scenarioHandler);
$testsuite->run();

$scenarioFailures = $scenarioHandler->getFailures();
if ($scenarioFailures) {
    $scenarioFailCount = count($scenarioFailures);
    echo "Failed $scenarioFailCount of $scenarioTotalCount scenario(s)" . PHP_EOL;
    exit(1);
} else {
    echo 'Successful' . PHP_EOL;
}
