<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Test\Unit\Model;

class StoreManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Store\Model\StoreManagement
     */
    protected $model;

    /**
     * @var \Magento\Store\Model\ResourceModel\Store\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storesFactoryMock;

    protected function setUp()
    {
        $this->storesFactoryMock = $this->getMock(
            'Magento\Store\Model\ResourceModel\Store\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->model = new \Magento\Store\Model\StoreManagement(
            $this->storesFactoryMock
        );
    }

    public function testGetCount()
    {
        $storesMock = $this->getMock('\Magento\Store\Model\ResourceModel\Store\Collection', [], [], '', false);

        $this->storesFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($storesMock);
        $storesMock
            ->expects($this->once())
            ->method('setWithoutDefaultFilter')
            ->willReturnSelf();
        $storesMock
            ->expects($this->once())
            ->method('getSize')
            ->willReturn('expected');

        $this->assertEquals(
            'expected',
            $this->model->getCount()
        );
    }
}
