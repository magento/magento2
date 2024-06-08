<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */

/**
 * Test Skipped Subscriber
 */
namespace Magento\TestFramework\Event;

use PHPUnit\Event\Test\SkippedSubscriber;
use PHPUnit\Event\Test\Skipped;
use Magento\TestFramework\Helper\Bootstrap;

class TestSkippedSubscriber implements SkippedSubscriber
{
    /**
     * Skipped Subscriber
     *
     * @param Skipped $event
     */
    public function notify(Skipped $event): void
    {
        $className = $event->test()->className();
        $methodName = $event->test()->methodName();

        if (!Magento::getTestPrepared()) {
            $objectManager = Bootstrap::getObjectManager();
            $assetRepo = $objectManager->create($className, ['name' => $methodName]);

            $mageEvent = Magento::getDefaultEventManager();
            $mageEvent->fireEvent('endTest', [$assetRepo], true);
            Magento::setCurrentEventObject(null);
            Magento::setTestPrepared(false);
        }
    }
}
