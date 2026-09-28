<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\NewRelicReporting\Test\Unit\Model\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\NewRelicReporting\Model\Config;
use Magento\NewRelicReporting\Model\NewRelicWrapper;
use Magento\NewRelicReporting\Model\Observer\ReportApplicationHandledExceptionToNewRelic;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test coverage for \Magento\NewRelicReporting\Model\Observer\ReportApplicationHandledExceptionToNewRelic
 */
class ReportApplicationHandledExceptionToNewRelicTest extends TestCase
{

    /**
     * @var ReportApplicationHandledExceptionToNewRelic
     */
    protected $observer;
    /**
     * @var Config|MockObject
     */
    protected $configMock;

    /**
     * @var NewRelicWrapper|MockObject
     */
    protected $newRelicWrapperMock;
    /**
     * @return void
     * @throws Exception
     */
    public function setUp(): void
    {
        $this->newRelicWrapperMock = $this->createMock(NewRelicWrapper::class);
        $this->configMock = $this->createMock(Config::class);
        $this->observer = new ReportApplicationHandledExceptionToNewRelic(
            $this->configMock,
            $this->newRelicWrapperMock
        );
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testExecuteReportsErrorWhenEnabled(): void
    {
        $this->configMock->method('isNewRelicEnabled')->willReturn(true);

        $exception = new \Exception('Test exception');

        // Use a DataObject to simulate the Event object
        $eventMock = new DataObject(['exception' => $exception]);

        $observerMock = $this->createMock(Observer::class);
        $observerMock->method('getEvent')->willReturn($eventMock);

        $this->newRelicWrapperMock
            ->expects($this->once())
            ->method('reportError')
            ->with($exception);

        $this->observer->execute($observerMock);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testExecuteDoesNothingWhenDisabled(): void
    {
        $this->configMock->method('isNewRelicEnabled')->willReturn(false);

        $exception = new \Exception('Test exception');

        $eventMock = new DataObject(['exception' => $exception]);

        $observerMock = $this->createMock(Observer::class);
        $observerMock->method('getEvent')->willReturn($eventMock);

        // Expect NewRelicWrapper::reportError to never be called
        $this->newRelicWrapperMock
            ->expects($this->never())
            ->method('reportError');

        $this->observer->execute($observerMock);
    }
}
