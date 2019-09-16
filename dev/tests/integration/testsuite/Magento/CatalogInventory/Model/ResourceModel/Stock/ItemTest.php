<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Model\ResourceModel\Stock;

use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\CatalogInventory\Model\Stock;
use Magento\TestFramework\App\Config;
use Magento\Store\Model\ScopeInterface;
use Magento\CatalogInventory\Model\Configuration;

/**
 * Item test.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ItemTest extends TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Item
     */
    private $stockItemResourceModel;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var StockItemRepositoryInterface
     */
    private $stockItemRepository;

    /**
     * @var StockItemCriteriaInterfaceFactory
     */
    private $stockItemCriteriaFactory;

    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * @var Config
     */
    private $config;

    /**
     * Saved Stock Status Item data.
     *
     * @var array
     */
    private $stockStatusData;

    /**
     * Saved system config data.
     *
     * @var array
     */
    private $configData;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();

        $this->stockItemResourceModel = $this->objectManager->get(Item::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->stockItemRepository = $this->objectManager->get(StockItemRepositoryInterface::class);
        $this->stockItemCriteriaFactory = $this->objectManager->get(StockItemCriteriaInterfaceFactory::class);
        $this->stockConfiguration = $this->objectManager->get(StockConfigurationInterface::class);
        $this->config = $this->objectManager->get(Config::class);

        $this->storeSystemConfig();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        $this->restoreSystemConfig();
    }

    /**
     * Tests updateSetOutOfStock method.
     *
     * @return void
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testUpdateSetOutOfStock(): void
    {
        $stockItem = $this->getStockItem(1);
        $this->saveStockItemData($stockItem);
        $this->storeSystemConfig();

        foreach ($this->stockStatusVariations() as $variation) {
            /**
             * Check when Stock Item use it's own configuration of backorders.
             */
            $this->configureStockItem($stockItem, $variation);
            $this->stockItemResourceModel->updateSetOutOfStock($this->stockConfiguration->getDefaultScopeId());
            $stockItem = $this->getStockItem(1);

            self::assertEquals($variation['is_in_stock'], $stockItem->getIsInStock(), $variation['message']);
            $stockItem = $this->resetStockItem($stockItem);

            /**
             * Check when Stock Item use system configuration of backorders.
             */
            $this->configureStockItemWithSystemConfig($stockItem, $variation);
            $this->stockItemResourceModel->updateSetOutOfStock($this->stockConfiguration->getDefaultScopeId());
            $stockItem = $this->getStockItem(1);

            self::assertEquals($variation['is_in_stock'], $stockItem->getIsInStock(), $variation['message']);
            $stockItem = $this->resetStockItem($stockItem);
            $this->restoreSystemConfig();
        }
    }

    /**
     * Tests updateSetInOfStock method.
     *
     * @return void
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple_out_of_stock.php
     */
    public function testUpdateSetInStock(): void
    {
        $product = $this->productRepository->get('simple-out-of-stock');
        $stockItem = $this->getStockItem((int)$product->getId());
        $this->saveStockItemData($stockItem);
        $this->storeSystemConfig();

        foreach ($this->stockStatusVariations() as $variation) {
            /**
             * Check when Stock Item use it's own configuration of backorders.
             */
            $stockItem->setStockStatusChangedAutomaticallyFlag(true);
            $this->configureStockItem($stockItem, $variation);
            $this->stockItemResourceModel->updateSetInStock($this->stockConfiguration->getDefaultScopeId());
            $stockItem = $this->getStockItem((int)$product->getId());

            self::assertEquals($variation['is_in_stock'], $stockItem->getIsInStock(), $variation['message']);
            $stockItem = $this->resetStockItem($stockItem);

            /**
             * Check when Stock Item use the system configuration of backorders.
             */
            $stockItem->setStockStatusChangedAuto(1);
            $this->configureStockItemWithSystemConfig($stockItem, $variation);
            $this->stockItemResourceModel->updateSetInStock($this->stockConfiguration->getDefaultScopeId());
            $stockItem = $this->getStockItem((int)$product->getId());

            self::assertEquals($variation['is_in_stock'], $stockItem->getIsInStock(), $variation['message']);
            $stockItem = $this->resetStockItem($stockItem);
            $this->restoreSystemConfig();
        }
    }

    /**
     * Configure backorders feature for Stock Item.
     *
     * @param StockItemInterface $stockItem
     * @param array $config
     * @return void
     */
    private function configureStockItem(StockItemInterface $stockItem, array $config): void
    {
        /**
         * Configuring Stock Item to use it's own configuration.
         */
        $stockItem->setUseConfigBackorders(0);
        $stockItem->setUseConfigMinQty(0);
        $stockItem->setQty($config['qty']);
        $stockItem->setMinQty($config['min_qty']);
        $stockItem->setBackorders($config['backorders']);

        $this->stockItemRepository->save($stockItem);
    }

    /**
     * Configure backorders feature using the system configuration for Stock Item.
     *
     * @param StockItemInterface $stockItem
     * @param array $config
     * @return void
     */
    private function configureStockItemWithSystemConfig(StockItemInterface $stockItem, array $config): void
    {
        /**
         * Configuring Stock Item to use the system configuration.
         */
        $stockItem->setUseConfigBackorders(1);
        $stockItem->setUseConfigMinQty(1);

        $this->config->setValue(
            Configuration::XML_PATH_BACKORDERS,
            $config['backorders'],
            ScopeInterface::SCOPE_STORE
        );
        $this->config->setValue(
            Configuration::XML_PATH_MIN_QTY,
            $config['min_qty'],
            ScopeInterface::SCOPE_STORE
        );

        $stockItem->setQty($config['qty']);

        $this->stockItemRepository->save($stockItem);
    }

    /**
     * Stock status variations.
     *
     * @return array
     */
    private function stockStatusVariations(): array
    {
        return [
            // Quantity has not reached Threshold
            [
                'qty' => 3,
                'min_qty' => 2,
                'backorders' => Stock::BACKORDERS_NO,
                'is_in_stock' => true,
                'message' => "Stock status should be In Stock - v.1",
            ],
            // Quantity has reached Threshold
            [
                'qty' => 3,
                'min_qty' => 3,
                'backorders' => Stock::BACKORDERS_NO,
                'is_in_stock' => false,
                'message' => "Stock status should be Out of Stock - v.2",
            ],
            // Infinite backorders
            [
                'qty' => -100,
                'min_qty' => 0,
                'backorders' => Stock::BACKORDERS_YES_NOTIFY,
                'is_in_stock' => true,
                'message' => "Stock status should be In Stock for infinite backorders - v.3",
            ],
            // Quantity has not reached Threshold's negative value
            [
                'qty' => -99,
                'min_qty' => -100,
                'backorders' => Stock::BACKORDERS_YES_NOTIFY,
                'is_in_stock' => true,
                'message' => "Stock status should be In Stock - v.4",
            ],
            // Quantity has reached Threshold's negative value
            [
                'qty' => -100,
                'min_qty' => -99,
                'backorders' => Stock::BACKORDERS_YES_NOTIFY,
                'is_in_stock' => false,
                'message' => "Stock status should be Out of Stock - v.5",
            ],
        ];
    }

    /**
     * Stores Stock Item values.
     *
     * @param StockItemInterface $stockItem
     * @return void
     */
    private function saveStockItemData(StockItemInterface $stockItem): void
    {
        $this->stockStatusData = $stockItem->getData();
    }

    /**
     * Resets Stock Item to previous saved values and prepare for new test variation.
     *
     * @param StockItemInterface $stockItem
     * @return StockItemInterface
     */
    private function resetStockItem(StockItemInterface $stockItem): StockItemInterface
    {
        $stockItem->setData($this->stockStatusData);

        return $this->stockItemRepository->save($stockItem);
    }

    /**
     * Get Stock Item by product id.
     *
     * @param int $productId
     * @param int|null $scope
     * @return StockItemInterface
     * @throws NoSuchEntityException
     */
    private function getStockItem(int $productId, ?int $scope = null): StockItemInterface
    {
        $scope = $scope ?? $this->stockConfiguration->getDefaultScopeId();
        $stockItemCriteria = $this->stockItemCriteriaFactory->create();
        $stockItemCriteria->setScopeFilter($scope);
        $stockItemCriteria->setProductsFilter([$productId]);
        $stockItems = $this->stockItemRepository->getList($stockItemCriteria);
        $stockItems = $stockItems->getItems();

        if (empty($stockItems)) {
            throw new NoSuchEntityException();
        }

        $stockItem = reset($stockItems);

        return $stockItem;
    }

    /**
     * Stores system configuration.
     *
     * @return void
     */
    private function storeSystemConfig(): void
    {
        /**
         * Save system configuration data.
         */
        $backorders = $this->config->getValue(
            Configuration::XML_PATH_BACKORDERS,
            ScopeInterface::SCOPE_STORE
        );
        $minQty = $this->config->getValue(Configuration::XML_PATH_MIN_QTY, ScopeInterface::SCOPE_STORE);
        $this->configData = [
            'backorders' => $backorders,
            'min_qty' => $minQty,
        ];
    }

    /**
     * Restores system configuration.
     *
     * @return void
     */
    private function restoreSystemConfig(): void
    {
        /**
         * Turn back system configuration.
         */
        $this->config->setValue(
            Configuration::XML_PATH_BACKORDERS,
            $this->configData['backorders'],
            ScopeInterface::SCOPE_STORE
        );
        $this->config->setValue(
            Configuration::XML_PATH_MIN_QTY,
            $this->configData['min_qty'],
            ScopeInterface::SCOPE_STORE
        );
    }
}
