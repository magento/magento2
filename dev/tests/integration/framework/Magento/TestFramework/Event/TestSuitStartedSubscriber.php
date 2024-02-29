<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Listener of PHPUnit built-in events
 */
namespace Magento\TestFramework\Event;

use ReflectionMethod;
use PHPUnit\Event\TestSuite\StartedSubscriber;

final class TestSuitStartedSubscriber implements StartedSubscriber
{
    public function notify(\PHPUnit\Event\TestSuite\Started $event): void
    {
        $mageEvent = \Magento\TestFramework\Event\Magento::getDefaultEventManager();
        $mageEvent->fireEvent('startTestSuite');
    }
}
