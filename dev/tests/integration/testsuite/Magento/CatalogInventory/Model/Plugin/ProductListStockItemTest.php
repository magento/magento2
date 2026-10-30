<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Model\Plugin;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class ProductListStockItemTest extends TestCase
{
    #[
        AppArea('webapi_rest'),
        DbIsolation(true),
        DataFixture(
            ProductFixture::class,
            ['sku' => 'stock-item-list-1', 'extension_attributes' => ['stock_item' => ['qty' => 41]]]
        ),
        DataFixture(
            ProductFixture::class,
            ['sku' => 'stock-item-list-2', 'extension_attributes' => ['stock_item' => ['qty' => 42]]]
        ),
    ]
    public function testGetListReturnsStockItemInWebApiArea(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $criteria = $objectManager->get(SearchCriteriaBuilder::class)
            ->addFilter('sku', ['stock-item-list-1', 'stock-item-list-2'], 'in')
            ->create();

        $items = $objectManager->create(ProductRepositoryInterface::class)->getList($criteria)->getItems();

        $this->assertCount(2, $items);
        $quantities = [];
        foreach ($items as $product) {
            $stockItem = $product->getExtensionAttributes()->getStockItem();
            $this->assertNotNull($stockItem, 'Stock item is missing for ' . $product->getSku());
            $quantities[$product->getSku()] = (float)$stockItem->getQty();
        }
        ksort($quantities);
        $this->assertEquals(['stock-item-list-1' => 41.0, 'stock-item-list-2' => 42.0], $quantities);
    }
}
