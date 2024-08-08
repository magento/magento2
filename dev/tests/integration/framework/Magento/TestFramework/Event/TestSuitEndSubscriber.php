<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */

/**
 * TestSuite Finished Subscriber
 */
namespace Magento\TestFramework\Event;

use PHPUnit\Event\TestSuite\Finished;
use PHPUnit\Event\TestSuite\FinishedSubscriber;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestSuite;

class TestSuitEndSubscriber implements FinishedSubscriber
{
    /**
     * Finish TestSuite
     *
     * @param Finished $event
     */
    public function notify(Finished $event): void
    {
        $phpUnit = Bootstrap::getObjectManager()->create(PhpUnit::class);
        if (class_exists($event->testSuite()->name())) {
            $testSuite = TestSuite::empty($event->testSuite()->name());
            $phpUnit->endTestSuite($testSuite);
        }
    }
}
