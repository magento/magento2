<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Plugin;

use Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute\Save;
use Magento\CatalogInventory\Api\Data\StockItemInterface;

/**
 * MassUpdate product attribute.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MassUpdateProductAttribute
{
    /**
     * @var \Magento\CatalogInventory\Model\Indexer\Stock\Processor
     */
    private $stockIndexerProcessor;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var \Magento\CatalogInventory\Api\StockItemRepositoryInterface
     */
    private $stockItemRepository;

    /**
     * @var \Magento\CatalogInventory\Api\StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * @var \Magento\Catalog\Helper\Product\Edit\Action\Attribute
     */
    private $attributeHelper;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @param \Magento\CatalogInventory\Model\Indexer\Stock\Processor $stockIndexerProcessor
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\CatalogInventory\Api\StockItemRepositoryInterface $stockItemRepository
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
     * @param \Magento\Catalog\Helper\Product\Edit\Action\Attribute $attributeHelper
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\CatalogInventory\Model\Indexer\Stock\Processor $stockIndexerProcessor,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockItemRepositoryInterface $stockItemRepository,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\Catalog\Helper\Product\Edit\Action\Attribute $attributeHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->stockIndexerProcessor = $stockIndexerProcessor;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->stockRegistry = $stockRegistry;
        $this->stockItemRepository = $stockItemRepository;
        $this->stockConfiguration = $stockConfiguration;
        $this->attributeHelper = $attributeHelper;
        $this->messageManager = $messageManager;
    }

    /**
     * Around execute plugin
     *
     * @param Save $subject
     * @param callable $proceed
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function aroundExecute(Save $subject, callable $proceed)
    {
        try {
            /** @var \Magento\Framework\App\RequestInterface $request */
            $request = $subject->getRequest();
            $inventoryData = $request->getParam('inventory', []);
            $inventoryData = $this->addConfigSettings($inventoryData);

            $storeId = $this->attributeHelper->getSelectedStoreId();
            $websiteId = $this->attributeHelper->getStoreWebsiteId($storeId);
            $productIds = $this->attributeHelper->getProductIds();

            if (!empty($inventoryData)) {
                $this->updateInventoryInProducts($productIds, $websiteId, $inventoryData);
            }

            return $proceed();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $proceed();
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('Something went wrong while updating the product(s) attributes.')
            );
            return $proceed();
        }
    }

    /**
     * Add config settings
     *
     * @param array $inventoryData
     *
     * @return array
     */
    private function addConfigSettings($inventoryData)
    {
        $options = $this->stockConfiguration->getConfigItemOptions();
        foreach ($options as $option) {
            $useConfig = 'use_config_' . $option;
            if (isset($inventoryData[$option]) && !isset($inventoryData[$useConfig])) {
                $inventoryData[$useConfig] = 0;
            }
        }
        return $inventoryData;
    }

    /**
     * Update inventory in products
     *
     * @param array $productIds
     * @param int $websiteId
     * @param array $inventoryData
     *
     * @return void
     */
    private function updateInventoryInProducts($productIds, $websiteId, $inventoryData): void
    {
        foreach ($productIds as $productId) {
            $stockItemDo = $this->stockRegistry->getStockItem($productId, $websiteId);
            if (!$stockItemDo->getProductId()) {
                $inventoryData['product_id'] = $productId;
            }
            $stockItemId = $stockItemDo->getId();
            $this->dataObjectHelper->populateWithArray($stockItemDo, $inventoryData, StockItemInterface::class);
            $stockItemDo->setItemId($stockItemId);
            $this->stockItemRepository->save($stockItemDo);
        }
        $this->stockIndexerProcessor->reindexList($productIds);
    }
}
