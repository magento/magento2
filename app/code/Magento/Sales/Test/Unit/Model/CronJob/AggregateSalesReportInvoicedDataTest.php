<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\CronJob;

use \Magento\Sales\Model\CronJob\AggregateSalesReportInvoicedData;

/**
 * Tests Magento\Sales\Model\CronJob\AggregateSalesReportInvoicedDataTest
 */
class AggregateSalesReportInvoicedDataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Locale\ResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeResolverMock;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeDateMock;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Report\InvoicedFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $invoicedFactoryMock;

    /**
     * @var \Magento\Sales\Model\CronJob\AggregateSalesReportInvoicedData
     */
    protected $observer;

    protected function setUp()
    {
        $this->localeResolverMock = $this->getMockBuilder('Magento\Framework\Locale\ResolverInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->invoicedFactoryMock = $this->getMockBuilder('Magento\Sales\Model\ResourceModel\Report\InvoicedFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->localeDateMock = $this->getMockBuilder('Magento\Framework\Stdlib\DateTime\TimezoneInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->observer = new AggregateSalesReportInvoicedData(
            $this->localeResolverMock,
            $this->localeDateMock,
            $this->invoicedFactoryMock
        );
    }

    public function testExecute()
    {
        $date = $this->setupAggregate();
        $invoicedMock = $this->getMockBuilder('Magento\Sales\Model\ResourceModel\Report\Invoiced')
            ->disableOriginalConstructor()
            ->getMock();
        $invoicedMock->expects($this->once())
            ->method('aggregate')
            ->with($date);
        $this->invoicedFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($invoicedMock));
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
            ->will($this->returnValue($date));

        return $date;
    }
}
