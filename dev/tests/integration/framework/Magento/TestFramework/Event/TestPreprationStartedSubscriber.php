<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Event;

use PHPUnit\Event\Test\PreparationStarted;
use PHPUnit\Event\Test\PreparationStartedSubscriber;
use Magento\TestFramework\Helper\Bootstrap;

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
        $objectManager = Bootstrap::getObjectManager();
        $className = $event->test()->className();
        $methodName = $event->test()->methodName();

        $testObj = $objectManager->create($className, ['name' => $methodName]);

        Magento::setCurrentEventObject($event);

        $phpUnit = $objectManager->create(PhpUnit::class);
        try {
            $phpUnit->startTest($testObj);
        } catch (\Throwable $e) {
            $this->executionState->registerPreparationFailure($testObj->toString(), $e);
        }
    }
}
