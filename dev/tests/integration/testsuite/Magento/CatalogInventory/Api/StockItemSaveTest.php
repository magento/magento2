<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Api;

use Magento\Catalog\Api\Data\ProductExtensionInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;

class StockItemSaveTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDbIsolation disabled
     */
    public function testSave()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $objectManager->get(ProductRepositoryInterface::class);
        /** @var ProductInterface $product */
        $product = $productRepository->get('simple', false, null, true);

        /** @var ProductExtensionInterface $ea */
        $ea = $product->getExtensionAttributes();
        $ea->getStockItem()->setQty(555);
        $productRepository->save($product);

        $product = $productRepository->get('simple', false, null, true);
        $this->assertEquals(555, $product->getExtensionAttributes()->getStockItem()->getQty());

        $stockItem = $product->getExtensionAttributes()->getStockItem();
        $stockItem->setQty(200);
        /** @var StockItemRepositoryInterface $stockItemRepository */
        $stockItemRepository = $objectManager->get(StockItemRepositoryInterface::class);
        $stockItemRepository->save($stockItem);
        $this->assertEquals(200, $product->getExtensionAttributes()->getStockItem()->getQty());

        $product = $productRepository->get('simple', false, null, true);
        $this->assertEquals(200, $product->getExtensionAttributes()->getStockItem()->getQty());
    }
}
