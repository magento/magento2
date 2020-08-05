<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model;

use Magento\Catalog\Api\Data\ProductTypeInterface;
use Magento\Catalog\Api\Data\ProductTypeInterfaceFactory;
use Magento\Catalog\Model\ProductTypeList;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductTypeListTest extends TestCase
{
    /**
     * @var ProductTypeList
     */
    private $model;

    /**
     * @var MockObject
     */
    private $typeConfigMock;

    /**
     * @var MockObject
     */
    private $factoryMock;

    protected function setUp(): void
    {
        $this->typeConfigMock = $this->getMockForAbstractClass(ConfigInterface::class);
        $this->factoryMock = $this->createPartialMock(
            ProductTypeInterfaceFactory::class,
            ['create']
        );
        $this->model = new ProductTypeList(
            $this->typeConfigMock,
            $this->factoryMock
        );
    }

    public function testGetProductTypes()
    {
        $simpleProductType = [
            'name' => 'simple',
            'label' => 'Simple Product',
        ];
        $productTypeData = [
            'simple' => $simpleProductType,
        ];
        $productTypeMock = $this->getMockForAbstractClass(ProductTypeInterface::class);
        $this->typeConfigMock->expects($this->any())->method('getAll')->willReturn($productTypeData);

        $this->factoryMock->expects($this->once())->method('create')->willReturn($productTypeMock);
        $productTypeMock->expects($this->once())
            ->method('setName')
            ->with($simpleProductType['name'])
            ->willReturnSelf();
        $productTypeMock->expects($this->once())
            ->method('setLabel')
            ->with($simpleProductType['label'])
            ->willReturnSelf();
        $productTypes = $this->model->getProductTypes();
        $this->assertCount(1, $productTypes);
        $this->assertContains($productTypeMock, $productTypes);
    }
}
