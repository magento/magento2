<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\ResourceModel\Address\Attribute\Backend;

use Magento\Customer\Model\ResourceModel\Address\Attribute\Backend\Region;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\DataObject;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

class RegionTest extends TestCase
{
    use MockCreationTrait;

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
        $this->region = $this->createPartialMockWithReflection(
            \Magento\Directory\Model\Region::class,
            [
                'getCountryId',
                'load',
                'getId',
                'getName'
            ]
        );
        $this->model = new Region($this->regionFactory);
        $this->object = $this->createPartialMockWithReflection(
            DataObject::class,
            [
                'getCountryId',
                'setRegionId',
                'setRegion',
                'getData'
            ]
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
