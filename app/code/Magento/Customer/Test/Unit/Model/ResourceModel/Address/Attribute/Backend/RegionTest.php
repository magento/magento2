<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\ResourceModel\Address\Attribute\Backend;

use Magento\Customer\Model\ResourceModel\Address\Attribute\Backend\Region;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\DataObject;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RegionTest extends TestCase
{
    /** @var RegionFactory|MockObject */
    protected $regionFactory;

    /** @var Region */
    protected $model;

    /** @var DataObject|MockObject */
    protected $object;

    /** @var \Magento\Directory\Model\Region|MockObject */
    protected $region;

    protected function setUp(): void
    {
        $this->regionFactory = $this->createPartialMock(RegionFactory::class, ['create']);
        $this->region = $this->getMockBuilder(\Magento\Directory\Model\Region::class)->addMethods(['getCountryId'])
            ->onlyMethods(['load', 'getId', 'getName'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = new Region($this->regionFactory);
        $this->object = $this->getMockBuilder(DataObject::class)
            ->addMethods(['getCountryId', 'setRegionId', 'setRegion'])
            ->onlyMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testBeforeSave()
    {
        $regionId = '23';
        $countryId = '67';
        $this->object->expects($this->once())
            ->method('getData')
            ->with('region')
            ->willReturn($regionId);
        $this->object->expects($this->once())
            ->method('getCountryId')
            ->willReturn($countryId);
        $this->regionFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->region);
        $this->region->expects($this->once())
            ->method('load')
            ->with($regionId)
            ->willReturnSelf();
        $this->region->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($regionId);
        $this->region->expects($this->once())
            ->method('getCountryId')
            ->willReturn($countryId);
        $this->object->expects($this->once())
            ->method('setRegionId')
            ->with($regionId)
            ->willReturnSelf();
        $this->region->expects($this->once())
            ->method('getName')
            ->willReturn('Region name');
        $this->object->expects($this->once())
            ->method('setRegion')
            ->with('Region name');

        $this->model->beforeSave($this->object);
    }
}
