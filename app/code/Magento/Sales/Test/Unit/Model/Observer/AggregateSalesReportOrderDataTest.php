<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Observer;

use \Magento\Sales\Model\Observer\AggregateSalesReportOrderData;

/**
 * Tests Magento\Sales\Model\Observer\AggregateSalesReportOrderDataTest
 */
class AggregateSalesReportOrderDataTest extends \PHPUnit_Framework_TestCase
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
     * @var \Magento\Sales\Model\Resource\Report\OrderFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderFactoryMock;

    /**
     * @var \Magento\Sales\Model\Observer\AggregateSalesReportOrderData
     */
    protected $observer;

    protected function setUp()
    {
        $this->localeResolverMock = $this->getMockBuilder('Magento\Framework\Locale\ResolverInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderFactoryMock = $this->getMockBuilder('Magento\Sales\Model\Resource\Report\OrderFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->localeDateMock = $this->getMockBuilder('Magento\Framework\Stdlib\DateTime\TimezoneInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->observer = new AggregateSalesReportOrderData(
            $this->localeResolverMock,
            $this->localeDateMock,
            $this->orderFactoryMock
        );
    }

    public function testExecute()
    {
        $date = $this->setupAggregate();
        $orderMock = $this->getMockBuilder('Magento\Sales\Model\Resource\Report\Order')
            ->disableOriginalConstructor()
            ->getMock();
        $orderMock->expects($this->once())
            ->method('aggregate')
            ->with($date);
        $this->orderFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($orderMock));
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
