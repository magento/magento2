<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\ConfigurableProduct\Model\ConfigurableProductManagement;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute;
use Magento\ConfigurableProduct\Model\ProductVariationsBuilder;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\Collection;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\CollectionFactory;
use Magento\Eav\Model\Entity\Attribute\Option;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigurableProductManagementTest extends TestCase
{
    /**
     * @var ConfigurableProductManagement
     */
    protected $model;

    /**
     * @var ProductAttributeRepositoryInterface|MockObject
     */
    protected $attributeRepository;

    /**
     * @var ProductVariationsBuilder|MockObject
     */
    protected $productVariationBuilder;

    /**
     * @var ProductInterface
     */
    protected $product;

    /**
     * @var MockObject
     */
    protected $option;

    /**
     * @var CollectionFactory|MockObject
     */
    protected $productsFactoryMock;

    protected function setUp(): void
    {
        $this->attributeRepository = $this->getMockForAbstractClass(ProductAttributeRepositoryInterface::class);
        $this->product = $this->getMockForAbstractClass(ProductInterface::class);
        $this->option = $this->createMock(
            Attribute::class
        );
        $this->productVariationBuilder = $this->createMock(
            ProductVariationsBuilder::class
        );
        $this->productsFactoryMock = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );

        $this->model = new ConfigurableProductManagement(
            $this->attributeRepository,
            $this->productVariationBuilder,
            $this->productsFactoryMock
        );
    }

    public function testGenerateVariation()
    {
        $data = ['someKey' => 'someValue'];
        $attributeOption = $this->createMock(Option::class);
        $attributeOption->expects($this->once())->method('getData')->willReturn(['key' => 'value']);

        $attribute = $this->createMock(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class);
        $attribute->expects($this->any())->method('getOptions')->willReturn([$attributeOption]);
        $attribute->expects($this->once())->method('getAttributeCode')->willReturn(10);

        $this->option->expects($this->any())->method('getAttributeId')->willReturn(1);
        $this->attributeRepository->expects($this->once())->method('get')->with(1)->willReturn($attribute);

        $this->option->expects($this->any())->method('getData')->willReturn($data);

        $expectedAttributes = [
            1 => [
                'someKey' => 'someValue',
                'options' => [['key' => 'value']],
                'attribute_code' => 10,
            ]
        ];

        $this->productVariationBuilder->expects($this->once())
            ->method('create')
            ->with($this->product, $expectedAttributes)
            ->willReturn(['someObject']);

        $expected = ['someObject'];
        $this->assertEquals($expected, $this->model->generateVariation($this->product, [$this->option]));
    }

    public function testGetEnabledCount()
    {
        $statusEnabled = 1;
        $productsMock = $this->createMock(
            Collection::class
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
        $productsMock = $this->createMock(
            Collection::class
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
