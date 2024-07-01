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

class TestSuitEndSubscriber implements FinishedSubscriber
{
    /**
     * Finish TestSuite
     *
     * @param Finished $event
     */
    public function notify(Finished $event): void
    {
        $mageEvent = Magento::getDefaultEventManager();
        $mageEvent->fireEvent('rollbackTransaction');
    }
}
