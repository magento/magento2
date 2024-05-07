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
        $className = $event->test()->className();
        $methodName = $event->test()->methodName();

        $objectManager = Bootstrap::getObjectManager();
        $assetRepo = $objectManager->create($className, ['name' => $methodName]);

        Magento::setCurrentEventObject($event);
        $mageEvent = Magento::getDefaultEventManager();
        $mageEvent->fireEvent('startTest', [$assetRepo]);
    }
}
