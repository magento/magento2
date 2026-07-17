<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Event;

use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Test\PreparationStarted;
use PHPUnit\Event\Test\PreparationStartedSubscriber;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * TestPreparation Started Subscriber
 */
class TestPreprationStartedSubscriber implements PreparationStartedSubscriber
{
    /**
     * @param ExecutionState $executionState
     */
    public function __construct(private readonly ExecutionState $executionState)
    {
    }

    /**
     * Test Preparation Started Subscriber
     *
     * @param PreparationStarted $event
     */
    public function notify(PreparationStarted $event): void
    {
        $test = $event->test();
        if (!$test->isTestMethod()) {
            return;
        }
        $testObj = $this->createTestObject($test);

        Magento::setCurrentEventObject($event);

        $phpUnit = Bootstrap::getObjectManager()->create(PhpUnit::class);
        try {
            $phpUnit->startTest($testObj);
        } catch (\Throwable $e) {
            $this->executionState->registerPreparationFailure($testObj->toString(), $e);
        }
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

        $testObj = Bootstrap::getObjectManager()->create($className, ['name' => $methodName]);
        if ($testData->hasDataFromDataProvider()) {
            // IMPORTANT: It's not actual data returned from data provider. It's simplified readable version of them.
            $dataFromDataProvider = $testData->dataFromDataProvider();
            $data = array_map(trim(...), explode(',', $dataFromDataProvider->data()));
            $testObj->setData($dataFromDataProvider->dataSetName(), $data);
        }

        return $testObj;
    }
}
