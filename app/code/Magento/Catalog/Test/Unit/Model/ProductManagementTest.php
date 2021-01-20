<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model;

class ProductManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\ProductManagement
     */
    protected $model;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $productsFactoryMock;

    protected function setUp(): void
    {
        $this->productsFactoryMock = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class,
            ['create']
        );
        $this->model = new \Magento\Catalog\Model\ProductManagement(
            $this->productsFactoryMock
        );
    }

    public function testGetEnabledCount()
    {
        $statusEnabled = 1;
        $productsMock = $this->createMock(\Magento\Catalog\Model\ResourceModel\Product\Collection::class);

        $this->productsFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($productsMock);
        $productsMock
            ->expects($this->once())
            ->method('addAttributeToFilter')
            ->with('status', $statusEnabled)
            ->willReturnSelf();
        $productsMock
            ->expects($this->once())
            ->method('getSize')
            ->willReturn('expected');

        $this->assertEquals(
            'expected',
            $this->model->getCount($statusEnabled)
        );
    }

    public function testGetDisabledCount()
    {
        $statusDisabled = 2;
        $productsMock = $this->createMock(\Magento\Catalog\Model\ResourceModel\Product\Collection::class);

        $this->productsFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($productsMock);
        $productsMock
            ->expects($this->once())
            ->method('addAttributeToFilter')
            ->with('status', $statusDisabled)
            ->willReturnSelf();
        $productsMock
            ->expects($this->once())
            ->method('getSize')
            ->willReturn('expected');

        $this->assertEquals(
            'expected',
            $this->model->getCount($statusDisabled)
        );
    }
}
