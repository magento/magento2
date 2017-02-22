<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\CronJob;

use \Magento\Sales\Model\CronJob\AggregateSalesReportBestsellersData;

/**
 * Tests Magento\Sales\Model\CronJob\AggregateSalesReportBestsellersDataTest
 */
class AggregateSalesReportBestsellersDataTest extends \PHPUnit_Framework_TestCase
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
     * @var \Magento\Sales\Model\ResourceModel\Report\BestsellersFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bestsellersFactoryMock;

    /**
     * @var \Magento\Sales\Model\CronJob\AggregateSalesReportBestsellersData
     */
    protected $observer;

    protected function setUp()
    {
        $this->localeResolverMock = $this->getMockBuilder('Magento\Framework\Locale\ResolverInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->bestsellersFactoryMock =
            $this->getMockBuilder('Magento\Sales\Model\ResourceModel\Report\BestsellersFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->localeDateMock = $this->getMockBuilder('Magento\Framework\Stdlib\DateTime\TimezoneInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->observer = new AggregateSalesReportBestsellersData(
            $this->localeResolverMock,
            $this->localeDateMock,
            $this->bestsellersFactoryMock
        );
    }

    public function testExecute()
    {
        $date = $this->setupAggregate();
        $bestsellersMock = $this->getMockBuilder('Magento\Sales\Model\ResourceModel\Report\Bestsellers')
            ->disableOriginalConstructor()
            ->getMock();
        $bestsellersMock->expects($this->once())
            ->method('aggregate')
            ->with($date);
        $this->bestsellersFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($bestsellersMock));
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
