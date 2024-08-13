<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */

/**
 * TestPreparation Started Subscriber
 */
namespace Magento\TestFramework\Event;

use PHPUnit\Event\Test\PreparationStarted;
use PHPUnit\Event\Test\PreparationStartedSubscriber;
use Magento\TestFramework\Helper\Bootstrap;

class TestPreprationStartedSubscriber implements PreparationStartedSubscriber
{
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
        $phpUnit->startTest($testObj);
    }
}
