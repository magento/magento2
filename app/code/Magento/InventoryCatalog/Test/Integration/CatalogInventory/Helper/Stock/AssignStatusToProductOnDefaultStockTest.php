<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Test\Integration\CatalogInventory\Helper\Stock;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Helper\Stock;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class AssignStatusToProductOnDefaultStockTest extends TestCase
{
    /**
     * @var Stock
     */
    private $stockHelper;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->stockHelper = Bootstrap::getObjectManager()->get(Stock::class);
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/source_items_on_default_source.php
     */
    public function testAssignStatusToProductIfStatusParameterIsNotPassed()
    {
        $productsData = [
            'SKU-1' => 1,
            'SKU-2' => 1,
            'SKU-3' => 0,
        ];

        foreach ($productsData as $sku => $expectedStatus) {
            $product = $this->productRepository->get($sku);
            /** @var Product $product */
            $this->stockHelper->assignStatusToProduct($product);

            self::assertEquals($expectedStatus, $product->isSalable());
        }
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/source_items_on_default_source.php
     */
    public function testAssignStatusToProductIfStatusParameterIsPassed()
    {
        $expectedStatus = 1;
        $productsSku = [
            'SKU-1',
            'SKU-2',
            'SKU-3',
        ];

        foreach ($productsSku as $sku) {
            $product = $this->productRepository->get($sku);
            /** @var Product $product */
            $this->stockHelper->assignStatusToProduct($product, $expectedStatus);

            self::assertEquals($expectedStatus, $product->isSalable());
        }
    }
}
