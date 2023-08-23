<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Plugin;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute\Save;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Observer\ParentItemProcessorInterface;

/**
 * Around plugin for MassUpdate product attribute via product grid.
 *
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
     * @var ParentItemProcessorInterface[]
     */
    private $parentItemProcessorPool;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var array
     */
    private $useConfigFieldMap = [
        'enable_qty_increments' => 'use_config_enable_qty_inc'
    ];

    /**
     * @param \Magento\CatalogInventory\Model\Indexer\Stock\Processor $stockIndexerProcessor
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\CatalogInventory\Api\StockItemRepositoryInterface $stockItemRepository
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
     * @param \Magento\Catalog\Helper\Product\Edit\Action\Attribute $attributeHelper
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param ProductRepositoryInterface $productRepository
     * @param ParentItemProcessorInterface[] $parentItemProcessorPool
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\CatalogInventory\Model\Indexer\Stock\Processor $stockIndexerProcessor,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockItemRepositoryInterface $stockItemRepository,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\Catalog\Helper\Product\Edit\Action\Attribute $attributeHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        ProductRepositoryInterface $productRepository,
        array $parentItemProcessorPool = []
    ) {
        $this->stockIndexerProcessor = $stockIndexerProcessor;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->stockRegistry = $stockRegistry;
        $this->stockItemRepository = $stockItemRepository;
        $this->stockConfiguration = $stockConfiguration;
        $this->attributeHelper = $attributeHelper;
        $this->messageManager = $messageManager;
        $this->productRepository = $productRepository;
        $this->parentItemProcessorPool = $parentItemProcessorPool;
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
            $useConfig = isset($this->useConfigFieldMap[$option])
                ? $this->useConfigFieldMap[$option]
                : 'use_config_' . $option;
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
            $product = $this->productRepository->getById($productId);
            $stockItemDo = $this->stockRegistry->getStockItem($productId, $websiteId);
            if (!$stockItemDo->getProductId()) {
                $inventoryData['product_id'] = $productId;
            }
            $stockItemId = $stockItemDo->getId();
            $this->dataObjectHelper->populateWithArray($stockItemDo, $inventoryData, StockItemInterface::class);
            $stockItemDo->setItemId($stockItemId);
            $this->stockItemRepository->save($stockItemDo);
            $this->processParents($product);
        }
        $this->stockIndexerProcessor->reindexList($productIds);
    }

    /**
     * Process stock data for parent products
     *
     * @param ProductInterface $product
     * @return void
     */
    private function processParents(ProductInterface $product): void
    {
        foreach ($this->parentItemProcessorPool as $processor) {
            $processor->process($product);
        }
    }
}
