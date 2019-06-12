<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogInventory\Model\Plugin;

use Magento\CatalogInventory\Api\Data\StockItemInterface;

/**
 * @magentoAppArea adminhtml
 */
class ProductSearchTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * @var array
     */
    private $stockItemData = [
        StockItemInterface::QTY => 555,
        StockItemInterface::MANAGE_STOCK => true,
        StockItemInterface::IS_IN_STOCK => false,
    ];

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoConfigFixture current_store cataloginventory/options/show_out_of_stock 1
     */
    public function testExecute() : void
    {
        $productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $product = $productRepository->get('simple');
        $product->setQuantityAndStockStatus($this->stockItemData);
        $product->save();
        $this->getRequest()
            ->setPostValue('searchKey', 'simple')
            ->setPostValue('page', 1)
            ->setPostValue('limit', 50);
        $this->dispatch('backend/catalog/product/search');
        $responseBody = $this->getResponse()->getBody();
        $this->assertContains(
            '"options":{"1":{"value":"1","label":"Simple Product","is_active":1,"path":"simple","optgroup":false}',
            $responseBody
        );
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoConfigFixture current_store cataloginventory/options/show_out_of_stock 0
     */
    public function testExecuteNotShowOutOfStock() : void
    {
        $productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $product = $productRepository->get('simple');
        $product->setQuantityAndStockStatus($this->stockItemData);
        $product->save();
        $this->getRequest()
            ->setPostValue('searchKey', 'simple')
            ->setPostValue('page', 1)
            ->setPostValue('limit', 50);
        $this->dispatch('backend/catalog/product/search');
        $responseBody = $this->getResponse()->getBody();
        $this->assertNotContains(
            '"options":{"1":{"value":"1","label":"Simple Product","is_active":1,"path":"simple","optgroup":false}',
            $responseBody
        );
    }
}
