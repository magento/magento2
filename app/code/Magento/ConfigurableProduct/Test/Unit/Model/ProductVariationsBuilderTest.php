<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Model;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductFactory;
use Magento\ConfigurableProduct\Model\Product\Type\VariationMatrix;
use Magento\ConfigurableProduct\Model\ProductVariationsBuilder;
use Magento\Framework\Api\AttributeInterface;
use Magento\Framework\Api\AttributeValueFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductVariationsBuilderTest extends TestCase
{
    /**
     * @var ProductVariationsBuilder
     */
    protected $model;

    /**
     * @var MockObject
     */
    private $customAttributeFactory;

    /**
     * @var MockObject
     */
    protected $productFactory;

    /**
     * @var MockObject
     */
    private $variationMatrix;

    /**
     * @var MockObject
     */
    protected $product;

    protected function setUp(): void
    {
        $this->customAttributeFactory = $this->createMock(AttributeValueFactory::class);

        $this->product = $this->createPartialMock(
            Product::class,
            ['getData', 'getPrice', 'getName', 'getSku', 'getCustomAttributes']
        );

        $this->productFactory = $this->createPartialMock(ProductFactory::class, ['create']);

        $this->variationMatrix = $this->createMock(
            VariationMatrix::class
        );

        $this->model = new ProductVariationsBuilder(
            $this->productFactory,
            $this->customAttributeFactory,
            $this->variationMatrix
        );
    }

    public function testCreate()
    {
        $output = $this->createPartialMock(
            Product::class,
            ['setPrice', 'setData', 'getCustomAttributes', 'setName', 'setSku', 'setVisibility']
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

        $attribute = $this->getMockForAbstractClass(AttributeInterface::class);
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
            ->with(Visibility::VISIBILITY_NOT_VISIBLE);

        $this->assertEquals([$output], $this->model->create($this->product, $attributes));
    }
}
