<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\CronJob;

use \Magento\Sales\Model\CronJob\AggregateSalesReportRefundedData;

/**
 * Tests Magento\Sales\Model\CronJob\AggregateSalesReportRefundedDataTest
 */
class AggregateSalesReportRefundedDataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Locale\ResolverInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $localeResolverMock;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $localeDateMock;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Report\RefundedFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $refundedFactoryMock;

    /**
     * @var \Magento\Sales\Model\CronJob\AggregateSalesReportRefundedData
     */
    protected $observer;

    protected function setUp(): void
    {
        $this->localeResolverMock = $this->getMockBuilder(\Magento\Framework\Locale\ResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->refundedFactoryMock = $this->getMockBuilder(
            \Magento\Sales\Model\ResourceModel\Report\RefundedFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->localeDateMock = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->observer = new AggregateSalesReportRefundedData(
            $this->localeResolverMock,
            $this->localeDateMock,
            $this->refundedFactoryMock
        );
    }

    public function testExecute()
    {
        $date = $this->setupAggregate();
        $refundedMock = $this->getMockBuilder(\Magento\Sales\Model\ResourceModel\Report\Refunded::class)
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
