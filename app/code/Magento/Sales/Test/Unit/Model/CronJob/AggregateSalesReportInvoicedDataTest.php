<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\CronJob;

use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Model\CronJob\AggregateSalesReportInvoicedData;
use Magento\Sales\Model\ResourceModel\Report\Invoiced;
use Magento\Sales\Model\ResourceModel\Report\InvoicedFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests Magento\Sales\Model\CronJob\AggregateSalesReportInvoicedDataTest
 */
class AggregateSalesReportInvoicedDataTest extends TestCase
{
    /**
     * @var ResolverInterface|MockObject
     */
    protected $localeResolverMock;

    /**
     * @var TimezoneInterface|MockObject
     */
    protected $localeDateMock;

    /**
     * @var InvoicedFactory|MockObject
     */
    protected $invoicedFactoryMock;

    /**
     * @var AggregateSalesReportInvoicedData
     */
    protected $observer;

    protected function setUp(): void
    {
        $this->localeResolverMock = $this->getMockBuilder(ResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->invoicedFactoryMock = $this->getMockBuilder(
            InvoicedFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->localeDateMock = $this->getMockBuilder(TimezoneInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->observer = new AggregateSalesReportInvoicedData(
            $this->localeResolverMock,
            $this->localeDateMock,
            $this->invoicedFactoryMock
        );
    }

    public function testExecute()
    {
        $date = $this->setupAggregate();
        $invoicedMock = $this->getMockBuilder(Invoiced::class)
            ->disableOriginalConstructor()
            ->getMock();
        $invoicedMock->expects($this->once())
            ->method('aggregate')
            ->with($date);
        $this->invoicedFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($invoicedMock);
        $this->observer->execute();
    }

    /**
     * Set up aggregate
     *
     * @return \DateTime
     */
    protected function setupAggregate()
    {
        $this->localeResolverMock->expects($this->once())
            ->method('emulate')
            ->with(0);
        $this->localeResolverMock->expects($this->once())
            ->method('revert');

        $date = (new \DateTime())->sub(new \DateInterval('PT25H'));
        $this->localeDateMock->expects($this->once())
            ->method('date')
            ->willReturn($date);

        return $date;
    }
}
