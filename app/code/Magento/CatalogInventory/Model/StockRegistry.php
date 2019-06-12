<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Model;

use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
use Magento\Framework\Exception\InputException;

/**
 * Class StockRegistry
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StockRegistry implements StockRegistryInterface
{
    /**
     * @var StockConfigurationInterface
     */
    protected $stockConfiguration;

    /**
     * @var StockRegistryProviderInterface
     */
    protected $stockRegistryProvider;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var StockItemRepositoryInterface
     */
    protected $stockItemRepository;

    /**
     * @var \Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory
     */
    protected $criteriaFactory;

    /**
     * @param StockConfigurationInterface $stockConfiguration
     * @param StockRegistryProviderInterface $stockRegistryProvider
     * @param StockItemRepositoryInterface $stockItemRepository
     * @param StockItemCriteriaInterfaceFactory $criteriaFactory
     * @param ProductFactory $productFactory
     */
    public function __construct(
        StockConfigurationInterface $stockConfiguration,
        StockRegistryProviderInterface $stockRegistryProvider,
        StockItemRepositoryInterface $stockItemRepository,
        StockItemCriteriaInterfaceFactory $criteriaFactory,
        ProductFactory $productFactory
    ) {
        $this->stockConfiguration = $stockConfiguration;
        $this->stockRegistryProvider = $stockRegistryProvider;
        $this->stockItemRepository = $stockItemRepository;
        $this->criteriaFactory = $criteriaFactory;
        $this->productFactory = $productFactory;
    }

    /**
     * @param int $scopeId
     * @return \Magento\CatalogInventory\Api\Data\StockInterface
     */
    public function getStock($scopeId = null)
    {
        $scopeId = $this->getScopeId($scopeId);
        return $this->stockRegistryProvider->getStock($scopeId);
    }

    /**
     * @param int $productId
     * @param int $scopeId
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     */
    public function getStockItem($productId, $scopeId = null)
    {
        $scopeId = $this->getScopeId($scopeId);
        return $this->stockRegistryProvider->getStockItem($productId, $scopeId);
    }

    /**
     * @param string $productSku
     * @param int $scopeId
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStockItemBySku($productSku, $scopeId = null)
    {
        $scopeId = $this->getScopeId($scopeId);
        $productId = $this->resolveProductId($productSku);
        return $this->stockRegistryProvider->getStockItem($productId, $scopeId);
    }

    /**
     * @param int $productId
     * @param int $scopeId
     * @return \Magento\CatalogInventory\Api\Data\StockStatusInterface
     */
    public function getStockStatus($productId, $scopeId = null)
    {
        $scopeId = $this->getScopeId($scopeId);
        return $this->stockRegistryProvider->getStockStatus($productId, $scopeId);
    }

    /**
     * @param string $productSku
     * @param int $scopeId
     * @return \Magento\CatalogInventory\Api\Data\StockStatusInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStockStatusBySku($productSku, $scopeId = null)
    {
        $scopeId = $this->getScopeId($scopeId);
        $productId = $this->resolveProductId($productSku);
        return $this->getStockStatus($productId, $scopeId);
    }

    /**
     * Retrieve Product stock status
     *
     * @param int $productId
     * @param int $scopeId
     * @return int
     */
    public function getProductStockStatus($productId, $scopeId = null)
    {
        $scopeId = $this->getScopeId($scopeId);
        $stockStatus = $this->getStockStatus($productId, $scopeId);
        return $stockStatus->getStockStatus();
    }

    /**
     * @param string $productSku
     * @param int $scopeId
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductStockStatusBySku($productSku, $scopeId = null)
    {
        $scopeId = $this->getScopeId($scopeId);
        $productId = $this->resolveProductId($productSku);
        return $this->getProductStockStatus($productId, $scopeId);
    }

    /**
     * @inheritdoc
     */
    public function getLowStockItems($scopeId, $qty, $currentPage = 1, $pageSize = 0)
    {
        $criteria = $this->criteriaFactory->create();
        $criteria->setLimit($currentPage, $pageSize);
        $criteria->setScopeFilter($scopeId);
        $criteria->setQtyFilter('<=', $qty);
        $criteria->addField('qty');
        return $this->stockItemRepository->getList($criteria);
    }

    /**
     * @inheritdoc
     */
    public function updateStockItemBySku($productSku, \Magento\CatalogInventory\Api\Data\StockItemInterface $stockItem)
    {
        $productId = $this->resolveProductId($productSku);
        $websiteId = $stockItem->getWebsiteId() ?: null;
        $origStockItem = $this->getStockItem($productId, $websiteId);
        $data = $stockItem->getData();
        if ($origStockItem->getItemId()) {
            unset($data['item_id']);
        }
        $origStockItem->addData($data);
        $origStockItem->setProductId($productId);
        return $this->stockItemRepository->save($origStockItem)->getItemId();
    }

    /**
     * @param string $productSku
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function resolveProductId($productSku)
    {
        $product = $this->productFactory->create();
        $productId = $product->getIdBySku($productSku);
        if (!$productId) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __(
                    'The Product with the "%1" SKU doesn\'t exist.',
                    $productSku
                )
            );
        }
        return $productId;
    }

    /**
     * Return either the provided scope ID, or the default one in case of null
     *
     * @param int|null $scopeId
     * @return int
     * @throws \Magento\Framework\Exception\InputException
     */
    protected function getScopeId($scopeId)
    {
        if ($scopeId !== null) {
            if (!is_numeric($scopeId)) {
                throw new InputException(__("Expected a numeric value, got %type", ["type" => $scopeId]));
            }
            return $scopeId;
        }
        return $this->stockConfiguration->getDefaultScopeId();
    }
}
