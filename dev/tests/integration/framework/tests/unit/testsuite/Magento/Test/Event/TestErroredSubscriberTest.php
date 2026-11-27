<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Test\Event;

use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Event\Magento;
use Magento\TestFramework\Event\PhpUnit;
use Magento\TestFramework\Event\TestErroredSubscriber;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Event\Code\TestMethodBuilder;
use PHPUnit\Event\Code\ThrowableBuilder;
use PHPUnit\Event\Telemetry\Duration;
use PHPUnit\Event\Telemetry\Info;
use PHPUnit\Event\Telemetry\MemoryUsage;
use PHPUnit\Event\Telemetry\System;
use PHPUnit\Event\Telemetry\SystemGarbageCollectorStatusProvider;
use PHPUnit\Event\Telemetry\SystemMemoryMeter;
use PHPUnit\Event\Telemetry\SystemStopWatch;
use PHPUnit\Event\Test\Errored;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TestErroredSubscriberTest extends TestCase
{
    /**
     * @var ObjectManagerInterface|null
     */
    private $originalObjectManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->originalObjectManager = Bootstrap::getObjectManager();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $property = new \ReflectionProperty(Bootstrap::class, '_objectManager');
        $property->setValue(null, $this->originalObjectManager);
        Magento::setTestPrepared(false);
    }

    /**
     * A failed fixture revert must not escape the Errored event, or PHPUnit loses the test error
     *
     * @return void
     */
    public function testFailedFixtureRevertDoesNotEscapeErroredEvent(): void
    {
        $phpUnit = $this->createMock(PhpUnit::class);
        $phpUnit->expects($this->once())
            ->method('endTest')
            ->willThrowException(new AssertionFailedError('Unable to revert fixture: rollback.php'));
        $objectManager = $this->createMock(ObjectManagerInterface::class);
        $objectManager->method('create')->willReturnCallback(
            fn (string $type) => $type === PhpUnit::class ? $phpUnit : $this
        );
        Bootstrap::setObjectManager($objectManager);
        Magento::setTestPrepared(true);

        (new TestErroredSubscriber())->notify($this->createErroredEvent());

        $this->assertFalse(Magento::getTestPrepared());
    }

    /**
     * Create the Errored event PHPUnit emits for this test
     *
     * @return Errored
     */
    private function createErroredEvent(): Errored
    {
        $snapshot = (new System(
            new SystemStopWatch(),
            new SystemMemoryMeter(),
            new SystemGarbageCollectorStatusProvider()
        ))->snapshot();
        $info = new Info(
            $snapshot,
            Duration::fromSecondsAndNanoseconds(0, 0),
            MemoryUsage::fromBytes(0),
            Duration::fromSecondsAndNanoseconds(0, 0),
            MemoryUsage::fromBytes(0)
        );

        return new Errored(
            $info,
            TestMethodBuilder::fromTestCase($this),
            ThrowableBuilder::from(new \RuntimeException('body error'))
        );
    }
}
