<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */

/**
 * Test Finished Subscriber
 */
namespace Magento\TestFramework\Event;

use PHPUnit\Event\Test\FinishedSubscriber;
use PHPUnit\Event\Test\Finished;
use Magento\TestFramework\Helper\Bootstrap;

class TestFinishedSubscriber implements FinishedSubscriber
{
    /**
     * Test finished Subscriber
     *
     * @param Finished $event
     */
    public function notify(Finished $event): void
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
