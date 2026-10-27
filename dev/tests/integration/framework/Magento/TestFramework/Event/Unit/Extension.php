<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\TestFramework\Event\Unit;

use Magento\TestFramework\Event\Magento;
use PHPUnit\Event\Test\Errored;
use PHPUnit\Event\Test\ErroredSubscriber;
use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\FinishedSubscriber;
use PHPUnit\Event\Test\PreparationStarted;
use PHPUnit\Event\Test\PreparationStartedSubscriber;
use PHPUnit\Event\Test\Skipped;
use PHPUnit\Event\Test\SkippedSubscriber;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

/**
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 */
class Extension implements \PHPUnit\Runner\Extension\Extension
{
    /**
     * @inheritDoc
     */
    public function bootstrap(
        Configuration $configuration,
        Facade $facade,
        ParameterCollection $parameters
    ): void {
        $facade->registerSubscribers(
            new class implements PreparationStartedSubscriber {
                /**
                 * @inheritDoc
                 */
                public function notify(PreparationStarted $event): void
                {
                    Magento::setCurrentEventObject($event);
                }
            },
            new class implements FinishedSubscriber {
                /**
                 * @inheritDoc
                 */
                public function notify(Finished $event): void
                {
                    Magento::setCurrentEventObject(null);
                }
            },
            new class implements ErroredSubscriber {
                /**
                 * @inheritDoc
                 */
                public function notify(Errored $event): void
                {
                    Magento::setCurrentEventObject(null);
                }
            },
            new class implements SkippedSubscriber {
                /**
                 * @inheritDoc
                 */
                public function notify(Skipped $event): void
                {
                    Magento::setCurrentEventObject(null);
                }
            }
        );
    }
}
