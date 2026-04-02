<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Event;

use PHPUnit\Runner;
use PHPUnit\TextUI;

/**
 * Subscribers of PHPUnit built-in events
 */
class Subscribers implements Runner\Extension\Extension
{
    /**
     * Register Event Subscribers
     *
     * @param TextUI\Configuration\Configuration $configuration
     * @param Runner\Extension\Facade $facade
     * @param Runner\Extension\ParameterCollection $parameters
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function bootstrap(
        TextUI\Configuration\Configuration $configuration,
        Runner\Extension\Facade $facade,
        Runner\Extension\ParameterCollection $parameters
    ): void {
        $executionState = new ExecutionState();
        if ($configuration->hasConfigurationFile() &&
            str_contains($configuration->configurationFile(), 'setup-integration')) {
            $facade->registerSubscribers(
                new TestPreprationStartedSubscriber($executionState),
                new TestFinishedSubscriber($executionState)
            );
        } else {
            $facade->registerSubscribers(
                new TestSuitStartedSubscriber(),
                new TestSuitEndSubscriber(),
                new TestPreparedSubscriber($executionState),
                new TestPreprationStartedSubscriber($executionState),
                new TestFinishedSubscriber($executionState),
                new TestSkippedSubscriber(),
                new TestErroredSubscriber()
            );
        }
    }
}
