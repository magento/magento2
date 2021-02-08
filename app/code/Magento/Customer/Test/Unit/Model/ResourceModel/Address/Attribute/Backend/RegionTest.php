<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Unit\Model\ResourceModel\Address\Attribute\Backend;

use Magento\Customer\Model\ResourceModel\Address\Attribute\Backend\Region;

class RegionTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Directory\Model\RegionFactory|\PHPUnit\Framework\MockObject\MockObject */
    protected $regionFactory;

    /** @var Region */
    protected $model;

    /** @var \Magento\Framework\DataObject|\PHPUnit\Framework\MockObject\MockObject */
    protected $object;

    /** @var \Magento\Directory\Model\Region|\PHPUnit\Framework\MockObject\MockObject */
    protected $region;

    protected function setUp(): void
    {
        $this->regionFactory = $this->createPartialMock(\Magento\Directory\Model\RegionFactory::class, ['create']);
        $this->region = $this->createPartialMock(
            \Magento\Directory\Model\Region::class,
            ['load', 'getId', 'getCountryId', 'getName']
        );
        $this->model = new Region($this->regionFactory);
        $this->object = $this->createPartialMock(
            \Magento\Framework\DataObject::class,
            ['getData', 'getCountryId', 'setRegionId', 'setRegion']
        );
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
