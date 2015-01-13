<?php
/**
 * JMeter scenarios execution script
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var $bootstrap \Magento\TestFramework\Performance\Bootstrap */
$bootstrap = require_once __DIR__ . '/framework/bootstrap.php';

$logWriter = new \Zend_Log_Writer_Stream('php://output');
$logWriter->setFormatter(new \Zend_Log_Formatter_Simple('%message%' . PHP_EOL));
$logger = new \Zend_Log($logWriter);

$shell = new \Magento\Framework\Shell(new \Magento\Framework\Shell\CommandRenderer(), $logger);
$scenarioHandler = new \Magento\TestFramework\Performance\Scenario\Handler\FileFormat();
$scenarioHandler->register(
    'jmx',
    new \Magento\TestFramework\Performance\Scenario\Handler\Jmeter($shell)
)->register(
    'php',
    new \Magento\TestFramework\Performance\Scenario\Handler\Php($shell)
);

$application = $bootstrap->createApplication($shell);
$testsuite = $bootstrap->createTestSuite($application, $scenarioHandler);

$scenarioTotalCount = count($bootstrap->getConfig()->getScenarios());
$scenarioCount = 1;
$scenarioFailCount = 0;
$testsuite->onScenarioRun(
    function (
        \Magento\TestFramework\Performance\Scenario $scenario
    ) use (
        $logger,
        &$scenarioCount,
        $scenarioTotalCount
    ) {
        $logger->log("Scenario {$scenarioCount} of {$scenarioTotalCount}: '{$scenario->getTitle()}'", \Zend_Log::INFO);
        $scenarioCount++;
    }
);
$testsuite->onScenarioFailure(
    function (
        \Magento\TestFramework\Performance\Scenario\FailureException $scenarioFailure
    ) use (
        $logger,
        &$scenarioFailCount
    ) {
        $scenario = $scenarioFailure->getScenario();
        $logger->log("Scenario '{$scenario->getTitle()}' has failed!", \Zend_Log::ERR);
        $logger->log($scenarioFailure->getMessage(), \Zend_Log::ERR);
        $scenarioFailCount++;
    }
);

$testsuite->run();

if ($scenarioFailCount) {
    $logger->log("Failed {$scenarioFailCount} of {$scenarioTotalCount} scenario(s)", \Zend_Log::INFO);
    exit(1);
} else {
    $logger->log('Successful', \Zend_Log::INFO);
}
