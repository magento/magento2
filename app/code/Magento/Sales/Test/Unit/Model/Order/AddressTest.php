<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Model\Order;

use \Magento\Sales\Model\Order\Payment;

/**
 * Class PaymentTest
 *
 * @package Magento\Sales\Model\Order
 */
class AddressTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Order\Address
     */
    protected $address;

    /**
     * @var \Magento\Sales\Model\Order|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderMock;

    /**
     * @var \Magento\Directory\Model\RegionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $regionFactoryMock;

    /**
     * @var \Magento\Directory\Model\Region|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $regionMock;

    protected function setUp()
    {
        $this->orderMock = $this->getMock(
            \Magento\Sales\Model\Order::class,
            [],
            [],
            '',
            false
        );
        $this->orderMock = $this->getMock(
            \Magento\Sales\Model\Order::class,
            [],
            [],
            '',
            false
        );
        $this->regionFactoryMock = $this->getMock(
            \Magento\Directory\Model\RegionFactory::class,
            [],
            [],
            '',
            false
        );
        $this->regionMock = $this->getMock(
            \Magento\Directory\Model\Region::class,
            ['load', 'getCountryId', 'getCode'],
            [],
            '',
            false
        );
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->address = $objectManager->getObject(
            \Magento\Sales\Model\Order\Address::class,
            [
                'regionFactory' => $this->regionFactoryMock
            ]
        );
    }

    public function testSetOrder()
    {
        $this->assertEquals($this->address, $this->address->setOrder($this->orderMock));
    }

    public function testGetRegionCodeRegionIsSet()
    {
        $regionId = 1;
        $this->address->setData('region', 'region');
        $this->address->setData('region_id', $regionId);
        $this->address->setData('country_id', 2);

        $this->regionMock->expects(static::once())
            ->method('load')
            ->with($regionId)
            ->willReturnSelf();

        $this->regionMock->expects(static::once())
            ->method('getCountryId')
            ->willReturn(1);

        $this->regionFactoryMock->expects(static::once())
            ->method('create')
            ->willReturn($this->regionMock);
        $this->assertEquals('region', $this->address->getRegionCode());
    }

    public function regionProvider()
    {
        return [ [1, null], [null, 1]];
    }

    /**
     * @dataProvider regionProvider
     */
    public function testGetRegionCodeRegion($region, $regionId)
    {
        $this->address->setData('region', $region);
        $this->address->setData('region_id', $regionId);
        $this->address->setData('country_id', 1);
        $this->regionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->regionMock);
        $this->regionMock->expects($this->once())
            ->method('load')
            ->with(1)
            ->willReturn($this->regionMock);
        $this->regionMock->expects($this->once())
            ->method('getCountryId')
            ->willReturn(1);
        $this->regionMock->expects($this->once())
            ->method('getCode')
            ->willReturn('region');
        $this->assertEquals('region', $this->address->getRegionCode());
    }

    public function testGetRegionCodeRegionFailure()
    {
        $this->address->setData('region', 1);
        $this->address->setData('region_id', 1);
        $this->address->setData('country_id', 1);
        $this->regionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->regionMock);
        $this->regionMock->expects($this->once())
            ->method('load')
            ->with(1)
            ->willReturn($this->regionMock);
        $this->regionMock->expects($this->once())
            ->method('getCountryId')
            ->willReturn(2);
        $this->regionMock->expects($this->never())
            ->method('getCode');
        $this->assertEquals(null, $this->address->getRegionCode());
    }

    public function testGetName()
    {
        $this->address->setData('suffix', 'suffix');
        $this->address->setData('prefix', 'prefix');
        $this->address->setData('firstname', 'firstname');
        $this->address->setData('middlename', 'middlename');
        $this->address->setData('lastname', 'lastname');
        $this->assertEquals('prefix firstname middlename lastname suffix', $this->address->getName());
    }
}
