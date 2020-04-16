<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model;

use Magento\Catalog\Model\ProductManagement;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductManagementTest extends TestCase
{
    /**
     * @var ProductManagement
     */
    protected $model;

    /**
     * @var CollectionFactory|MockObject
     */
    protected $productsFactoryMock;

    protected function setUp(): void
    {
        $this->productsFactoryMock = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $this->model = new ProductManagement(
            $this->productsFactoryMock
        );
    }

    public function testGetEnabledCount()
    {
        $statusEnabled = 1;
        $productsMock = $this->createMock(Collection::class);

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
        $productsMock = $this->createMock(Collection::class);

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
