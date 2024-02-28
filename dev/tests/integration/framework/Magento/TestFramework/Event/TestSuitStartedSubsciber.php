<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Listener of PHPUnit built-in events
 */
namespace Magento\TestFramework\Event;

use PHPUnit\Event\TestSuite\Started;
use PHPUnit\Event\TestSuite\StartedSubscriber;


final class TestSuitStartedSubsciber implements StartedSubscriber
{
    public function notify(Started $event): void{
        $mageEvent = Magento::getDefaultEventManager();
        $mageEvent->fireEvent('startTestSuite');
    }
}
