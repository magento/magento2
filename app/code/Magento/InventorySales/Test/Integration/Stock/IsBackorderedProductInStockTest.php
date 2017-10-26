<?php

namespace  Magento\InventorySales\Test\Integration\Stock;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\InventoryApi\Api\GetProductQuantityInStockInterface;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\InventoryApi\Api\IsProductInStockInterface;

class IsBackorderedProductInStockTest extends TestCase
{
    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var GetProductQuantityInStockInterface
     */
    protected $isProductInStock;

    /**
     * @var StockItemRepositoryInterface
     */
    protected $stockItemRepository;

    /**
     * @var StockItemCriteriaInterfaceFactory
     */
    protected $stockItemCriteriaInterfaceFactory;

    protected function setUp()
    {
        $this->productRepository = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);
        $this->stockItemRepository =  Bootstrap::getObjectManager()->create(StockItemRepositoryInterface::class);
        $this->stockItemCriteriaInterfaceFactory =  Bootstrap::getObjectManager()->create(
            StockItemCriteriaInterfaceFactory::class
        );
        $this->isProductInStock = Bootstrap::getObjectManager()->create(
            IsProductInStockInterface::class
        );
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDbIsolation disabled
     */
    public function testBackorderedZeroQtyProductIsInStock()
    {
        /** @var ProductInterface $product */
        $product = $this->productRepository->get('SKU-1');
        $stockItemSearchCriteria = $this->stockItemCriteriaInterfaceFactory->create();
        $stockItemSearchCriteria->setProductsFilter($product->getId());
        $stockItemsCollection = $this->stockItemRepository->getList($stockItemSearchCriteria);

        /** @var StockItemInterface $stockItem */
        $stockItem = current($stockItemsCollection->getItems());

        $stockItem->setBackorders(1);
        $stockItem->setUseConfigBackorders(1);
        $stockItem->setQty(-15);

        $this->stockItemRepository->save($stockItem);

        $this->assertTrue($this->isProductInStock->execute('SKU-1', 1));
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     */
    public function testZeroQtyProductIsOutOfStock()
    {
        $this->productRepository->cleanCache();
        /** @var ProductInterface $product */
        $product = $this->productRepository->get('SKU-1');
        $stockItemSearchCriteria = $this->stockItemCriteriaInterfaceFactory->create();
        $stockItemSearchCriteria->setProductsFilter($product->getId());
        $stockItemsCollection = $this->stockItemRepository->getList($stockItemSearchCriteria);

        /** @var StockItemInterface $stockItem */
        $stockItem = current($stockItemsCollection->getItems());

        $stockItem->setBackorders(0);
        $stockItem->setUseConfigBackorders(0);
        $stockItem->setQty(0);

        $this->stockItemRepository->save($stockItem);

        $this->assertFalse($this->isProductInStock->execute('SKU-1', 1));
    }
}
