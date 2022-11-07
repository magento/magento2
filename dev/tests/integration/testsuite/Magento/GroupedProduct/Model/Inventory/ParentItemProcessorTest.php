<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GroupedProduct\Model\Inventory;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\ObjectManagerInterface;

/**
 * Test stock status parent product
 */
class ParentItemProcessorTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * Test stock status parent product if children are out of stock
     *
     * @magentoDataFixture Magento/GroupedProduct/_files/product_grouped_with_simple_out_of_stock.php
     *
     * @return void
     */
    public function testOutOfStockParentProduct(): void
    {
        $productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        /** @var Product $product */
        $product = $productRepository->get('simple_100000001');
        $product->setStockData(['qty' => 0, 'is_in_stock' => 0]);
        $productRepository->save($product);
        /** @var StockItemRepository $stockItemRepository */
        $stockItemRepository = $this->objectManager->create(StockItemRepository::class);
        /** @var StockRegistryInterface $stockRegistry */
        $stockRegistry = $this->objectManager->create(StockRegistryInterface::class);
        $stockItem = $stockRegistry->getStockItemBySku('grouped');
        $stockItem = $stockItemRepository->get($stockItem->getItemId());

        $this->assertEquals(false, $stockItem->getIsInStock());
    }
}
