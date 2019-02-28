<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Attribute\Backend;

use Magento\Catalog\Api\Data\MassActionInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Notification\NotifierInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Psr\Log\LoggerInterface;

/**
 * Consumer for export message.
 */
class Consumer
{
    /**
     * @var NotifierInterface
     */
    private $notifier;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\Processor
     */
    private $productFlatIndexerProcessor;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Processor
     */
    private $productPriceIndexerProcessor;

    /**
     * Catalog product
     *
     * @var \Magento\Catalog\Helper\Product
     */
    private $catalogProduct;

    /**
     * Stock Indexer
     *
     * @var \Magento\CatalogInventory\Model\Indexer\Stock\Processor
     */
    private $stockIndexerProcessor;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Catalog\Model\Product\Action
     */
    private $productAction;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterfaceFactory
     */
    private $stockRegistry;

    /**
     * @var \Magento\CatalogInventory\Api\StockItemRepositoryInterfaceFactory
     */
    private $stockItemRepository;

    /**
     * @param \Magento\Catalog\Helper\Product $catalogProduct
     * @param \Magento\Catalog\Model\Indexer\Product\Flat\Processor $productFlatIndexerProcessor
     * @param \Magento\Catalog\Model\Indexer\Product\Price\Processor $productPriceIndexerProcessor
     * @param \Magento\CatalogInventory\Model\Indexer\Stock\Processor $stockIndexerProcessor
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Catalog\Model\Product\Action $action
     * @param \Magento\CatalogInventory\Api\StockRegistryInterfaceFactory $stockRegistryFactory
     * @param \Magento\CatalogInventory\Api\StockItemRepositoryInterfaceFactory $stockItemRepositoryFactory
     * @param NotifierInterface $notifier
     * @param LoggerInterface $logger
     */
    public function __construct(
        \Magento\Catalog\Helper\Product $catalogProduct,
        \Magento\Catalog\Model\Indexer\Product\Flat\Processor $productFlatIndexerProcessor,
        \Magento\Catalog\Model\Indexer\Product\Price\Processor $productPriceIndexerProcessor,
        \Magento\CatalogInventory\Model\Indexer\Stock\Processor $stockIndexerProcessor,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Catalog\Model\Product\Action $action,
        \Magento\CatalogInventory\Api\StockRegistryInterfaceFactory $stockRegistryFactory,
        \Magento\CatalogInventory\Api\StockItemRepositoryInterfaceFactory $stockItemRepositoryFactory,
        NotifierInterface $notifier,
        LoggerInterface $logger
    ) {
        $this->catalogProduct = $catalogProduct;
        $this->productFlatIndexerProcessor = $productFlatIndexerProcessor;
        $this->productPriceIndexerProcessor = $productPriceIndexerProcessor;
        $this->stockIndexerProcessor = $stockIndexerProcessor;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->notifier = $notifier;
        $this->eventManager = $eventManager;
        $this->objectManager = ObjectManager::getInstance();
        $this->productAction = $action;
        $this->stockRegistry = $stockRegistryFactory->create();
        $this->stockItemRepository = $stockItemRepositoryFactory->create();
        $this->logger = $logger;
    }

    public function process(MassActionInterface $data): void
    {
        try {
            if ($data->getInventory()) {
                $this->updateInventoryInProducts($data->getProductIds(), $data->getWebsiteId(), $data->getInventory());
            }

            if ($data->getWebsiteAdd() || $data->getWebsiteRemove()) {
                $this->updateWebsiteInProducts($data->getProductIds(), $data->getWebsiteRemove(), $data->getWebsiteAdd());
            }

            if ($data->getAttributeValues()) {
                $attributesData = $this->getAttributesData(
                    $data->getProductIds(),
                    $data->getStoreId(),
                    $data->getAttributeValues(),
                    $data->getAttributeKeys()
                );
                $this->reindex($data->getProductIds(), $attributesData, $data->getWebsiteRemove(), $data->getWebsiteAdd());
            }

            $this->productFlatIndexerProcessor->reindexList($data->getProductIds());

            $this->notifier->addNotice(
                __('Product attributes updated'),
                __('A total of %1 record(s) were updated.', count($data->getProductIds()))
            );
        } catch (LocalizedException $exception) {
            $this->notifier->addCritical(
                __('Error during process occurred'),
                __('Error during process occurred. Please check logs for detail')
            );
            $this->logger->critical('Something went wrong while process. ' . $exception->getMessage());
        }
    }

    /**
     * @param $productIds
     * @param $storeId
     * @param $attributeValuesData
     * @param $attributeKeysData
     * @return mixed
     */
    private function getAttributesData($productIds, $storeId, $attributeValuesData, $attributeKeysData)
    {
        $attributesData = array_combine($attributeKeysData, $attributeValuesData);
        $dateFormat = $this->objectManager->get(TimezoneInterface::class)->getDateFormat(\IntlDateFormatter::SHORT);
        $config = $this->objectManager->get(\Magento\Eav\Model\Config::class);

        foreach ($attributesData as $attributeCode => $value) {
            $attribute = $config->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode);
            if (!$attribute->getAttributeId()) {
                unset($attributesData[$attributeCode]);
                continue;
            }
            if ($attribute->getBackendType() == 'datetime') {
                if (!empty($value)) {
                    $filterInput = new \Zend_Filter_LocalizedToNormalized(['date_format' => $dateFormat]);
                    $filterInternal = new \Zend_Filter_NormalizedToLocalized(
                        ['date_format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT]
                    );
                    $value = $filterInternal->filter($filterInput->filter($value));
                } else {
                    $value = null;
                }
                $attributesData[$attributeCode] = $value;
            } elseif ($attribute->getFrontendInput() == 'multiselect') {
                // Check if 'Change' checkbox has been checked by admin for this attribute
                $isChanged = (bool)$this->getRequest()->getPost('toggle_' . $attributeCode);
                if (!$isChanged) {
                    unset($attributesData[$attributeCode]);
                    continue;
                }
                if (is_array($value)) {
                    $value = implode(',', $value);
                }
                $attributesData[$attributeCode] = $value;
            }
        }

        $this->productAction->updateAttributes($productIds, $attributesData, $storeId);
        return $attributesData;
    }

    /**
     * @param $productIds
     * @param $websiteId
     * @param $inventoryData
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

    /**
     * @param $productIds
     * @param $websiteRemoveData
     * @param $websiteAddData
     */
    private function updateWebsiteInProducts($productIds, $websiteRemoveData, $websiteAddData): void
    {
        if ($websiteRemoveData) {
            $this->productAction->updateWebsites($productIds, $websiteRemoveData, 'remove');
        }
        if ($websiteAddData) {
            $this->productAction->updateWebsites($productIds, $websiteAddData, 'add');
        }

        $this->eventManager->dispatch('catalog_product_to_website_change', ['products' => $productIds]);
    }

    /**
     * @param $productIds
     * @param $attributesData
     * @param $websiteRemoveData
     * @param $websiteAddData
     */
    private function reindex($productIds, $attributesData, $websiteRemoveData, $websiteAddData): void
    {
        if ($this->catalogProduct->isDataForPriceIndexerWasChanged($attributesData)
            || !empty($websiteRemoveData)
            || !empty($websiteAddData)
        ) {
            $this->productPriceIndexerProcessor->reindexList($productIds);
        }
    }
}