<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Listener of PHPUnit built-in events
 */
namespace Magento\TestFramework\Event;

use PHPUnit\Event\TestSuite\Finished;
use PHPUnit\Event\TestSuite\FinishedSubscriber;
use Magento\TestFramework\Helper\Bootstrap;


final class TestSuitFinishedSubsciber implements FinishedSubscriber
{
    public function notify(Finished $event): void{
//        $className = $event->testsuite()->classname();
        $mageEvent = Magento::getDefaultEventManager();
//        $mageEvent->fireEvent('endTestSuite', [$event->testsuite()]);
        $eventTransaction = new \Magento\TestFramework\Event\Transaction($mageEvent);
        $eventTransaction->endTestSuite();
    }
}
