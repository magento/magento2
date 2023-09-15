<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Product\Compare;

use Magento\Catalog\Block\Product\Compare\ListCompare;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\AttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend;
use Magento\Framework\Pricing\Render;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Layout;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ListCompareTest extends TestCase
{
    /**
     * @var ListCompare
     */
    protected $block;

    /**
     * @var LayoutInterface|MockObject
     */
    protected $layout;

    protected function setUp(): void
    {
        $this->layout = $this->createPartialMock(Layout::class, ['getBlock']);

        $context = $this->createPartialMock(Context::class, ['getLayout']);
        $context->expects($this->any())
            ->method('getLayout')
            ->willReturn($this->layout);

        $objectManager = new ObjectManager($this);
        $this->block = $objectManager->getObject(
            ListCompare::class,
            ['context' => $context]
        );
    }

    protected function tearDown(): void
    {
        $this->block = null;
    }

    /**
     * @dataProvider attributeDataProvider
     * @param array $attributeData
     * @param string $expectedResult
     */
    public function testProductAttributeValue($attributeData, $expectedResult)
    {
        $attribute = $this->getMockBuilder(AttributeInterface::class)
            ->addMethods(['getAttributeCode', 'getSourceModel', 'getFrontendInput', 'getFrontend'])
            ->getMockForAbstractClass();
        $frontEndModel = $this->createPartialMock(AbstractFrontend::class, ['getValue']);
        $productMock = $this->createPartialMock(Product::class, ['getId', 'getData', 'hasData']);
        $productMock->expects($this->any())
            ->method('hasData')
            ->with($attributeData['attribute_code'])
            ->willReturn(true);
        $productMock->expects($this->any())
            ->method('getData')
            ->with($attributeData['attribute_code'])
            ->willReturn($attributeData['attribute_value']);
        $attribute->expects($this->any())
            ->method('getAttributeCode')
            ->willReturn($attributeData['attribute_code']);
        $attribute->expects($this->any())
            ->method('getSourceModel')
            ->willReturn($attributeData['source_model']);
        $attribute->expects($this->any())
            ->method('getFrontendInput')
            ->willReturn($attributeData['frontend_input']);
        $frontEndModel->expects($this->any())
            ->method('getValue')
            ->with($productMock)
            ->willReturn($attributeData['attribute_value']);
        $attribute->expects($this->any())
            ->method('getFrontend')
            ->willReturn($frontEndModel);
        $this->assertEquals(
            $expectedResult,
            $this->block->getProductAttributeValue($productMock, $attribute)
        );
    }

    public function testGetProductPrice()
    {
        //Data
        $expectedResult = 'html';
        $blockName = 'product.price.render.default';
        $productId = 1;

        //Verification
        $product = $this->createPartialMock(Product::class, ['getId']);
        $product->expects($this->once())
            ->method('getId')
            ->willReturn($productId);

        $blockMock = $this->createPartialMock(Render::class, ['render']);
        $blockMock->expects($this->once())
            ->method('render')
            ->with(
                'final_price',
                $product,
                [
                    'price_id' => 'product-price-' . $productId . '-compare-list-top',
                    'display_minimal_price' => true,
                    'zone' => Render::ZONE_ITEM_LIST
                ]
            )
            ->willReturn($expectedResult);

        $this->layout->expects($this->once())
            ->method('getBlock')
            ->with($blockName)
            ->willReturn($blockMock);

        $this->assertEquals($expectedResult, $this->block->getProductPrice($product, '-compare-list-top'));
    }

    /**
     * @return array
     */
    public function attributeDataProvider(): array
    {
        return [
            [
                'attributeData' => [
                    'attribute_code' => 'tier_price',
                    'source_model' => null,
                    'frontend_input' => 'text',
                    'attribute_value' => []
                ],
                'expectedResult' => __('N/A')
            ],
            [
                'attributeData' => [
                    'attribute_code' => 'special_price',
                    'source_model' => null,
                    'frontend_input' => 'decimal',
                    'attribute_value' => 50.00
                ],
                'expectedResult' => '50.00'
            ]
        ];
    }
}
