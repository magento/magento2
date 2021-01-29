<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Test\Unit\Block\Product\Grouped\AssociatedProducts;

class ListAssociatedProductsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $contextMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $registryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $typeInstanceMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeMock;

    /**
     * @var \Magento\GroupedProduct\Block\Product\Grouped\AssociatedProducts\ListAssociatedProducts
     */
    protected $block;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(\Magento\Backend\Block\Template\Context::class);
        $this->registryMock = $this->createMock(\Magento\Framework\Registry::class);
        $this->productMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $this->storeMock = $this->createMock(\Magento\Store\Model\Store::class);
        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManager::class);
        $this->typeInstanceMock = $this->createMock(\Magento\GroupedProduct\Model\Product\Type\Grouped::class);

        $this->contextMock->expects(
            $this->any()
        )->method(
            'getStoreManager'
        )->willReturn(
            $this->storeManagerMock
        );

        $this->priceCurrency = $this->getMockBuilder(
            \Magento\Framework\Pricing\PriceCurrencyInterface::class
        )->getMock();

        $this->block = new \Magento\GroupedProduct\Block\Product\Grouped\AssociatedProducts\ListAssociatedProducts(
            $this->contextMock,
            $this->registryMock,
            $this->priceCurrency
        );
    }

    /**
     * @covers \Magento\GroupedProduct\Block\Product\Grouped\AssociatedProducts\ListAssociatedProducts
     *     ::getAssociatedProducts
     */
    public function testGetAssociatedProducts()
    {
        $this->priceCurrency->expects(
            $this->any()
        )->method(
            'format'
        )->with(
            '1.00',
            false
        )->willReturn(
            '1'
        );

        $this->storeManagerMock->expects($this->any())->method('getStore')->willReturn($this->storeMock);

        $this->productMock->expects(
            $this->once()
        )->method(
            'getTypeInstance'
        )->willReturn(
            $this->typeInstanceMock
        );

        $this->registryMock->expects(
            $this->once()
        )->method(
            'registry'
        )->with(
            'current_product'
        )->willReturn(
            $this->productMock
        );

        $this->typeInstanceMock->expects(
            $this->once()
        )->method(
            'getAssociatedProducts'
        )->with(
            $this->productMock
        )->willReturn(
            [$this->generateAssociatedProduct(1), $this->generateAssociatedProduct(2)]
        );

        $expectedResult = [
            '0' => [
                'id' => 'id1',
                'sku' => 'sku1',
                'name' => 'name1',
                'qty' => 1,
                'position' => 1,
                'price' => '1',
            ],
            '1' => [
                'id' => 'id2',
                'sku' => 'sku2',
                'name' => 'name2',
                'qty' => 2,
                'position' => 2,
                'price' => '1',
            ],
        ];

        $this->assertEquals($expectedResult, $this->block->getAssociatedProducts());
    }

    /**
     * Generate associated product mock
     *
     * @param int $productKey
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function generateAssociatedProduct($productKey = 0)
    {
        $associatedProduct = $this->createPartialMock(
            \Magento\Framework\DataObject::class,
            ['getQty', 'getPosition', 'getId', 'getSku', 'getName', 'getPrice']
        );

        $associatedProduct->expects($this->once())->method('getId')->willReturn('id' . $productKey);
        $associatedProduct->expects($this->once())->method('getSku')->willReturn('sku' . $productKey);
        $associatedProduct->expects($this->once())->method('getName')->willReturn('name' . $productKey);
        $associatedProduct->expects($this->once())->method('getQty')->willReturn($productKey);
        $associatedProduct->expects($this->once())->method('getPosition')->willReturn($productKey);
        $associatedProduct->expects($this->once())->method('getPrice')->willReturn('1.00');

        return $associatedProduct;
    }
}
