<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Listener of PHPUnit built-in events
 */
namespace Magento\TestFramework\Event;

use PHPUnit\Event\Test\FinishedSubscriber;
use PHPUnit\Event\Test\Finished;
use Magento\TestFramework\Helper\Bootstrap;


final class PhpUnitExtensionSubscriberFinished implements FinishedSubscriber
{
    public function notify(Finished $event): void
    {
        $className = $event->test()->className();
        $methodName = $event->test()->methodName();

        $objectManager = Bootstrap::getObjectManager();
        $assetRepo = $objectManager->create($className, ['name' => $methodName]);

        $mageEvent = Magento::getDefaultEventManager();
        $mageEvent->fireEvent('endTest', [$assetRepo], true);
        Magento::setCurrentEventObject(null);
    }
}
