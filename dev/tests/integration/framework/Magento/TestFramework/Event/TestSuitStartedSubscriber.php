<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */

/**
 * TestSuite Started Subscriber
 */
namespace Magento\TestFramework\Event;

use PHPUnit\Event\TestSuite\Started;
use PHPUnit\Event\TestSuite\StartedSubscriber;

class TestSuitStartedSubscriber implements StartedSubscriber
{
    /**
     * TestSuit Started Subscriber
     *
     * @param Started $event
     */
    public function notify(Started $event): void
    {
        $mageEvent = Magento::getDefaultEventManager();
        $mageEvent->fireEvent('startTestSuite');
    }
}
