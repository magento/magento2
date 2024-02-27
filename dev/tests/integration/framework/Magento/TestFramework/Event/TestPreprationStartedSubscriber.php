<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestFramework\Event;

use PHPUnit\Event\Test\PreparationStarted;
use PHPUnit\Event\Test\PreparationStartedSubscriber;
use Magento\TestFramework\Helper\Bootstrap;

final class TestPreprationStartedSubscriber implements PreparationStartedSubscriber
{
    public function notify(PreparationStarted $event): void{
        $className = $event->test()->className();
        $methodName = $event->test()->methodName();

        $objectManager = Bootstrap::getObjectManager();
        $assetRepo = $objectManager->create($className, ['name' => $methodName]);

        Magento::setCurrentEventObject($event);
        $mageEvent = Magento::getDefaultEventManager();
        $mageEvent->fireEvent('startTest', [$assetRepo]);
    }
}
