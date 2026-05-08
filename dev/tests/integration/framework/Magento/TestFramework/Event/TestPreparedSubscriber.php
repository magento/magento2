<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Event;

use PHPUnit\Event\Test\Prepared;
use PHPUnit\Event\Test\PreparedSubscriber;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Config;

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
        $className = $event->test()->className();
        $methodName = $event->test()->methodName();

        $objectManager = Bootstrap::getObjectManager();
        $testObj = $objectManager->create($className, ['name' => $methodName]);

        // An exception can occur in PreparationStarted subscriber during applying fixtures.
        // In order to prevent test execution it should be thrown here, from Prepared subscriber.
        $exception = $this->executionState->popPreparationFailure($testObj->toString());
        if ($exception) {
            throw $exception;
        }

        $testData = $event->test()->testData();
        if ($testData->hasDataFromDataProvider()) {
            $dataSetName = $testData->dataFromDataProvider()->dataSetName();
            $testObj->setData($dataSetName, ['']);
        }

        $skipConfig = Config::getInstance()->getSkipConfiguration($testObj);
        if ($skipConfig['skip']) {
            $testObj->markTestSkipped($skipConfig['skipMessage']);
        }
        Magento::setTestPrepared(true);
    }
}
