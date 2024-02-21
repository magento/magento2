<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Listener of PHPUnit built-in events
 */
namespace Magento\TestFramework\Event;

use Magento\Framework\Event;
use PHPUnit\Runner;
use PHPUnit\TextUI;

final class PhpUnitExtensionFinished implements Runner\Extension\Extension
{
    public function bootstrap(
        TextUI\Configuration\Configuration $configuration,
        Runner\Extension\Facade $facade,
        Runner\Extension\ParameterCollection $parameters
    ): void
    {
        $facade->registerSubscriber(new PhpUnitExtensionSubsscriberFinished());
    }
}
