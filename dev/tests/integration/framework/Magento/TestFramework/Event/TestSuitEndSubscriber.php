<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Listener of PHPUnit built-in events
 */
namespace Magento\TestFramework\Event;

use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Event\TestSuite\FinishedSubscriber;
//use PHPUnit\Event\TestSuite;
use PHPUnit\Framework\TestSuite;

final class TestSuitEndSubscriber implements FinishedSubscriber
{
    public function notify(\PHPUnit\Event\TestSuite\Finished $event): void
    {
        $mageEvent = \Magento\TestFramework\Event\Magento::getDefaultEventManager();
        $mageEvent->fireEvent('rollbackTransaction');
    }
}
