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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/** @var $config Magento_Performance_Config */
$config = require_once __DIR__ . '/framework/bootstrap.php';

$shell = new Magento_Shell(true);
$scenarioHandler = new Magento_Performance_Scenario_Handler_FileFormat();
$scenarioHandler
    ->register('jmx', new Magento_Performance_Scenario_Handler_Jmeter($shell))
    ->register('php', new Magento_Performance_Scenario_Handler_Php($shell))
;

$testsuite = new Magento_Performance_Testsuite($config, new Magento_Application($config, $shell), $scenarioHandler);

$scenarioTotalCount = count($config->getScenarios());
$scenarioCount = 1;
$scenarioFailCount = 0;
$testsuite->onScenarioRun(function (Magento_Performance_Scenario $scenario) use (&$scenarioCount, $scenarioTotalCount) {
    echo "Scenario $scenarioCount of $scenarioTotalCount: '{$scenario->getTitle()}'" . PHP_EOL;
    $scenarioCount++;
});
$testsuite->onScenarioFailure(
    function (Magento_Performance_Scenario_FailureException $scenarioFailure) use (&$scenarioFailCount) {
        $scenario = $scenarioFailure->getScenario();
        echo "Scenario '{$scenario->getTitle()}' has failed!" . PHP_EOL
            . $scenarioFailure->getMessage() . PHP_EOL . PHP_EOL;
        $scenarioFailCount++;
    }
);

$testsuite->run();

if ($scenarioFailCount) {
    echo "Failed $scenarioFailCount of $scenarioTotalCount scenario(s)" . PHP_EOL;
    exit(1);
} else {
    echo 'Successful' . PHP_EOL;
}
