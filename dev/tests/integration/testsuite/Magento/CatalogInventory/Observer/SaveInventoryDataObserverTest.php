<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogInventory\Observer;

use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\Data\ProductExtensionInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Test for SaveInventoryDataObserver
 */
class SaveInventoryDataObserverTest extends TestCase
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var StockItemRepositoryInterface
     */
    private $stockItemRepository;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->productRepository = Bootstrap::getObjectManager()
            ->get(ProductRepositoryInterface::class);
        $this->stockItemRepository = Bootstrap::getObjectManager()
            ->get(StockItemRepositoryInterface::class);
    }

    /**
     * Check that parent product will be out of stock
     *
     * @magentoAppArea adminhtml
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @magentoDataFixture Magento/CatalogInventory/_files/configurable_options_with_low_stock.php
     * @throws NoSuchEntityException
     * @throws InputException
     * @throws StateException
     * @throws CouldNotSaveException
     * @return void
     */
    public function testAutoChangingIsInStockForParent()
    {
        /** @var ProductInterface $product */
        $product = $this->productRepository->get('simple_10');

        /** @var ProductExtensionInterface $attributes*/
        $attributes = $product->getExtensionAttributes();

        /** @var StockItemInterface $stockItem */
        $stockItem = $attributes->getStockItem();
        $stockItem->setQty(0);
        $stockItem->setIsInStock(false);
        $attributes->setStockItem($stockItem);
        $product->setExtensionAttributes($attributes);
        $this->productRepository->save($product);

        /** @var ProductInterface $product */
        $parentProduct = $this->productRepository->get('configurable');

        $parentProductStockItem = $this->stockItemRepository->get(
            $parentProduct->getExtensionAttributes()->getStockItem()->getItemId()
        );
        $this->assertFalse($parentProductStockItem->getIsInStock());
    }
}
