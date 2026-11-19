<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */

/**
 * Test Errored Subscriber
 */
namespace Magento\TestFramework\Event;

use PHPUnit\Event\Test\ErroredSubscriber;
use PHPUnit\Event\Test\Errored;
use Magento\TestFramework\Helper\Bootstrap;

class TestErroredSubscriber implements ErroredSubscriber
{
    /**
     * Errored Subscriber
     *
     * @param Errored $event
     */
    public function notify(Errored $event): void
    {
        $className = $event->test()->className();
        $methodName = $event->test()->methodName();
        $objectManager = Bootstrap::getObjectManager();

        $testObj = $objectManager->create($className, ['name' => $methodName]);
        $phpUnit = $objectManager->create(PhpUnit::class);
        try {
            $phpUnit->endTest($testObj, 0);
        } catch (\Throwable $exception) {
            // A throwable leaving this subscriber stops the Errored event before PHPUnit records the test error,
            // and the run ends as OK. Keep the test error and report the failed fixture revert next to it.
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            fwrite(
                STDERR,
                PHP_EOL . $event->test()->id() . ': ' . get_class($exception) . ': '
                . $exception->getMessage() . PHP_EOL
            );
        }

        Magento::setCurrentEventObject(null);
        Magento::setTestPrepared(false);
    }
}
