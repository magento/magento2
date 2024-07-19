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

        $testObj = $objectManager->create($className, ['name' => $methodName]);
        $phpUnit = $objectManager->create(PhpUnit::class);
        $phpUnit->endTest($testObj, 0);

        Magento::setCurrentEventObject(null);
        Magento::setTestPrepared(false);
    }
}
