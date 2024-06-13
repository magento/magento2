<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GroupedProduct\Test\Unit\Block\Product\Grouped\AssociatedProducts;

use PHPUnit\Framework\Attributes\CoversClass;
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

#[CoversClass(\Magento\GroupedProduct\Block\Product\Grouped\AssociatedProducts\ListAssociatedProducts::class)]
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

        $this->contextMock->method('getStoreManager')->willReturn(
            $this->storeManagerMock
        );

        $this->priceCurrency = $this->createMock(PriceCurrencyInterface::class);

        $this->block = new ListAssociatedProducts(
            $this->contextMock,
            $this->registryMock,
            $this->priceCurrency
        );
    }

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

        $this->storeManagerMock->method('getStore')->willReturn($this->storeMock);

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
     * @return \Magento\Framework\DataObject\Test\Unit\Helper\DataObjectTestHelper
     */
    protected function generateAssociatedProduct($productKey = 0)
    {
        $associatedProduct = new \Magento\Framework\DataObject\Test\Unit\Helper\DataObjectTestHelper();
        $associatedProduct->setId('id' . $productKey);
        $associatedProduct->setSku('sku' . $productKey);
        $associatedProduct->setName('name' . $productKey);
        $associatedProduct->setQty($productKey);
        $associatedProduct->setPosition($productKey);
        $associatedProduct->setPrice('1.00');

        return $associatedProduct;
    }
}
