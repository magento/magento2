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


final class PhpUnitExtensionSubsscriberFinished implements FinishedSubscriber
{
    public function notify(\PHPUnit\Event\Test\Finished $event): void{
        $className = $event->test()->className();
        $methodName = $event->test()->methodName();

        $objectManager = Bootstrap::getObjectManager();
        $assetRepo = $objectManager->create($className, ['name' => $methodName]);

        $mageEvent = \Magento\TestFramework\Event\Magento::getDefaultEventManager();
//        $mageEvent->fireEvent('endTest', [new \Magento\AdobeStockAsset\Test\Integration\Model\AssetRepositoryTest($methodName)], true);
        $mageEvent->fireEvent('endTest', [$assetRepo]);
    }
}
