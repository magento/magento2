<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute;

use Magento\Backend\App\Action;

/**
 * Class Save
 */
class Save extends \Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute
{
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
     * @var \Magento\CatalogInventory\Api\Data\StockItemDataBuilder
     */
    protected $stockItemBuilder;

    /**
     * Stock Indexer
     *
     * @var \Magento\CatalogInventory\Model\Indexer\Stock\Processor
     */
    protected $_stockIndexerProcessor;

    /**
     * @var \Magento\Backend\Model\View\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @param Action\Context $context
     * @param \Magento\Catalog\Helper\Product\Edit\Action\Attribute $attributeHelper
     * @param \Magento\Catalog\Model\Indexer\Product\Flat\Processor $productFlatIndexerProcessor
     * @param \Magento\Catalog\Model\Indexer\Product\Price\Processor $productPriceIndexerProcessor
     * @param \Magento\CatalogInventory\Model\Indexer\Stock\Processor $stockIndexerProcessor
     * @param \Magento\Catalog\Helper\Product $catalogProduct
     * @param \Magento\CatalogInventory\Api\Data\StockItemDataBuilder $stockItemBuilder
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     */
    public function __construct(
        Action\Context $context,
        \Magento\Catalog\Helper\Product\Edit\Action\Attribute $attributeHelper,
        \Magento\Catalog\Model\Indexer\Product\Flat\Processor $productFlatIndexerProcessor,
        \Magento\Catalog\Model\Indexer\Product\Price\Processor $productPriceIndexerProcessor,
        \Magento\CatalogInventory\Model\Indexer\Stock\Processor $stockIndexerProcessor,
        \Magento\Catalog\Helper\Product $catalogProduct,
        \Magento\CatalogInventory\Api\Data\StockItemDataBuilder $stockItemBuilder,
        \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
    ) {
        $this->_productFlatIndexerProcessor = $productFlatIndexerProcessor;
        $this->_productPriceIndexerProcessor = $productPriceIndexerProcessor;
        $this->_stockIndexerProcessor = $stockIndexerProcessor;
        $this->_catalogProduct = $catalogProduct;
        $this->stockItemBuilder = $stockItemBuilder;
        parent::__construct($context, $attributeHelper);
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    /**
     * Update product attributes
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        if (!$this->_validateProducts()) {
            return $this->resultRedirectFactory->create()->setPath('catalog/product/', ['_current' => true]);
        }

        /* Collect Data */
        $inventoryData = $this->getRequest()->getParam('inventory', []);
        $attributesData = $this->getRequest()->getParam('attributes', []);
        $websiteRemoveData = $this->getRequest()->getParam('remove_website_ids', []);
        $websiteAddData = $this->getRequest()->getParam('add_website_ids', []);

        /* Prepare inventory data item options (use config settings) */
        $options = $this->_objectManager->get('Magento\CatalogInventory\Api\StockConfigurationInterface')
            ->getConfigItemOptions();
        foreach ($options as $option) {
            if (isset($inventoryData[$option]) && !isset($inventoryData['use_config_' . $option])) {
                $inventoryData['use_config_' . $option] = 0;
            }
        }

        try {
            $storeId = $this->attributeHelper->getSelectedStoreId();
            if ($attributesData) {
                $dateFormat = $this->_objectManager->get('Magento\Framework\Stdlib\DateTime\TimezoneInterface')
                    ->getDateFormat(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_SHORT);

                foreach ($attributesData as $attributeCode => $value) {
                    $attribute = $this->_objectManager->get('Magento\Eav\Model\Config')
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
                        $isChanged = (bool)$this->getRequest()->getPost($attributeCode . '_checkbox');
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

                $this->_objectManager->get('Magento\Catalog\Model\Product\Action')
                    ->updateAttributes($this->attributeHelper->getProductIds(), $attributesData, $storeId);
            }

            if ($inventoryData) {
                // TODO why use ObjectManager?
                /** @var \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry */
                $stockRegistry = $this->_objectManager
                    ->create('Magento\CatalogInventory\Api\StockRegistryInterface');
                /** @var \Magento\CatalogInventory\Api\StockItemRepositoryInterface $stockItemRepository */
                $stockItemRepository = $this->_objectManager
                    ->create('Magento\CatalogInventory\Api\StockItemRepositoryInterface');
                foreach ($this->attributeHelper->getProductIds() as $productId) {
                    $stockItemDo = $stockRegistry->getStockItem(
                        $productId,
                        $this->attributeHelper->getStoreWebsiteId($storeId)
                    );
                    if (!$stockItemDo->getProductId()) {
                        $inventoryData[] = $productId;
                    }

                    $stockItemId = $stockItemDo->getId();
                    $stockItemBuilder = $this->stockItemBuilder->mergeDataObjectWithArray($stockItemDo, $inventoryData);
                    $stockItemToSave = $stockItemBuilder->create();
                    $stockItemToSave->setItemId($stockItemId);
                    $stockItemRepository->save($stockItemToSave);
                }
                $this->_stockIndexerProcessor->reindexList($this->attributeHelper->getProductIds());
            }

            if ($websiteAddData || $websiteRemoveData) {
                /* @var $actionModel \Magento\Catalog\Model\Product\Action */
                $actionModel = $this->_objectManager->get('Magento\Catalog\Model\Product\Action');
                $productIds = $this->attributeHelper->getProductIds();

                if ($websiteRemoveData) {
                    $actionModel->updateWebsites($productIds, $websiteRemoveData, 'remove');
                }
                if ($websiteAddData) {
                    $actionModel->updateWebsites($productIds, $websiteAddData, 'add');
                }

                $this->_eventManager->dispatch('catalog_product_to_website_change', ['products' => $productIds]);
            }

            $this->messageManager->addSuccess(
                __('A total of %1 record(s) were updated.', count($this->attributeHelper->getProductIds()))
            );

            $this->_productFlatIndexerProcessor->reindexList($this->attributeHelper->getProductIds());

            if ($this->_catalogProduct->isDataForPriceIndexerWasChanged($attributesData)
                || !empty($websiteRemoveData)
                || !empty($websiteAddData)
            ) {
                $this->_productPriceIndexerProcessor->reindexList($this->attributeHelper->getProductIds());
            }
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException(
                $e,
                __('Something went wrong while updating the product(s) attributes.')
            );
        }

        return $this->resultRedirectFactory->create()
            ->setPath('catalog/product/', ['store' => $this->attributeHelper->getSelectedStoreId()]);
    }
}
