<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Listener of PHPUnit built-in events
 */
namespace Magento\TestFramework\Event;

use PHPUnit\Runner;
use PHPUnit\TextUI;

final class PhpUnitExtension implements Runner\Extension\Extension
{
    public function bootstrap(
        TextUI\Configuration\Configuration $configuration,
        Runner\Extension\Facade $facade,
        Runner\Extension\ParameterCollection $parameters
    ): void
    {
        $facade->registerSubscribers(
            new TestSuitStartedSubscriber(),
            new TestSuitEndSubscriber(),
            new PhpUnitExtensionSubscriber(),
            new PhpUnitExtensionSubscriberFinished()
        );
    }
}
