<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\NewRelicReporting\Test\Unit\Observer;

use Magento\Framework\Event\Observer;
use Magento\NewRelicReporting\Model\NewRelicWrapper;
use Magento\NewRelicReporting\Observer\DisableBrowserMonitoringAutomaticInjection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for DisableBrowserMonitoringAutomaticInjection Observer
 *
 * @covers \Magento\NewRelicReporting\Observer\DisableBrowserMonitoringAutomaticInjection
 */
class DisableBrowserMonitoringAutomaticInjectionTest extends TestCase
{
    /**
     * @var DisableBrowserMonitoringAutomaticInjection
     */
    private DisableBrowserMonitoringAutomaticInjection $observer;

    /**
     * @var NewRelicWrapper|\PHPUnit\Framework\MockObject\MockObject
     */
    private $newRelicWrapperMock;

    /**
     * @var Observer|\PHPUnit\Framework\MockObject\MockObject
     */
    private $observerMock;

    /**
     * Set up test dependencies
     */
    protected function setUp(): void
    {
        $this->newRelicWrapperMock = $this->createMock(NewRelicWrapper::class);
        $this->observerMock = $this->createMock(Observer::class);

        /** @var NewRelicWrapper $newRelicWrapper */
        $newRelicWrapper = $this->newRelicWrapperMock;
        $this->observer = new DisableBrowserMonitoringAutomaticInjection(
            $newRelicWrapper
        );
    }

    /**
     * Test that disableAutorum is called when auto instrument is enabled
     */
    public function testExecuteDisablesAutoRumWhenAutoInstrumentEnabled(): void
    {
        // Mock: Auto instrument is enabled
        $this->newRelicWrapperMock->expects($this->once())
            ->method('isAutoInstrumentEnabled')
            ->willReturn(true);

        // Mock: disableAutorum should be called once
        $this->newRelicWrapperMock->expects($this->once())
            ->method('disableAutorum');

        // Execute the observer
        $this->observer->execute($this->observerMock);
    }

    /**
     * Test that disableAutorum is NOT called when auto instrument is disabled
     */
    public function testExecuteDoesNotDisableAutoRumWhenAutoInstrumentDisabled(): void
    {
        // Mock: Auto instrument is disabled
        $this->newRelicWrapperMock->expects($this->once())
            ->method('isAutoInstrumentEnabled')
            ->willReturn(false);

        // Mock: disableAutorum should NOT be called
        $this->newRelicWrapperMock->expects($this->never())
            ->method('disableAutorum');

        // Execute the observer
        $this->observer->execute($this->observerMock);
    }

    /**
     * Test that the observer implements ObserverInterface
     */
    public function testImplementsObserverInterface(): void
    {
        $this->assertInstanceOf(
            \Magento\Framework\Event\ObserverInterface::class,
            $this->observer
        );
    }

    /**
     * Test that the observer handles execution without exceptions
     */
    public function testExecuteHandlesExecutionGracefully(): void
    {
        // Mock: Auto instrument is enabled
        $this->newRelicWrapperMock->method('isAutoInstrumentEnabled')
            ->willReturn(true);

        // Mock: disableAutorum returns expected value
        $this->newRelicWrapperMock->method('disableAutorum')
            ->willReturn(true);

        // Should not throw any exceptions
        $this->expectNotToPerformAssertions();
        $this->observer->execute($this->observerMock);
    }

    /**
     * Test observer behavior when NewRelicWrapper methods return null
     */
    public function testExecuteHandlesNullReturnValues(): void
    {
        // Mock: Auto instrument check returns false (extension not installed)
        $this->newRelicWrapperMock->expects($this->once())
            ->method('isAutoInstrumentEnabled')
            ->willReturn(false);

        // disableAutorum should not be called
        $this->newRelicWrapperMock->expects($this->never())
            ->method('disableAutorum');

        // Should execute without issues
        $this->observer->execute($this->observerMock);
    }

    /**
     * Test that constructor properly injects dependencies
     */
    public function testConstructorInjectsDependencies(): void
    {
        /** @var NewRelicWrapper|\PHPUnit\Framework\MockObject\MockObject $newRelicWrapper */
        $newRelicWrapper = $this->createMock(NewRelicWrapper::class);
        $observer = new DisableBrowserMonitoringAutomaticInjection($newRelicWrapper);

        $this->assertInstanceOf(
            DisableBrowserMonitoringAutomaticInjection::class,
            $observer
        );
    }

    /**
     * Test the complete flow with different scenarios
     */
    #[DataProvider('executeScenarioProvider')]
    public function testExecuteScenarios(bool $isAutoInstrumentEnabled, int $disableAutoRumCallCount): void
    {
        $this->newRelicWrapperMock->expects($this->once())
            ->method('isAutoInstrumentEnabled')
            ->willReturn($isAutoInstrumentEnabled);

        $this->newRelicWrapperMock->expects($this->exactly($disableAutoRumCallCount))
            ->method('disableAutorum');

        $this->observer->execute($this->observerMock);
    }

    /**
     * Data provider for execute scenarios
     */
    public static function executeScenarioProvider(): array
    {
        return [
            'auto_instrument_enabled' => [
                'isAutoInstrumentEnabled' => true,
                'disableAutoRumCallCount' => 1
            ],
            'auto_instrument_disabled' => [
                'isAutoInstrumentEnabled' => false,
                'disableAutoRumCallCount' => 0
            ]
        ];
    }

    /**
     * Test that the observer method signature matches the interface
     */
    public function testExecuteMethodSignature(): void
    {
        $reflection = new \ReflectionClass(DisableBrowserMonitoringAutomaticInjection::class);
        $executeMethod = $reflection->getMethod('execute');

        $this->assertTrue($executeMethod->isPublic());
        $this->assertEquals('execute', $executeMethod->getName());

        $parameters = $executeMethod->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('observer', $parameters[0]->getName());
    }
}
