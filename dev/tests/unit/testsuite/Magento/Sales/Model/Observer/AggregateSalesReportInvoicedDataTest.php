<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Observer;

/**
 * Tests Magento\Sales\Model\Observer\AggregateSalesReportInvoicedDataTest
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
     * @var \Magento\Sales\Model\Resource\Report\InvoicedFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $invoicedFactoryMock;

    /**
     * @var \Magento\Sales\Model\Observer\AggregateSalesReportInvoicedData
     */
    protected $observer;

    protected function setUp()
    {
        $this->localeResolverMock = $this->getMockBuilder('Magento\Framework\Locale\ResolverInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->invoicedFactoryMock = $this->getMockBuilder('Magento\Sales\Model\Resource\Report\InvoicedFactory')
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
        $invoicedMock = $this->getMockBuilder('Magento\Sales\Model\Resource\Report\Invoiced')
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
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface
     */
    protected function setupAggregate()
    {
        $date = $this->getMock('Magento\Framework\Stdlib\DateTime\Date', ['emulate', 'revert'], [], '', false);
        $this->localeResolverMock->expects($this->once())
            ->method('emulate')
            ->with(0);
        $this->localeResolverMock->expects($this->once())
            ->method('revert');
        $dateMock = $this->getMockBuilder('Magento\Framework\Stdlib\DateTime\DateInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $dateMock->expects($this->once())
            ->method('subHour')
            ->with(25)
            ->will($this->returnValue($date));
        $this->localeDateMock->expects($this->once())
            ->method('date')
            ->will($this->returnValue($dateMock));
        return $date;
    }
}
