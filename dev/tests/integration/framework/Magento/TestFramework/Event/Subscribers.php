<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */

/**
 * Subscribers of PHPUnit built-in events
 */
namespace Magento\TestFramework\Event;

use PHPUnit\Runner;
use PHPUnit\TextUI;

class Subscribers implements Runner\Extension\Extension
{
    /**
     * Register Event Subscribers
     *
     * @param TextUI\Configuration\Configuration $configuration
     * @param Runner\Extension\Facade $facade
     * @param Runner\Extension\ParameterCollection $parameters
     */
    public function bootstrap(
        TextUI\Configuration\Configuration $configuration,
        Runner\Extension\Facade $facade,
        Runner\Extension\ParameterCollection $parameters
    ): void {
        if ($configuration->hasConfigurationFile() &&
            str_contains($configuration->configurationFile(), 'setup-integration')) {
            $facade->registerSubscribers(
                new TestPreprationStartedSubscriber(),
                new TestFinishedSubscriber()
            );
        } else {
            $facade->registerSubscribers(
                new TestSuitStartedSubscriber(),
                new TestSuitEndSubscriber(),
                new TestPreparedSubscriber(),
                new TestPreprationStartedSubscriber(),
                new TestFinishedSubscriber(),
                new TestSkippedSubscriber(),
                new TestErroredSubscriber()
            );
        }
    }
}
