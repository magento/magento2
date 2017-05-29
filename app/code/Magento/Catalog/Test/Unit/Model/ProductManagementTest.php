<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model;

class ProductManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\ProductManagement
     */
    protected $model;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productsFactoryMock;

    protected function setUp()
    {
        $this->productsFactoryMock = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->model = new \Magento\Catalog\Model\ProductManagement(
            $this->productsFactoryMock
        );
    }

    public function testGetEnabledCount()
    {
        $statusEnabled = 1;
        $productsMock = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Product\Collection::class,
            [],
            [],
            '',
            false
        );

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
        $productsMock = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Product\Collection::class,
            [],
            [],
            '',
            false
        );

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
