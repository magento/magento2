<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */

/**
 * Test Errored Subscriber
 */
namespace Magento\TestFramework\Event;

use PHPUnit\Event\Test\ErroredSubscriber;
use PHPUnit\Event\Test\Errored;
use Magento\TestFramework\Helper\Bootstrap;

class TestErroredSubscriber implements ErroredSubscriber
{
    /**
     * Errored Subscriber
     *
     * @param Errored $event
     */
    public function notify(Errored $event): void
    {
        $className = $event->test()->className();
        $methodName = $event->test()->methodName();

        $objectManager = Bootstrap::getObjectManager();
        $assetRepo = $objectManager->create($className, ['name' => $methodName]);

        $mageEvent = Magento::getDefaultEventManager();
        $mageEvent->fireEvent('endTest', [$assetRepo], true);
        Magento::setCurrentEventObject(null);
        Magento::setTestPrepared(false);
    }
}
