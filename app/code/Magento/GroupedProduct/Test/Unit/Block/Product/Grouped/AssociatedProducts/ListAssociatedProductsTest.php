<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GroupedProduct\Test\Unit\Block\Product\Grouped\AssociatedProducts;

use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Registry;
use Magento\GroupedProduct\Block\Product\Grouped\AssociatedProducts\ListAssociatedProducts;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ListAssociatedProductsTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $contextMock;

    /**
     * @var MockObject
     */
    protected $registryMock;

    /**
     * @var MockObject
     */
    protected $productMock;

    /**
     * @var MockObject
     */
    protected $typeInstanceMock;

    /**
     * @var MockObject
     */
    protected $storeManagerMock;

    /**
     * @var MockObject
     */
    protected $storeMock;

    /**
     * @var ListAssociatedProducts
     */
    protected $block;

    /**
     * @var MockObject|PriceCurrencyInterface
     */
    protected $priceCurrency;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->registryMock = $this->createMock(Registry::class);
        $this->productMock = $this->createMock(Product::class);
        $this->storeMock = $this->createMock(Store::class);
        $this->storeManagerMock = $this->createMock(StoreManager::class);
        $this->typeInstanceMock = $this->createMock(Grouped::class);

        $this->contextMock->expects(
            $this->any()
        )->method(
            'getStoreManager'
        )->willReturn(
            $this->storeManagerMock
        );

        $this->priceCurrency = $this->getMockBuilder(
            PriceCurrencyInterface::class
        )->getMock();

        $this->block = new ListAssociatedProducts(
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
     * @return MockObject
     */
    protected function generateAssociatedProduct($productKey = 0)
    {
        $associatedProduct = $this->getMockBuilder(DataObject::class)
            ->addMethods(['getQty', 'getPosition', 'getId', 'getSku', 'getName', 'getPrice'])
            ->disableOriginalConstructor()
            ->getMock();

        $associatedProduct->expects($this->once())->method('getId')->willReturn('id' . $productKey);
        $associatedProduct->expects($this->once())->method('getSku')->willReturn('sku' . $productKey);
        $associatedProduct->expects($this->once())->method('getName')->willReturn('name' . $productKey);
        $associatedProduct->expects($this->once())->method('getQty')->willReturn($productKey);
        $associatedProduct->expects($this->once())->method('getPosition')->willReturn($productKey);
        $associatedProduct->expects($this->once())->method('getPrice')->willReturn('1.00');

        return $associatedProduct;
    }
}
