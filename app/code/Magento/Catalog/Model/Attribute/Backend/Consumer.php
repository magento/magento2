<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Attribute\Backend;

use Magento\Catalog\Api\Data\MassActionInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Notification\NotifierInterface;
use Magento\Framework\App\ObjectManager;

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
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\Processor
     */
    protected $_productFlatIndexerProcessor;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Processor
     */
    protected $_productPriceIndexerProcessor;

    /**
     * Catalog product
     *
     * @var \Magento\Catalog\Helper\Product
     */
    protected $_catalogProduct;

    /**
     * @var \Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory
     */
    protected $stockItemFactory;

    /**
     * Stock Indexer
     *
     * @var \Magento\CatalogInventory\Model\Indexer\Stock\Processor
     */
    protected $_stockIndexerProcessor;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $_eventManager;

    /**
     * @var ObjectManager
     */
    private $_objectManager;

    /**
     * @param \Magento\Catalog\Helper\Product $catalogProduct
     * @param \Magento\Catalog\Model\Indexer\Product\Flat\Processor $productFlatIndexerProcessor
     * @param \Magento\Catalog\Model\Indexer\Product\Price\Processor $productPriceIndexerProcessor
     * @param \Magento\CatalogInventory\Model\Indexer\Stock\Processor $stockIndexerProcessor
     * @param \Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory $stockItemFactory
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param NotifierInterface $notifier
     */
    public function __construct(
        \Magento\Catalog\Helper\Product $catalogProduct,
        \Magento\Catalog\Model\Indexer\Product\Flat\Processor $productFlatIndexerProcessor,
        \Magento\Catalog\Model\Indexer\Product\Price\Processor $productPriceIndexerProcessor,
        \Magento\CatalogInventory\Model\Indexer\Stock\Processor $stockIndexerProcessor,
        \Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory $stockItemFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        NotifierInterface $notifier
    ) {
        $this->_catalogProduct = $catalogProduct;
        $this->_productFlatIndexerProcessor = $productFlatIndexerProcessor;
        $this->_productPriceIndexerProcessor = $productPriceIndexerProcessor;
        $this->_stockIndexerProcessor = $stockIndexerProcessor;
        $this->stockItemFactory = $stockItemFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->notifier = $notifier;
        $this->_eventManager = $eventManager;
        $this->_objectManager = ObjectManager::getInstance();
    }

    public function process(MassActionInterface $data): void
    {
        try {
            if ($data->getAttributes()) {
                $attributesData = $this->getAttributesData($data, $data->getAttributes());
            }

            if ($data->getInventory()) {
                $this->saveInventory($data, $data->getInventory());
            }

            if ($data->getWebsiteAdd() || $data->getWebsiteRemove()) {
                $this->updateWebsiteInProducts($data, $data->getWebsiteRemove(), $data->getWebsiteAdd());
            }

            $this->reindex($data, $attributesData, $data->getWebsiteRemove(), $data->getWebsiteAdd());

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
     * @param MassActionInterface $data
     * @param $attributesData
     * @return mixed
     */
    private function getAttributesData(MassActionInterface $data, $attributesData)
    {
        $dateFormat = $this->_objectManager->get(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class)
            ->getDateFormat(\IntlDateFormatter::SHORT);

        foreach ($attributesData as $attributeCode => $value) {
            $attribute = $this->_objectManager->get(\Magento\Eav\Model\Config::class)
                ->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode);
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

        $this->_objectManager->get(\Magento\Catalog\Model\Product\Action::class)
            ->updateAttributes($data->getProductIds(), $attributesData, $data->getStoreId());
        return $attributesData;
    }

    /**
     * @param MassActionInterface $data
     * @param $inventoryData
     */
    private function saveInventory(MassActionInterface $data, $inventoryData): void
    {
        // TODO why use ObjectManager?
        /** @var \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry */
        $stockRegistry = $this->_objectManager
            ->create(\Magento\CatalogInventory\Api\StockRegistryInterface::class);
        /** @var \Magento\CatalogInventory\Api\StockItemRepositoryInterface $stockItemRepository */
        $stockItemRepository = $this->_objectManager
            ->create(\Magento\CatalogInventory\Api\StockItemRepositoryInterface::class);
        foreach ($data->getProductIds() as $productId) {
            $stockItemDo = $stockRegistry->getStockItem(
                $productId,
                $data->getWebsiteId()
            );
            if (!$stockItemDo->getProductId()) {
                $inventoryData['product_id'] = $productId;
            }

            $stockItemId = $stockItemDo->getId();
            $this->dataObjectHelper->populateWithArray(
                $stockItemDo,
                $inventoryData,
                \Magento\CatalogInventory\Api\Data\StockItemInterface::class
            );
            $stockItemDo->setItemId($stockItemId);
            $stockItemRepository->save($stockItemDo);
        }
        $this->_stockIndexerProcessor->reindexList($data->getProductIds());
    }

    /**
     * @param MassActionInterface $data
     * @param $websiteRemoveData
     * @param $websiteAddData
     */
    private function updateWebsiteInProducts(MassActionInterface $data, $websiteRemoveData, $websiteAddData): void
    {
        /* @var $actionModel \Magento\Catalog\Model\Product\Action */
        $actionModel = $this->_objectManager->get(\Magento\Catalog\Model\Product\Action::class);
        $productIds = $data->getProductIds();

        if ($websiteRemoveData) {
            $actionModel->updateWebsites($productIds, $websiteRemoveData, 'remove');
        }
        if ($websiteAddData) {
            $actionModel->updateWebsites($productIds, $websiteAddData, 'add');
        }

        $this->_eventManager->dispatch('catalog_product_to_website_change', ['products' => $productIds]);
    }

    /**
     * @param MassActionInterface $data
     * @param $attributesData
     * @param $websiteRemoveData
     * @param $websiteAddData
     */
    private function reindex(MassActionInterface $data, $attributesData, $websiteRemoveData, $websiteAddData): void
    {
        $this->_productFlatIndexerProcessor->reindexList($data->getProductIds());

        if ($this->_catalogProduct->isDataForPriceIndexerWasChanged($attributesData)
            || !empty($websiteRemoveData)
            || !empty($websiteAddData)
        ) {
            $this->_productPriceIndexerProcessor->reindexList($data->getProductIds());
        }
    }
}