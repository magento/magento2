<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Test\Unit\Model;

class StoreManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Store\Model\StoreManagement
     */
    protected $model;

    /**
     * @var \Magento\Store\Model\ResourceModel\Store\CollectionFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $storesFactoryMock;

    protected function setUp(): void
    {
        $this->storesFactoryMock = $this->createPartialMock(
            \Magento\Store\Model\ResourceModel\Store\CollectionFactory::class,
            ['create']
        );
        $this->model = new \Magento\Store\Model\StoreManagement(
            $this->storesFactoryMock
        );
    }

    public function testGetCount()
    {
        $storesMock = $this->createMock(\Magento\Store\Model\ResourceModel\Store\Collection::class);

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
