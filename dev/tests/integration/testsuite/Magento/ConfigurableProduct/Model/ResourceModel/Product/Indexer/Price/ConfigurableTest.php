<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\ResourceModel\Product\Indexer\Price;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\CatalogInventory\Model\Stock;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Configurable test
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @magentoAppArea adminhtml
 */
class ConfigurableTest extends TestCase
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var StockItemRepositoryInterface
     */
    private $stockRepository;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $this->stockRepository = Bootstrap::getObjectManager()->get(StockItemRepositoryInterface::class);
    }

    /**
     * Test get product final price if one of child is disabled
     *
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @magentoDbIsolation disabled
     *
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function testGetProductFinalPriceIfOneOfChildIsDisabled()
    {
        $configurableProduct = $this->getConfigurableProductFromCollection();
        $this->assertEquals(10, $configurableProduct->getMinimalPrice());

        $childProduct = $this->productRepository->getById(10, false, null, true);
        $childProduct->setStatus(Status::STATUS_DISABLED);
        // update in global scope
        $currentStoreId = $this->storeManager->getStore()->getId();
        $this->storeManager->setCurrentStore(Store::ADMIN_CODE);
        $this->productRepository->save($childProduct);
        $this->storeManager->setCurrentStore($currentStoreId);

        $configurableProduct = $this->getConfigurableProductFromCollection();
        $this->assertEquals(20, $configurableProduct->getMinimalPrice());
    }

    /**
     * Test get product final price if one of child is disabled per store
     *
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @magentoDbIsolation disabled
     *
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function testGetProductFinalPriceIfOneOfChildIsDisabledPerStore()
    {
        $configurableProduct = $this->getConfigurableProductFromCollection();
        $this->assertEquals(10, $configurableProduct->getMinimalPrice());

        $childProduct = $this->productRepository->getById(10, false, null, true);
        $childProduct->setStatus(Status::STATUS_DISABLED);

        // update in default store scope
        $currentStoreId = $this->storeManager->getStore()->getId();
        $defaultStore = $this->storeManager->getDefaultStoreView();
        $this->storeManager->setCurrentStore($defaultStore->getId());
        $this->productRepository->save($childProduct);
        $this->storeManager->setCurrentStore($currentStoreId);

        $configurableProduct = $this->getConfigurableProductFromCollection();
        $this->assertEquals(20, $configurableProduct->getMinimalPrice());
    }

    /**
     * Test get product minimal price if one child is out of stock
     *
     * @magentoConfigFixture current_store cataloginventory/options/show_out_of_stock 1
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @magentoDbIsolation disabled
     *
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testGetProductMinimalPriceIfOneOfChildIsOutOfStock()
    {
        $configurableProduct = $this->getConfigurableProductFromCollection();
        $this->assertEquals(10, $configurableProduct->getMinimalPrice());

        $childProduct = $this->productRepository->get('simple_10', false, null, true);
        $stockItem = $childProduct->getExtensionAttributes()->getStockItem();
        $stockItem->setIsInStock(Stock::STOCK_OUT_OF_STOCK);
        $this->stockRepository->save($stockItem);

        $configurableProduct = $this->getConfigurableProductFromCollection();
        $this->assertEquals(20, $configurableProduct->getMinimalPrice());
    }

    /**
     * Retrieve configurable product.
     * Returns Configurable product that was created by Magento/ConfigurableProduct/_files/product_configurable.php
     * fixture
     *
     * @return ProductInterface
     */
    private function getConfigurableProductFromCollection(): ProductInterface
    {
        /** @var Collection $collection */
        $collection = Bootstrap::getObjectManager()->get(CollectionFactory::class)
            ->create();
        /** @var ProductInterface $configurableProduct */
        $configurableProduct = $collection
            ->addIdFilter([1])
            ->addMinimalPrice()
            ->load()
            ->getFirstItem();

        return $configurableProduct;
    }
}
