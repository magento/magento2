<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Event;

use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Test\Prepared;
use PHPUnit\Event\Test\PreparedSubscriber;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Config;
use PHPUnit\Framework\TestCase;

class TestPreparedSubscriber implements PreparedSubscriber
{
    /**
     * @param ExecutionState $executionState
     */
    public function __construct(private readonly ExecutionState $executionState)
    {
    }

    /**
     * Test prepared Subscriber
     *
     * @param Prepared $event
     */
    public function notify(Prepared $event): void
    {
        $test = $event->test();
        if (!$test->isTestMethod()) {
            return;
        }
        $testObj = $this->createTestObject($test);

        // An exception can occur in PreparationStarted subscriber during applying fixtures.
        // In order to prevent test execution it should be thrown here, from Prepared subscriber.
        $exception = $this->executionState->popPreparationFailure($testObj->toString());
        if ($exception) {
            throw $exception;
        }

        $skipConfig = Config::getInstance()->getSkipConfiguration($testObj);
        if ($skipConfig['skip']) {
            $testObj->markTestSkipped($skipConfig['skipMessage']);
        }
        Magento::setTestPrepared(true);
    }

    /**
     * Create test instance
     *
     * @param TestMethod $test
     * @return TestCase
     */
    private function createTestObject(TestMethod $test): TestCase
    {
        $className = $test->className();
        $methodName = $test->methodName();
        $testData = $test->testData();

        $objectManager = Bootstrap::getObjectManager();
        $testObj = $objectManager->create($className, ['name' => $methodName]);
        if ($testData->hasDataFromDataProvider()) {
            // IMPORTANT: It's not actual data returned from data provider. It's simplified readable version of them.
            $dataFromDataProvider = $testData->dataFromDataProvider();
            $data = array_map(trim(...), explode(',', $dataFromDataProvider->data()));
            $testObj->setData($dataFromDataProvider->dataSetName(), $data);
        }

        return $testObj;
    }
}
