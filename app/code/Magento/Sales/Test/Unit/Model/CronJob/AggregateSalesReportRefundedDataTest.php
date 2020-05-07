<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\CronJob;

use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Model\CronJob\AggregateSalesReportRefundedData;
use Magento\Sales\Model\ResourceModel\Report\Refunded;
use Magento\Sales\Model\ResourceModel\Report\RefundedFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests Magento\Sales\Model\CronJob\AggregateSalesReportRefundedDataTest
 */
class AggregateSalesReportRefundedDataTest extends TestCase
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
     * @var RefundedFactory|MockObject
     */
    protected $refundedFactoryMock;

    /**
     * @var AggregateSalesReportRefundedData
     */
    protected $observer;

    protected function setUp(): void
    {
        $this->localeResolverMock = $this->getMockBuilder(ResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->refundedFactoryMock = $this->getMockBuilder(
            RefundedFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->localeDateMock = $this->getMockBuilder(TimezoneInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->observer = new AggregateSalesReportRefundedData(
            $this->localeResolverMock,
            $this->localeDateMock,
            $this->refundedFactoryMock
        );
    }

    public function testExecute()
    {
        $date = $this->setupAggregate();
        $refundedMock = $this->getMockBuilder(Refunded::class)
            ->disableOriginalConstructor()
            ->getMock();
        $refundedMock->expects($this->once())
            ->method('aggregate')
            ->with($date);
        $this->refundedFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($refundedMock);
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
