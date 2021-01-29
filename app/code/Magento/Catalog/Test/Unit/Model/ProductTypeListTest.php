<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model;

use Magento\Catalog\Model\ProductTypeList;

class ProductTypeListTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ProductTypeList
     */
    private $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $typeConfigMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $factoryMock;

    protected function setUp(): void
    {
        $this->typeConfigMock = $this->createMock(\Magento\Catalog\Model\ProductTypes\ConfigInterface::class);
        $this->factoryMock = $this->createPartialMock(
            \Magento\Catalog\Api\Data\ProductTypeInterfaceFactory::class,
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
        $productTypeMock = $this->createMock(\Magento\Catalog\Api\Data\ProductTypeInterface::class);
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
