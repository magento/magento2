<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Event;

use PHPUnit\Event\Test\FinishedSubscriber;
use PHPUnit\Event\Test\Finished;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class TestFinishedSubscriber implements FinishedSubscriber
{
    /**
     * @param ExecutionState $executionState
     */
    public function __construct(private readonly ExecutionState $executionState)
    {
    }

    /**
     * Test finished Subscriber
     *
     * @param Finished $event
     */
    public function notify(Finished $event): void
    {
        $className = $event->test()->className();
        $methodName = $event->test()->methodName();

        $objectManager = Bootstrap::getObjectManager();
        /** @var TestCase $testObj */
        $testObj = $objectManager->create($className, ['name' => $methodName]);
        $phpUnit = $objectManager->create(PhpUnit::class);
        $phpUnit->endTest($testObj, 0);

        $this->executionState->clearTestData($testObj->toString());
        Magento::setCurrentEventObject(null);
        Magento::setTestPrepared(false);
    }
}
