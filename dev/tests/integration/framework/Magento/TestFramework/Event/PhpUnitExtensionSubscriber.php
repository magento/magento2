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
use PHPUnit\Event\Test\PreparationStartedSubscriber;
use Magento\TestFramework\Helper\Bootstrap;

final class PhpUnitExtensionSubscriber implements PreparationStartedSubscriber
{
    public function notify(\PHPUnit\Event\Test\PreparationStarted $event): void
    {
        $className = $event->test()->className();
        $methodName = $event->test()->methodName();

        $objectManager = Bootstrap::getObjectManager();
        $assetRepo = $objectManager->create($className, ['name' => $methodName]);

        Magento::setCurrentEventObject($event);
        $mageEvent = \Magento\TestFramework\Event\Magento::getDefaultEventManager();
        $mageEvent->fireEvent('startTest', [$assetRepo]);
    }
}
