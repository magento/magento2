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
            $testObj = $objectManager->create($className, ['name' => $methodName]);

            $phpUnit = $objectManager->create(PhpUnit::class);
            $phpUnit->endTest($testObj, 0);
            Magento::setCurrentEventObject(null);
            Magento::setTestPrepared(false);
        }
    }
}
