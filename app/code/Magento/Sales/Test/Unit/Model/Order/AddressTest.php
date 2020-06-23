<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order;

use Magento\Directory\Model\Region;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AddressTest extends TestCase
{
    /**
     * @var Address
     */
    protected $address;

    /**
     * @var Order|MockObject
     */
    protected $orderMock;

    /**
     * @var RegionFactory|MockObject
     */
    protected $regionFactoryMock;

    /**
     * @var Region|MockObject
     */
    protected $regionMock;

    protected function setUp(): void
    {
        $this->orderMock = $this->createMock(Order::class);
        $this->orderMock = $this->createMock(Order::class);
        $this->regionFactoryMock = $this->createMock(RegionFactory::class);
        $this->regionMock = $this->getMockBuilder(Region::class)
            ->addMethods(['getCountryId', 'getCode'])
            ->onlyMethods(['load'])
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager = new ObjectManager($this);
        $this->address = $objectManager->getObject(
            Address::class,
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

    /**
     * @return array
     */
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
        $this->assertNull($this->address->getRegionCode());
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
