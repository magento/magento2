<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Listener of PHPUnit built-in events
 */
namespace Magento\TestFramework\Event;

use PHPUnit\Event\Test\SkippedSubscriber;
use PHPUnit\Event\Test\Skipped;
use Magento\TestFramework\Helper\Bootstrap;


final class TestSkippedSubscriber implements SkippedSubscriber
{
    public function notify(Skipped $event): void
    {
        $className = $event->test()->className();
        $methodName = $event->test()->methodName();

        if(!in_array($methodName, ['testAclHasAccess', 'testAclNoAccess', 'testCreateInvalidPriceFormat'])) {
            $objectManager = Bootstrap::getObjectManager();
            $assetRepo = $objectManager->create($className, ['name' => $methodName]);

            $mageEvent = Magento::getDefaultEventManager();
            $mageEvent->fireEvent('endTest', [$assetRepo], true);
            Magento::setCurrentEventObject(null);
        }
    }
}
