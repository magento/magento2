<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model;

use \Magento\Catalog\Model\ProductTypeList;

class ProductTypeListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductTypeList
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $typeConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $factoryMock;

    protected function setUp()
    {
        $this->typeConfigMock = $this->getMock(\Magento\Catalog\Model\ProductTypes\ConfigInterface::class);
        $this->factoryMock = $this->getMock(
            \Magento\Catalog\Api\Data\ProductTypeInterfaceFactory::class,
            ['create'],
            [],
            '',
            false
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
        $productTypeMock = $this->getMock(\Magento\Catalog\Api\Data\ProductTypeInterface::class);
        $this->typeConfigMock->expects($this->any())->method('getAll')->will($this->returnValue($productTypeData));

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
