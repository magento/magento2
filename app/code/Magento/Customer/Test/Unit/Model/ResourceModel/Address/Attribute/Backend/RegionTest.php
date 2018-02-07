<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Unit\Model\ResourceModel\Address\Attribute\Backend;

use Magento\Customer\Model\ResourceModel\Address\Attribute\Backend\Region;

class RegionTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Directory\Model\RegionFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $regionFactory;

    /** @var Region */
    protected $model;

    /** @var \Magento\Framework\DataObject|\PHPUnit_Framework_MockObject_MockObject */
    protected $object;

    /** @var \Magento\Directory\Model\Region|\PHPUnit_Framework_MockObject_MockObject */
    protected $region;

    protected function setUp()
    {
        $this->regionFactory = $this->getMock('Magento\Directory\Model\RegionFactory', ['create'], [], '', false);
        $this->region = $this->getMock(
            'Magento\Directory\Model\Region',
            ['load', 'getId', 'getCountryId', 'getName'],
            [],
            '',
            false
        );
        $this->model = new Region($this->regionFactory);
        $this->object = $this->getMock(
            'Magento\Framework\DataObject',
            ['getData', 'getCountryId', 'setRegionId', 'setRegion'],
            [],
            '',
            false
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
