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

final class PhpUnitExtensionSubsscriber implements PreparationStartedSubscriber
{
    public function notify(\PHPUnit\Event\Test\PreparationStarted $event): void{
        $subscribers[] = new \Magento\TestFramework\Event\Transaction(
            new \Magento\TestFramework\EventManager(
                [
                    new \Magento\TestFramework\Annotation\DbIsolation(),
                    new \Magento\TestFramework\Annotation\DataFixture(),
                ]
            )
        );
        $eventManager = new \Magento\TestFramework\EventManager($subscribers);
        $eventManager->fireEvent('startTest', [new \Magento\AdobeStockAsset\Test\Integration\Model\AssetRepositoryTest($methodName)]);
    }
}
