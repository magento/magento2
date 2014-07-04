<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Controller\Adminhtml\Product\Action;

use Magento\Backend\App\Action;

/**
 * Adminhtml catalog product action attribute update controller
 */
class Attribute extends Action
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
     * @var \Magento\CatalogInventory\Service\V1\Data\StockItemBuilder
     */
    protected $stockItemBuilder;

    /**
     * @param Action\Context $context
     * @param \Magento\Catalog\Helper\Product\Edit\Action\Attribute $helper
     * @param \Magento\Catalog\Model\Indexer\Product\Flat\Processor $productFlatIndexerProcessor
     * @param \Magento\Catalog\Model\Indexer\Product\Price\Processor $productPriceIndexerProcessor
     * @param \Magento\Catalog\Helper\Product $catalogProduct
     * @param \Magento\CatalogInventory\Service\V1\Data\StockItemBuilder $stockItemBuilder
     */
    public function __construct(
        Action\Context $context,
        \Magento\Catalog\Helper\Product\Edit\Action\Attribute $helper,
        \Magento\Catalog\Model\Indexer\Product\Flat\Processor $productFlatIndexerProcessor,
        \Magento\Catalog\Model\Indexer\Product\Price\Processor $productPriceIndexerProcessor,
        \Magento\Catalog\Helper\Product $catalogProduct,
        \Magento\CatalogInventory\Service\V1\Data\StockItemBuilder $stockItemBuilder
    ) {
        parent::__construct($context);
        $this->_helper = $helper;
        $this->_productFlatIndexerProcessor = $productFlatIndexerProcessor;
        $this->_productPriceIndexerProcessor = $productPriceIndexerProcessor;
        $this->_catalogProduct = $catalogProduct;
        $this->stockItemBuilder = $stockItemBuilder;
    }

    /**
     * @return void
     */
    public function editAction()
    {
        if (!$this->_validateProducts()) {
            return;
        }

        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }

    /**
     * Update product attributes
     *
     * @return void
     */
    public function saveAction()
    {
        if (!$this->_validateProducts()) {
            return;
        }

        /* Collect Data */
        $inventoryData = $this->getRequest()->getParam('inventory', array());
        $attributesData = $this->getRequest()->getParam('attributes', array());
        $websiteRemoveData = $this->getRequest()->getParam('remove_website_ids', array());
        $websiteAddData = $this->getRequest()->getParam('add_website_ids', array());

        /* Prepare inventory data item options (use config settings) */
        $options = $this->_objectManager->get('Magento\CatalogInventory\Helper\Data')->getConfigItemOptions();
        foreach ($options as $option) {
            if (isset($inventoryData[$option]) && !isset($inventoryData['use_config_' . $option])) {
                $inventoryData['use_config_' . $option] = 0;
            }
        }

        try {
            if ($attributesData) {
                $dateFormat = $this->_objectManager->get('Magento\Framework\Stdlib\DateTime\TimezoneInterface')
                    ->getDateFormat(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_SHORT);
                $storeId = $this->_helper->getSelectedStoreId();

                foreach ($attributesData as $attributeCode => $value) {
                    $attribute = $this->_objectManager->get('Magento\Eav\Model\Config')
                        ->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode);
                    if (!$attribute->getAttributeId()) {
                        unset($attributesData[$attributeCode]);
                        continue;
                    }
                    if ($attribute->getBackendType() == 'datetime') {
                        if (!empty($value)) {
                            $filterInput = new \Zend_Filter_LocalizedToNormalized(array('date_format' => $dateFormat));
                            $filterInternal = new \Zend_Filter_NormalizedToLocalized(
                                array('date_format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT)
                            );
                            $value = $filterInternal->filter($filterInput->filter($value));
                        } else {
                            $value = null;
                        }
                        $attributesData[$attributeCode] = $value;
                    } elseif ($attribute->getFrontendInput() == 'multiselect') {
                        // Check if 'Change' checkbox has been checked by admin for this attribute
                        $isChanged = (bool) $this->getRequest()->getPost($attributeCode . '_checkbox');
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
                    ->updateAttributes($this->_helper->getProductIds(), $attributesData, $storeId);
            }
            if ($inventoryData) {
                /** @var \Magento\CatalogInventory\Service\V1\StockItemService $stockItemService */
                $stockItemService = $this->_objectManager
                    ->create('Magento\CatalogInventory\Service\V1\StockItemService');

                foreach ($this->_helper->getProductIds() as $productId) {
                    $stockItemDo = $stockItemService->getStockItem($productId);
                    if (!$stockItemDo->getProductId()) {
                        $inventoryData[] = $productId;
                    }

                    $stockItemService->saveStockItem(
                        $this->stockItemBuilder->mergeDataObjectWithArray($stockItemDo, $inventoryData)
                    );
                }
            }

            if ($websiteAddData || $websiteRemoveData) {
                /* @var $actionModel \Magento\Catalog\Model\Product\Action */
                $actionModel = $this->_objectManager->get('Magento\Catalog\Model\Product\Action');
                $productIds = $this->_helper->getProductIds();

                if ($websiteRemoveData) {
                    $actionModel->updateWebsites($productIds, $websiteRemoveData, 'remove');
                }
                if ($websiteAddData) {
                    $actionModel->updateWebsites($productIds, $websiteAddData, 'add');
                }

                $this->_eventManager->dispatch('catalog_product_to_website_change', array('products' => $productIds));

                $this->messageManager->addNotice(
                    __(
                        'Please refresh "Catalog URL Rewrites" and "Product Attributes" in System -> ' .
                        '<a href="%1">Index Management</a>.',
                        $this->getUrl('adminhtml/process/list')
                    )
                );
            }

            $this->messageManager->addSuccess(
                __('A total of %1 record(s) were updated.', count($this->_helper->getProductIds()))
            );

            $this->_productFlatIndexerProcessor->reindexList($this->_helper->getProductIds());

            if ($this->_catalogProduct->isDataForPriceIndexerWasChanged($attributesData)
                || !empty($websiteRemoveData)
                || !empty($websiteAddData)
            ) {
                $this->_productPriceIndexerProcessor->reindexList($this->_helper->getProductIds());
            }
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException(
                $e,
                __('Something went wrong while updating the product(s) attributes.')
            );
        }

        $this->_redirect('catalog/product/', array('store' => $this->_helper->getSelectedStoreId()));
    }

    /**
     * Validate selection of products for mass update
     *
     * @return boolean
     */
    protected function _validateProducts()
    {
        $error = false;
        $productIds = $this->_helper->getProductIds();
        if (!is_array($productIds)) {
            $error = __('Please select products for attributes update.');
        } elseif (!$this->_objectManager->create('Magento\Catalog\Model\Product')->isProductsHasSku($productIds)) {
            $error = __('Please make sure to define SKU values for all processed products.');
        }

        if ($error) {
            $this->messageManager->addError($error);
            $this->_redirect('catalog/product/', array('_current' => true));
        }

        return !$error;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Catalog::update_attributes');
    }

    /**
     * Attributes validation action
     *
     * @return void
     */
    public function validateAction()
    {
        $response = new \Magento\Framework\Object();
        $response->setError(false);
        $attributesData = $this->getRequest()->getParam('attributes', array());
        $data = new \Magento\Framework\Object();

        try {
            if ($attributesData) {
                foreach ($attributesData as $attributeCode => $value) {
                    $attribute = $this->_objectManager->get('Magento\Eav\Model\Config')
                        ->getAttribute('catalog_product', $attributeCode);
                    if (!$attribute->getAttributeId()) {
                        unset($attributesData[$attributeCode]);
                        continue;
                    }
                    $data->setData($attributeCode, $value);
                    $attribute->getBackend()->validate($data);
                }
            }
        } catch (\Magento\Eav\Model\Entity\Attribute\Exception $e) {
            $response->setError(true);
            $response->setAttribute($e->getAttributeCode());
            $response->setMessage($e->getMessage());
        } catch (\Magento\Framework\Model\Exception $e) {
            $response->setError(true);
            $response->setMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException(
                $e,
                __('Something went wrong while updating the product(s) attributes.')
            );
            $this->_view->getLayout()->initMessages();
            $response->setError(true);
            $response->setHtmlMessage($this->_view->getLayout()->getMessagesBlock()->getGroupedHtml());
        }

        $this->getResponse()->representJson($response->toJson());
    }
}
