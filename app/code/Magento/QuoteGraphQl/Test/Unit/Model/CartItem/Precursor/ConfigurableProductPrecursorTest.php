<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Test\Unit\Model\CartItem\Precursor;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\QuoteGraphQl\Model\CartItem\Precursor\ConfigurableProductPrecursor;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for ConfigurableProductPrecursor
 */
class ConfigurableProductPrecursorTest extends TestCase
{
    /**
     * @var ConfigurableProductPrecursor
     */
    private $precursor;

    /**
     * @var ProductRepositoryInterface|MockObject
     */
    private $productRepositoryMock;

    /**
     * @var ContextInterface|MockObject
     */
    private $contextMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->productRepositoryMock = $this->createMock(ProductRepositoryInterface::class);
        $this->contextMock = $this->createMock(ContextInterface::class);
        $this->precursor = new ConfigurableProductPrecursor($this->productRepositoryMock);
    }

    /**
     * Test processing cart item without parent SKU
     *
     * @return void
     */
    public function testProcessItemWithoutParentSku(): void
    {
        $cartItemData = [
            [
                'sku' => 'simple-1',
                'quantity' => 1,
                'selected_options' => ['option1'],
            ]
        ];

        $result = $this->precursor->process($cartItemData, $this->contextMock);

        $this->assertSame($cartItemData, $result);
        $this->assertEmpty($this->precursor->getErrors());
    }

    /**
     * Test processing valid configurable product
     *
     * @return void
     * @throws Exception
     */
    public function testProcessValidConfigurableProduct(): void
    {
        $childProduct = $this->getMockProduct('simple-1');
        $childProduct->method('getData')
            ->willReturn(['color' => '1']);

        $productAttributeMock = $this->createMock(AbstractAttribute::class);
        $productAttributeMock->method('getAttributeId')->willReturn('1');
        $productAttributeMock->method('getAttributeCode')->willReturn('color');

        $attributeMock = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->addMethods(['getProductAttribute'])
            ->getMock();
        $attributeMock->method('getProductAttribute')->willReturn($productAttributeMock);

        $attributesCollection = [$attributeMock];

        $configurableTypeMock = $this->createMock(Configurable::class);
        $configurableTypeMock->method('getConfigurableAttributes')->willReturn($attributesCollection);

        $parentProduct = $this->getMockProduct('configurable-1', Configurable::TYPE_CODE);
        $parentProduct->method('getTypeInstance')->willReturn($configurableTypeMock);

        $this->productRepositoryMock->expects($this->exactly(2))
            ->method('get')
            ->willReturnMap([
                ['simple-1', false, null, false, $childProduct],
                ['configurable-1', false, null, false, $parentProduct],
            ]);

        $cartItemData = [
            [
                'sku' => 'simple-1',
                'parent_sku' => 'configurable-1',
                'quantity' => 2,
                'selected_options' => ['existing-option'],
            ]
        ];

        $expected = [
            [
                'sku' => 'configurable-1',
                'quantity' => 2,
                'selected_options' => [base64_encode('configurable/1/1'), 'existing-option'],
                'entered_options' => [],
                'parent_sku' => null
            ]
        ];

        $result = $this->precursor->process($cartItemData, $this->contextMock);

        $this->assertEquals($expected, $result);
        $this->assertEmpty($this->precursor->getErrors());
    }

    /**
     * Test processing with non-configurable parent product
     *
     * @return void
     * @throws Exception
     */
    public function testProcessNonConfigurableParent(): void
    {
        $childProduct = $this->getMockProduct('simple-1');
        $parentProduct = $this->getMockProduct('simple-parent', 'simple');

        $this->productRepositoryMock->expects($this->exactly(2))
            ->method('get')
            ->willReturnMap([
                ['simple-1', false, null, false, $childProduct],
                ['simple-parent', false, null, false, $parentProduct],
            ]);

        $cartItemData = [
            [
                'sku' => 'simple-1',
                'parent_sku' => 'simple-parent',
                'quantity' => 1
            ]
        ];

        $result = $this->precursor->process($cartItemData, $this->contextMock);

        $this->assertEquals($cartItemData, $result);
        $this->assertCount(1, $this->precursor->getErrors());
        $this->assertStringContainsString('not a configurable product', $this->precursor->getErrors()[0]['message']);
    }

    /**
     * Test processing with no matching attributes
     *
     * @return void
     * @throws Exception
     */
    public function testProcessNoMatchingAttributes(): void
    {
        $childProduct = $this->getMockProduct('simple-1');
        $childProduct->method('getData')
            ->willReturn([]); // Empty array to represent no matching attributes

        $productAttributeMock = $this->createMock(AbstractAttribute::class);
        $productAttributeMock->method('getAttributeId')->willReturn('1');
        $productAttributeMock->method('getAttributeCode')->willReturn('color');

        $attributeMock = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->addMethods(['getProductAttribute'])
            ->getMock();
        $attributeMock->method('getProductAttribute')->willReturn($productAttributeMock);

        $attributesCollection = [$attributeMock];

        $configurableTypeMock = $this->createMock(Configurable::class);
        $configurableTypeMock->method('getConfigurableAttributes')->willReturn($attributesCollection);

        $parentProduct = $this->getMockProduct('configurable-1', Configurable::TYPE_CODE);
        $parentProduct->method('getTypeInstance')->willReturn($configurableTypeMock);

        $this->productRepositoryMock->expects($this->exactly(2))
            ->method('get')
            ->willReturnMap([
                ['simple-1', false, null, false, $childProduct],
                ['configurable-1', false, null, false, $parentProduct],
            ]);

        $cartItemData = [
            [
                'sku' => 'simple-1',
                'parent_sku' => 'configurable-1',
                'quantity' => 1
            ]
        ];

        $result = $this->precursor->process($cartItemData, $this->contextMock);

        $this->assertEquals($cartItemData, $result);
        $this->assertCount(1, $this->precursor->getErrors());
        $this->assertStringContainsString('Could not match child product', $this->precursor->getErrors()[0]['message']);
    }

    /**
     * Test processing with product not found exception
     *
     * @return void
     */
    public function testProcessProductNotFound(): void
    {
        $this->productRepositoryMock->method('get')
            ->willThrowException(new NoSuchEntityException(__('Product not found')));

        $cartItemData = [
            [
                'sku' => 'unknown',
                'parent_sku' => 'unknown-parent',
                'quantity' => 1
            ]
        ];

        $result = $this->precursor->process($cartItemData, $this->contextMock);

        $this->assertEquals($cartItemData, $result);
        $this->assertCount(1, $this->precursor->getErrors());
        $this->assertStringContainsString('Product not found', $this->precursor->getErrors()[0]['message']);
    }

    /**
     * Create mock product
     *
     * @param string $sku Product SKU
     * @param string $typeId Product type ID
     * @return ProductInterface
     * @throws Exception
     */
    private function getMockProduct(string $sku, string $typeId = 'simple'): ProductInterface
    {
        $product = $this->getMockBuilder(ProductInterface::class)
            ->addMethods(['getData', 'getTypeInstance'])
            ->onlyMethods(['getSku', 'getTypeId', 'getId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $product->method('getSku')->willReturn($sku);
        $product->method('getTypeId')->willReturn($typeId);
        $product->method('getId')->willReturn(rand(1, 100));
        return $product;
    }
}
