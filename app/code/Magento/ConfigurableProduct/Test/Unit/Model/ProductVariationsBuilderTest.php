<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Unit\Model;

class ProductVariationsBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ProductVariationsBuilder
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $customAttributeFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $variationMatrix;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $product;

    protected function setUp()
    {
        $this->customAttributeFactory = $this->createMock(\Magento\Framework\Api\AttributeValueFactory::class);

        $this->product = $this->createPartialMock(
            \Magento\Catalog\Model\Product::class,
            ['getData', 'getPrice', 'getName', 'getSku', '__wakeup', 'getCustomAttributes']
        );

        $this->productFactory = $this->createPartialMock(\Magento\Catalog\Model\ProductFactory::class, ['create']);

        $this->variationMatrix = $this->createMock(
            \Magento\ConfigurableProduct\Model\Product\Type\VariationMatrix::class
        );

        $this->model = new \Magento\ConfigurableProduct\Model\ProductVariationsBuilder(
            $this->productFactory,
            $this->customAttributeFactory,
            $this->variationMatrix
        );
    }

    public function testCreate()
    {
        $output = $this->createPartialMock(
            \Magento\Catalog\Model\Product::class,
            ['setPrice', '__wakeup', 'setData', 'getCustomAttributes', 'setName', 'setSku', 'setVisibility']
        );
        $attributes = [10 => ['attribute_code' => 'sort_order']];
        $variations = [
            [10 => ['value' => 15, 'price' => ['pricing_value' => 10]]],
        ];
        $this->variationMatrix->expects($this->once())
            ->method('getVariations')
            ->with($attributes)
            ->willReturn($variations);

        $this->productFactory->expects($this->once())->method('create')->willReturn($output);
        $productData = ['id' => '10', 'title' => 'simple'];
        $this->product->expects($this->once())->method('getData')->willReturn($productData);
        $this->product->expects($this->once())->method('getName')->willReturn('simple');
        $this->product->expects($this->once())->method('getSku')->willReturn('simple-sku');
        $this->product->expects($this->once())->method('getPrice')->willReturn(10);

        $output->expects($this->at(0))->method('setData')->with($productData);

        $attribute = $this->createMock(\Magento\Framework\Api\AttributeInterface::class);
        $attribute->expects($this->once())
            ->method('setAttributeCode')
            ->with('sort_order')
            ->willReturnSelf();

        $attribute->expects($this->once())
            ->method('setValue')
            ->with(15)
            ->willReturnSelf();

        $this->customAttributeFactory->expects($this->once())
            ->method('create')
            ->willReturn($attribute);

        $output->expects($this->once())->method('getCustomAttributes')->willReturn([]);

        $output->expects($this->at(2))->method('setData')->with('custom_attributes', ['sort_order' => $attribute]);
        $output->expects($this->once())->method('setPrice')->with(10);
        $output->expects($this->once())->method('setName')->with('simple-15');
        $output->expects($this->once())->method('setSku')->with('simple-sku-15');
        $output->expects($this->once())->method('setVisibility')
            ->with(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE);

        $this->assertEquals([$output], $this->model->create($this->product, $attributes));
    }
}
