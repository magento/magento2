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
 * @category    Magento
 * @package     Magento_Adminhtml
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog product controller
 *
 * @category   Magento
 * @package    Magento_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Adminhtml\Controller\Catalog;

class Product extends \Magento\Adminhtml\Controller\Action
{
    /**
     * The greatest value which could be stored in CatalogInventory Qty field
     */
    const MAX_QTY_VALUE = 99999999.9999;

    /**
     * Array of actions which can be processed without secret key validation
     *
     * @var array
     */
    protected $_publicActions = array('edit');

    /**
     * Core registry
     *
     * @var \Magento\Core\Model\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Controller\Context $context
     * @param \Magento\Core\Model\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Backend\Controller\Context $context,
        \Magento\Core\Model\Registry $coreRegistry
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Initialize product from request parameters
     *
     * @return \Magento\Catalog\Model\Product
     */
    protected function _initProduct()
    {
        $this->_title(__('Products'));

        $productId  = (int)$this->getRequest()->getParam('id');
        /** @var $product \Magento\Catalog\Model\Product */
        $product    = $this->_objectManager->create('Magento\Catalog\Model\Product')
            ->setStoreId($this->getRequest()->getParam('store', 0));

        $typeId = $this->getRequest()->getParam('type');
        if (!$productId && $typeId) {
            $product->setTypeId($typeId);
        }

        $product->setData('_edit_mode', true);
        if ($productId) {
            try {
                $product->load($productId);
            } catch (\Exception $e) {
                $product->setTypeId(\Magento\Catalog\Model\Product\Type::DEFAULT_TYPE);
                $this->_objectManager->get('Magento\Core\Model\Logger')->logException($e);
            }
        }

        $setId = (int)$this->getRequest()->getParam('set');
        if ($setId) {
            $product->setAttributeSetId($setId);
        }

        if ($this->getRequest()->has('attributes')) {
            $attributes = $this->getRequest()->getParam('attributes');
            if (!empty($attributes)) {
                $product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_CONFIGURABLE);
                $this->_objectManager->get('Magento\Catalog\Model\Product\Type\Configurable')
                    ->setUsedProductAttributeIds($attributes, $product);
            } else {
                $product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
            }
        }

        // Required attributes of simple product for configurable creation
        if ($this->getRequest()->getParam('popup')
            && $requiredAttributes = $this->getRequest()->getParam('required')) {
            $requiredAttributes = explode(",", $requiredAttributes);
            foreach ($product->getAttributes() as $attribute) {
                if (in_array($attribute->getId(), $requiredAttributes)) {
                    $attribute->setIsRequired(1);
                }
            }
        }

        if ($this->getRequest()->getParam('popup')
            && $this->getRequest()->getParam('product')
            && !is_array($this->getRequest()->getParam('product'))
            && $this->getRequest()->getParam('id', false) === false
        ) {

            $configProduct = $this->_objectManager->create('Magento\Catalog\Model\Product')
                ->setStoreId(0)
                ->load($this->getRequest()->getParam('product'))
                ->setTypeId($this->getRequest()->getParam('type'));

            /* @var $configProduct \Magento\Catalog\Model\Product */
            $data = array();
            foreach ($configProduct->getTypeInstance()->getEditableAttributes($configProduct) as $attribute) {
                /* @var $attribute \Magento\Catalog\Model\Resource\Eav\Attribute */
                if (!$attribute->getIsUnique()
                    && $attribute->getFrontend()->getInputType() != 'gallery'
                    && $attribute->getAttributeCode() != 'required_options'
                    && $attribute->getAttributeCode() != 'has_options'
                    && $attribute->getAttributeCode() != $configProduct->getIdFieldName()
                ) {
                    $data[$attribute->getAttributeCode()] = $configProduct->getData($attribute->getAttributeCode());
                }
            }
            $product->addData($data)
                ->setWebsiteIds($configProduct->getWebsiteIds());
        }

        $this->_coreRegistry->register('product', $product);
        $this->_coreRegistry->register('current_product', $product);
        $this->_objectManager->get('Magento\Cms\Model\Wysiwyg\Config')->setStoreId(
            $this->getRequest()->getParam('store')
        );
        return $product;
    }

    /**
     * Create serializer block for a grid
     *
     * @param string $inputName
     * @param \Magento\Adminhtml\Block\Widget\Grid $gridBlock
     * @param array $productsArray
     * @return \Magento\Adminhtml\Block\Catalog\Product\Edit\Tab\Ajax\Serializer
     */
    protected function _createSerializerBlock(
        $inputName,
        \Magento\Adminhtml\Block\Widget\Grid $gridBlock,
        $productsArray
    ) {
        return $this->getLayout()->createBlock('Magento\Adminhtml\Block\Catalog\Product\Edit\Tab\Ajax\Serializer')
            ->setGridBlock($gridBlock)
            ->setProducts($productsArray)
            ->setInputElementName($inputName);
    }

    /**
     * Output specified blocks as a text list
     */
    protected function _outputBlocks()
    {
        $blocks = func_get_args();
        $output = $this->getLayout()->createBlock('Magento\Adminhtml\Block\Text\ListText');
        foreach ($blocks as $block) {
            $output->insert($block, '', true);
        }
        $this->getResponse()->setBody($output->toHtml());
    }

    /**
     * Product list page
     */
    public function indexAction()
    {
        $this->_title(__('Products'));
        $this->loadLayout();
        $this->_setActiveMenu('Magento_Catalog::catalog_products');
        $this->renderLayout();
    }

    /**
     * Create new product page
     */
    public function newAction()
    {
        if (!$this->getRequest()->getParam('set')) {
            $this->_forward('noroute');
            return;
        }

        $product = $this->_initProduct();

        $productData = $this->getRequest()->getPost('product');
        if ($productData) {
            $this->_filterStockData($productData['stock_data']);
            $product->addData($productData);
        }

        $this->_title(__('New Product'));

        $this->_eventManager->dispatch('catalog_product_new_action', array('product' => $product));

        if ($this->getRequest()->getParam('popup')) {
            $this->loadLayout('popup');
        } else {
            $_additionalLayoutPart = '';
            if ($product->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_CONFIGURABLE
                && !($product->getTypeInstance()->getUsedProductAttributeIds($product))
            ) {
                $_additionalLayoutPart = '_new';
            }
            $this->loadLayout(array(
                'default',
                strtolower($this->getFullActionName()),
                'adminhtml_catalog_product_' . $product->getTypeId() . $_additionalLayoutPart
            ));
            $this->_setActiveMenu('Magento_Catalog::catalog_products');
        }

        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

        $block = $this->getLayout()->getBlock('catalog.wysiwyg.js');
        if ($block) {
            $block->setStoreId($product->getStoreId());
        }

        $this->renderLayout();
    }

    /**
     * Product edit form
     */
    public function editAction()
    {
        $productId  = (int)$this->getRequest()->getParam('id');
        $product = $this->_initProduct();

        if ($productId && !$product->getId()) {
            $this->_getSession()->addError(
                __('This product no longer exists.')
            );
            $this->_redirect('*/*/');
            return;
        }

        $this->_title($product->getName());

        $this->_eventManager->dispatch('catalog_product_edit_action', array('product' => $product));

        $additionalLayoutPart = '';
        if ($product->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_CONFIGURABLE
           && !($product->getTypeInstance()->getUsedProductAttributeIds($product))
        ) {
            $additionalLayoutPart = '_new';
        }

        $this->loadLayout(array(
            'default',
            strtolower($this->getFullActionName()),
            'adminhtml_catalog_product_'.$product->getTypeId() . $additionalLayoutPart
        ));

        $this->_setActiveMenu('Magento_Catalog::catalog_products');

        if (
            !$this->_objectManager->get('Magento\Core\Model\StoreManagerInterface')->isSingleStoreMode()
            && ($switchBlock = $this->getLayout()->getBlock('store_switcher'))
        ) {
            $switchBlock->setDefaultStoreName(__('Default Values'))
                ->setWebsiteIds($product->getWebsiteIds())
                ->setSwitchUrl(
                    $this->getUrl('*/*/*', array(
                        '_current' => true,
                        'active_tab' => null,
                        'tab' => null,
                        'store' => null
            )));
        }

        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

        $block = $this->getLayout()->getBlock('catalog.wysiwyg.js');
        if ($block) {
            $block->setStoreId($product->getStoreId());
        }

        $this->renderLayout();
    }

    /**
     * WYSIWYG editor action for ajax request
     *
     */
    public function wysiwygAction()
    {
        $elementId = $this->getRequest()->getParam('element_id', md5(microtime()));
        $storeId = $this->getRequest()->getParam('store_id', 0);
        $storeMediaUrl = $this->_objectManager->get('Magento\Core\Model\StoreManagerInterface')
            ->getStore($storeId)->getBaseUrl(\Magento\Core\Model\Store::URL_TYPE_MEDIA);

        $content = $this->getLayout()->createBlock(
            'Magento\Adminhtml\Block\Catalog\Helper\Form\Wysiwyg\Content',
            '',
            array(
                'data' => array(
                    'editor_element_id' => $elementId,
                    'store_id'          => $storeId,
                    'store_media_url'   => $storeMediaUrl,
                )
            )
        );
        $this->getResponse()->setBody($content->toHtml());
    }

    /**
     * Product grid for AJAX request
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Get specified tab grid
     */
    public function gridOnlyAction()
    {
        $this->_initProduct();
        $this->loadLayout();

        $block = $this->getRequest()->getParam('gridOnlyBlock');
        $blockClassSuffix = str_replace(' ', '_', ucwords(str_replace('_', ' ', $block)));

        $this->getResponse()->setBody(
            $this->getLayout()
                ->createBlock('Magento\Adminhtml\Block\Catalog\Product\Edit\Tab\\' . $blockClassSuffix)
                ->toHtml()
        );
    }

    /**
     * Generate product variations matrix
     */
    public function generateVariationsAction()
    {
        $this->_saveAttributeOptions();
        $this->_initProductSave($this->_initProduct());
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Save attribute options just created by user
     *
     * @TODO Move this logic to configurable product type model
     *   when full set of operations for attribute options during
     *   product creation will be implemented: edit labels, remove, reorder.
     * Currently only addition of options to end and removal of just added option is supported.
     */
    protected function _saveAttributeOptions()
    {
        $productData = (array)$this->getRequest()->getParam('product');
        if (!isset($productData['configurable_attributes_data'])) {
            return;
        }

        foreach ($productData['configurable_attributes_data'] as &$attributeData) {
            $values = array();
            foreach ($attributeData['values'] as $valueId => $priceData) {
                if (isset($priceData['label'])) {
                    /* @var $attribute \Magento\Catalog\Model\Resource\Eav\Attribute */
                    $attribute = $this->_objectManager->create('Magento\Catalog\Model\Resource\Eav\Attribute');
                    $attribute->load($attributeData['attribute_id']);
                    $optionsBefore = $attribute->getSource()->getAllOptions(false);

                    $attribute->setOption(array(
                        'value' => array('option_0' => array($priceData['label'])),
                        'order' => array('option_0' => count($optionsBefore) + 1),
                    ));
                    $attribute->save();

                    /* @var $attribute \Magento\Catalog\Model\Resource\Eav\Attribute */
                    $attribute = $this->_objectManager->create('Magento\Catalog\Model\Resource\Eav\Attribute');
                    $attribute->load($attributeData['attribute_id']);
                    $optionsAfter = $attribute->getSource()->getAllOptions(false);

                    $newOption = array_pop($optionsAfter);

                    unset($priceData['label']);
                    $valueId = $newOption['value'];
                    $priceData['value_index'] = $valueId;
                }
                $values[$valueId] = $priceData;
            }
            $attributeData['values'] = $values;
        }

        $this->getRequest()->setParam('product', $productData);
    }

    /**
     * Get categories fieldset block
     *
     */
    public function categoriesAction()
    {
        $this->_initProduct();
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Get options fieldset block
     *
     */
    public function optionsAction()
    {
        $this->_initProduct();
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Get related products grid and serializer block
     */
    public function relatedAction()
    {
        $this->_initProduct();
        $this->loadLayout();
        $this->getLayout()->getBlock('catalog.product.edit.tab.related')
            ->setProductsRelated($this->getRequest()->getPost('products_related', null));
        $this->renderLayout();
    }

    /**
     * Get upsell products grid and serializer block
     */
    public function upsellAction()
    {
        $this->_initProduct();
        $this->loadLayout();
        $this->getLayout()->getBlock('catalog.product.edit.tab.upsell')
            ->setProductsUpsell($this->getRequest()->getPost('products_upsell', null));
        $this->renderLayout();
    }

    /**
     * Get crosssell products grid and serializer block
     */
    public function crosssellAction()
    {
        $this->_initProduct();
        $this->loadLayout();
        $this->getLayout()->getBlock('catalog.product.edit.tab.crosssell')
            ->setProductsCrossSell($this->getRequest()->getPost('products_crosssell', null));
        $this->renderLayout();
    }

    /**
     * Get related products grid
     */
    public function relatedGridAction()
    {
        $this->_initProduct();
        $this->loadLayout();
        $this->getLayout()->getBlock('catalog.product.edit.tab.related')
            ->setProductsRelated($this->getRequest()->getPost('products_related', null));
        $this->renderLayout();
    }

    /**
     * Get upsell products grid
     */
    public function upsellGridAction()
    {
        $this->_initProduct();
        $this->loadLayout();
        $this->getLayout()->getBlock('catalog.product.edit.tab.upsell')
            ->setProductsRelated($this->getRequest()->getPost('products_upsell', null));
        $this->renderLayout();
    }

    /**
     * Get crosssell products grid
     */
    public function crosssellGridAction()
    {
        $this->_initProduct();
        $this->loadLayout();
        $this->getLayout()->getBlock('catalog.product.edit.tab.crosssell')
            ->setProductsRelated($this->getRequest()->getPost('products_crosssell', null));
        $this->renderLayout();
    }

    /**
     * Get associated grouped products grid
     */
    public function superGroupAction()
    {
        $this->_initProduct();
        $this->loadLayout(false);
        $this->renderLayout();
    }

    /**
     * Get associated grouped products grid popup
     */
    public function superGroupPopupAction()
    {
        $this->_initProduct();
        $this->loadLayout(false);
        $this->renderLayout();
    }

    /**
     * Get product reviews grid
     *
     */
    public function reviewsAction()
    {
        $this->_initProduct();
        $this->loadLayout();
        $this->getLayout()->getBlock('admin.product.reviews')
            ->setProductId($this->_coreRegistry->registry('product')->getId())
            ->setUseAjax(true);
        $this->renderLayout();
    }

    /**
     * Get super config grid
     *
     */
    public function superConfigAction()
    {
        $this->_initProduct();
        $this->loadLayout(false);
        $this->renderLayout();
    }

    /**
     * Validate product
     *
     */
    public function validateAction()
    {
        $response = new \Magento\Object();
        $response->setError(false);

        try {
            $productData = $this->getRequest()->getPost('product');

            if ($productData && !isset($productData['stock_data']['use_config_manage_stock'])) {
                $productData['stock_data']['use_config_manage_stock'] = 0;
            }
            /* @var $product \Magento\Catalog\Model\Product */
            $product = $this->_objectManager->create('Magento\Catalog\Model\Product');
            $product->setData('_edit_mode', true);
            $storeId = $this->getRequest()->getParam('store');
            if ($storeId) {
                $product->setStoreId($storeId);
            }
            $setId = $this->getRequest()->getParam('set');
            if ($setId) {
                $product->setAttributeSetId($setId);
            }
            $typeId = $this->getRequest()->getParam('type');
            if ($typeId) {
                $product->setTypeId($typeId);
            }
            $productId = $this->getRequest()->getParam('id');
            if ($productId) {
                $product->load($productId);
            }

            $dateFields = array();
            $attributes = $product->getAttributes();
            foreach ($attributes as $attrKey => $attribute) {
                if ($attribute->getBackend()->getType() == 'datetime') {
                    if (array_key_exists($attrKey, $productData) && $productData[$attrKey] != '') {
                        $dateFields[] = $attrKey;
                    }
                }
            }
            $productData = $this->_filterDates($productData, $dateFields);
            $product->addData($productData);

            /* set restrictions for date ranges */
            $resource = $product->getResource();
            $resource->getAttribute('special_from_date')
                ->setMaxValue($product->getSpecialToDate());
            $resource->getAttribute('news_from_date')
                ->setMaxValue($product->getNewsToDate());
            $resource->getAttribute('custom_design_from')
                ->setMaxValue($product->getCustomDesignTo());

            $variationProducts = (array)$this->getRequest()->getPost('variations-matrix');
            if ($variationProducts) {
                $validationResult = $this->_validateProductVariations($product, $variationProducts);
                if (!empty($validationResult)) {
                    $response->setError(true)
                        ->setMessage(__('Some product variations fields are not valid.'))
                        ->setAttributes($validationResult);
                }
            }
            $product->validate();
        } catch (\Magento\Eav\Model\Entity\Attribute\Exception $e) {
            $response->setError(true);
            $response->setAttribute($e->getAttributeCode());
            $response->setMessage($e->getMessage());
        } catch (\Magento\Core\Exception $e) {
            $response->setError(true);
            $response->setMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_initLayoutMessages('Magento\Adminhtml\Model\Session');
            $response->setError(true);
            $response->setMessage($this->getLayout()->getMessagesBlock()->getGroupedHtml());
        }

        $this->getResponse()->setBody($response->toJson());
    }

    /**
     * Product variations attributes validation
     *
     * @param \Magento\Catalog\Model\Product $parentProduct
     * @param array $products
     *
     * @return array
     */
    protected function _validateProductVariations($parentProduct, array $products)
    {
        $this->_eventManager->dispatch(
            'catalog_product_validate_variations_before',
            array('product' => $parentProduct, 'variations' => $products)
        );
        $validationResult = array();
        foreach ($products as $productData) {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $this->_objectManager->create('Magento\Catalog\Model\Product');
            $product->setData('_edit_mode', true);
            $storeId = $this->getRequest()->getParam('store');
            if ($storeId) {
                $product->setStoreId($storeId);
            }
            $product->setAttributeSetId($parentProduct->getAttributeSetId());

            $product->addData($productData);
            $product->setCollectExceptionMessages(true);
            $configurableAttribute = $this->_objectManager->get('Magento\Core\Helper\Data')
                ->jsonDecode($productData['configurable_attribute']);
            $configurableAttribute = implode('-', $configurableAttribute);

            $errorAttributes = $product->validate();
            if (is_array($errorAttributes)) {
                foreach ($errorAttributes as $attributeCode => $result) {
                    if (is_string($result)) {
                        $key = 'variations-matrix-' . $configurableAttribute . '-' . $attributeCode;
                        $validationResult[$key] = $result;
                    }
                }
            }
        }

        return $validationResult;
    }

    /**
     * Initialize product before saving
     *
     * @param $product \Magento\Catalog\Model\Product
     * @return \Magento\Catalog\Model\Product
     */
    protected function _initProductSave($product)
    {
        $productData = $this->getRequest()->getPost('product');
        if ($productData) {
            $this->_filterStockData($productData['stock_data']);
        }

        foreach (array('category_ids', 'website_ids') as $field) {
            if (!isset($productData[$field])) {
                $productData[$field] = array();
            }
        }

        $wasLockedMedia = false;
        if ($product->isLockedAttribute('media')) {
            $product->unlockAttribute('media');
            $wasLockedMedia = true;
        }

        $product->addData($productData);

        if ($wasLockedMedia) {
            $product->lockAttribute('media');
        }

        if ($this->_objectManager->get('Magento\Core\Model\StoreManagerInterface')->hasSingleStore()) {
            $product->setWebsiteIds(
                array(
                    $this->_objectManager->get('Magento\Core\Model\StoreManagerInterface')
                        ->getStore(true)->getWebsite()->getId()
                )
            );
        }

        /**
         * Create Permanent Redirect for old URL key
         */
        // && $product->getOrigData('url_key') != $product->getData('url_key')
        if ($product->getId() && isset($productData['url_key_create_redirect'])) {
            $product->setData('save_rewrites_history', (bool)$productData['url_key_create_redirect']);
        }

        /**
         * Check "Use Default Value" checkboxes values
         */
        $useDefaults = $this->getRequest()->getPost('use_default');
        if ($useDefaults) {
            foreach ($useDefaults as $attributeCode) {
                $product->setData($attributeCode, false);
            }
        }

        /**
         * Init product links data (related, upsell, crosssel)
         */
        $links = $this->getRequest()->getPost('links');
        if (isset($links['related']) && !$product->getRelatedReadonly()) {
            $product->setRelatedLinkData(
                $this->_objectManager->get('Magento\Adminhtml\Helper\Js')->decodeGridSerializedInput($links['related'])
            );
        }
        if (isset($links['upsell']) && !$product->getUpsellReadonly()) {
            $product->setUpSellLinkData(
                $this->_objectManager->get('Magento\Adminhtml\Helper\Js')->decodeGridSerializedInput($links['upsell'])
            );
        }
        if (isset($links['crosssell']) && !$product->getCrosssellReadonly()) {
            $product->setCrossSellLinkData($this->_objectManager->get('Magento\Adminhtml\Helper\Js')
                ->decodeGridSerializedInput($links['crosssell']));
        }

        if (isset($links['grouped']) && !$product->getGroupedReadonly()) {
            $product->setGroupedLinkData((array)$links['grouped']);
        }

        /**
         * Initialize data for configurable product
         */

        $attributes = $this->getRequest()->getParam('attributes');
        if (!empty($attributes)) {
            $this->_objectManager->get('Magento\Catalog\Model\Product\Type\Configurable')
                ->setUsedProductAttributeIds($attributes, $product);

            $product->setNewVariationsAttributeSetId($this->getRequest()->getPost('new-variations-attribute-set-id'));
            $associatedProductIds = $this->getRequest()->getPost('associated_product_ids', array());
            if ($this->getRequest()->getActionName() != 'generateVariations') {
                $generatedProductIds = $this->_objectManager->get('Magento\Catalog\Model\Product\Type\Configurable')
                    ->generateSimpleProducts($product, $this->getRequest()->getPost('variations-matrix', array()));
                $associatedProductIds = array_merge($associatedProductIds, $generatedProductIds);
            }
            $product->setAssociatedProductIds(array_filter($associatedProductIds));

            $product->setCanSaveConfigurableAttributes(
                (bool)$this->getRequest()->getPost('affect_configurable_product_attributes')
            );
        }

        /**
         * Initialize product options
         */
        if (isset($productData['options']) && !$product->getOptionsReadonly()) {
            $product->setProductOptions($productData['options']);
        }

        $product->setCanSaveCustomOptions(
            (bool)$this->getRequest()->getPost('affect_product_custom_options')
            && !$product->getOptionsReadonly()
        );

        $this->_eventManager->dispatch(
            'catalog_product_prepare_save',
            array('product' => $product, 'request' => $this->getRequest())
        );

        return $product;
    }

    /**
     * Filter product stock data
     *
     * @param array $stockData
     */
    protected function _filterStockData(&$stockData)
    {
        if (!isset($stockData['use_config_manage_stock'])) {
            $stockData['use_config_manage_stock'] = 0;
        }
        if ($stockData['use_config_manage_stock'] == 1 && !isset($stockData['manage_stock'])) {
            $stockData['manage_stock'] = $this->_objectManager->get('Magento\Core\Model\StoreManager')->getStore()
                ->getConfig(\Magento\CatalogInventory\Model\Stock\Item::XML_PATH_MANAGE_STOCK);
        }
        if (isset($stockData['qty']) && (float)$stockData['qty'] > self::MAX_QTY_VALUE) {
            $stockData['qty'] = self::MAX_QTY_VALUE;
        }
        if (isset($stockData['min_qty']) && (int)$stockData['min_qty'] < 0) {
            $stockData['min_qty'] = 0;
        }
        if (!isset($stockData['is_decimal_divided']) || $stockData['is_qty_decimal'] == 0) {
            $stockData['is_decimal_divided'] = 0;
        }
    }

    /**
     * Save product action
     */
    public function saveAction()
    {
        $storeId = $this->getRequest()->getParam('store');
        $redirectBack = $this->getRequest()->getParam('back', false);
        $productId = $this->getRequest()->getParam('id');
        $isEdit = (int)($this->getRequest()->getParam('id') != null);

        $data = $this->getRequest()->getPost();
        if ($data) {
            $this->_filterStockData($data['product']['stock_data']);

            $product = $this->_initProductSave($this->_initProduct());
            $this->_eventManager->dispatch(
                'catalog_product_transition_product_type',
                array('product' => $product, 'request' => $this->getRequest())
            );

            try {
                if (isset($data['product'][$product->getIdFieldName()])) {
                    throw new \Magento\Core\Exception(__('Unable to save product'));
                }

                $originalSku = $product->getSku();
                $product->save();
                $productId = $product->getId();

                /**
                 * Do copying data to stores
                 */
                if (isset($data['copy_to_stores'])) {
                    foreach ($data['copy_to_stores'] as $storeTo=>$storeFrom) {
                        $this->_objectManager->create('Magento\Catalog\Model\Product')
                            ->setStoreId($storeFrom)
                            ->load($productId)
                            ->setStoreId($storeTo)
                            ->save();
                    }
                }

                $this->_objectManager->create('Magento\CatalogRule\Model\Rule')->applyAllRulesToProduct($productId);

                $this->_getSession()->addSuccess(__('You saved the product.'));
                if ($product->getSku() != $originalSku) {
                    $this->_getSession()->addNotice(__('SKU for product %1 has been changed to %2.',
                            $this->_objectManager->get('Magento\Core\Helper\Data')->escapeHtml($product->getName()),
                            $this->_objectManager->get('Magento\Core\Helper\Data')->escapeHtml($product->getSku()))
                    );
                }

                $this->_eventManager->dispatch(
                    'controller_action_catalog_product_save_entity_after',
                    array('controller' => $this)
                );

                if ($redirectBack === 'duplicate') {
                    $newProduct = $product->duplicate();
                    $this->_getSession()->addSuccess(__('You duplicated the product.'));
                }

            } catch (\Magento\Core\Exception $e) {
                $this->_getSession()->addError($e->getMessage())
                    ->setProductData($data);
                $redirectBack = true;
            } catch (\Exception $e) {
                $this->_objectManager->get('Magento\Core\Model\Logger')->logException($e);
                $this->_getSession()->addError($e->getMessage());
                $redirectBack = true;
            }
        }

        if ($redirectBack === 'new') {
            $this->_redirect('*/*/new', array(
                'set'  => $product->getAttributeSetId(),
                'type' => $product->getTypeId()
            ));
        } elseif ($redirectBack === 'duplicate' && isset($newProduct)) {
            $this->_redirect(
                '*/*/edit',
                array(
                    'id' => $newProduct->getId(),
                    'back' => null,
                    '_current' => true
                )
            );
        } elseif ($redirectBack) {
            $this->_redirect('*/*/edit', array(
                'id'       => $productId,
                '_current' => true
            ));
        } elseif ($this->getRequest()->getParam('popup')) {
            $this->_redirect('*/*/created', array(
                '_current' => true,
                'id'       => $productId,
                'edit'     => $isEdit
            ));
        } else {
            $this->_redirect('*/*/', array('store'=>$storeId));
        }
    }

    /**
     * Create product duplicate
     */
    public function duplicateAction()
    {
        $product = $this->_initProduct();
        try {
            $newProduct = $product->duplicate();
            $this->_getSession()->addSuccess(__('You duplicated the product.'));
            $this->_redirect('*/*/edit', array('_current'=>true, 'id'=>$newProduct->getId()));
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Core\Model\Logger')->logException($e);
            $this->_getSession()->addError($e->getMessage());
            $this->_redirect('*/*/edit', array('_current'=>true));
        }
    }

    /**
     * Get alerts price grid
     */
    public function alertsPriceGridAction()
    {
        $this->loadLayout(false);
        $this->renderLayout();
    }

    /**
     * Get alerts stock grid
     */
    public function alertsStockGridAction()
    {
        $this->loadLayout(false);
        $this->renderLayout();
    }

    public function addAttributeAction()
    {
        $this->loadLayout('popup');
        $this->_initProduct();
        $this->_addContent(
            $this->getLayout()->createBlock('Magento\Adminhtml\Block\Catalog\Product\Attribute\NewAttribute\Product\Created')
        );
        $this->renderLayout();
    }

    public function createdAction()
    {
        $this->loadLayout('popup');
        $this->_addContent(
            $this->getLayout()->createBlock('Magento\Adminhtml\Block\Catalog\Product\Created')
        );
        $this->renderLayout();
    }

    public function massDeleteAction()
    {
        $productIds = $this->getRequest()->getParam('product');
        if (!is_array($productIds)) {
            $this->_getSession()->addError(__('Please select product(s).'));
        } else {
            if (!empty($productIds)) {
                try {
                    foreach ($productIds as $productId) {
                        $product = $this->_objectManager->get('Magento\Catalog\Model\Product')->load($productId);
                        $product->delete();
                    }
                    $this->_getSession()->addSuccess(
                        __('A total of %1 record(s) have been deleted.', count($productIds))
                    );
                } catch (\Exception $e) {
                    $this->_getSession()->addError($e->getMessage());
                }
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * Update product(s) status action
     *
     */
    public function massStatusAction()
    {
        $productIds = (array)$this->getRequest()->getParam('product');
        $storeId    = (int)$this->getRequest()->getParam('store', 0);
        $status     = (int)$this->getRequest()->getParam('status');

        try {
            $this->_validateMassStatus($productIds, $status);
            $this->_objectManager->get('Magento\Catalog\Model\Product\Action')
                ->updateAttributes($productIds, array('status' => $status), $storeId);

            $this->_getSession()->addSuccess(
                __('A total of %1 record(s) have been updated.', count($productIds))
            );
        } catch (\Magento\Core\Model\Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (\Magento\Core\Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->_getSession()
                ->addException($e, __('Something went wrong while updating the product(s) status.'));
        }

        $this->_redirect('*/*/', array('store'=> $storeId));
    }

    /**
     * Validate batch of products before theirs status will be set
     *
     * @throws \Magento\Core\Exception
     * @param  array $productIds
     * @param  int $status
     * @return void
     */
    public function _validateMassStatus(array $productIds, $status)
    {
        if ($status == \Magento\Catalog\Model\Product\Status::STATUS_ENABLED) {
            if (!$this->_objectManager->create('Magento\Catalog\Model\Product')->isProductsHasSku($productIds)) {
                throw new \Magento\Core\Exception(
                    __('Please make sure to define SKU values for all processed products.')
                );
            }
        }
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Catalog::products');
    }

    /**
     * Show item update result from updateAction
     * in Wishlist and Cart controllers.
     *
     */
    public function showUpdateResultAction()
    {
        $session = $this->_objectManager->get('Magento\Adminhtml\Model\Session');
        if ($session->hasCompositeProductResult()
            && $session->getCompositeProductResult() instanceof \Magento\Object) {
            $this->_objectManager->get('Magento\Adminhtml\Helper\Catalog\Product\Composite')
                ->renderUpdateResult($this, $session->getCompositeProductResult());
            $session->unsCompositeProductResult();
        } else {
            $session->unsCompositeProductResult();
            return false;
        }
    }

    /**
     * Show product grid for custom options import popup
     */
    public function optionsImportGridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Show custom options in JSON format for specified products
     */
    public function customOptionsAction()
    {
        $this->_coreRegistry->register('import_option_products', $this->getRequest()->getPost('products'));
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Action for product template selector
     */
    public function suggestProductTemplatesAction()
    {
        $this->_initProduct();
        $this->getResponse()->setBody($this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode(
            $this->getLayout()->createBlock('Magento\Catalog\Block\Product\TemplateSelector')
                ->getSuggestedTemplates($this->getRequest()->getParam('label_part'))
        ));
    }

    /**
     * Search for attributes by part of attribute's label in admin store
     */
    public function suggestAttributesAction()
    {
        $this->getResponse()->setBody($this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode(
            $this->getLayout()->createBlock('Magento\Adminhtml\Block\Catalog\Product\Edit\Tab\Attributes\Search')
                ->getSuggestedAttributes($this->getRequest()->getParam('label_part'))
        ));
    }

    /**
     * Add attribute to product template
     */
    public function addAttributeToTemplateAction()
    {
        $request = $this->getRequest();
        try {
            /** @var \Magento\Eav\Model\Entity\Attribute $attribute */
            $attribute = $this->_objectManager->create('Magento\Eav\Model\Entity\Attribute')
                ->load($request->getParam('attribute_id'));

            $attributeSet = $this->_objectManager->create('Magento\Eav\Model\Entity\Attribute\Set')
                ->load($request->getParam('template_id'));

            /** @var \Magento\Eav\Model\Resource\Entity\Attribute\Group\Collection $attributeGroupCollection */
            $attributeGroupCollection = $this->_objectManager
                ->get('Magento\Eav\Model\Resource\Entity\Attribute\Group\Collection');
            $attributeGroupCollection->setAttributeSetFilter($attributeSet->getId());
            $attributeGroupCollection->addFilter('attribute_group_code', $request->getParam('group'));
            $attributeGroupCollection->setPageSize(1);

            $attributeGroup = $attributeGroupCollection->getFirstItem();

            $attribute->setAttributeSetId($attributeSet->getId())->loadEntityAttributeIdBySet();

            $attribute->setEntityTypeId($attributeSet->getEntityTypeId())
                ->setAttributeSetId($request->getParam('template_id'))
                ->setAttributeGroupId($attributeGroup->getId())
                ->setSortOrder('0')
                ->save();

            $this->getResponse()->setBody($attribute->toJson());
        } catch (\Exception $e) {
            $response = new \Magento\Object();
            $response->setError(false);
            $response->setMessage($e->getMessage());
            $this->getResponse()->setBody($response->toJson());
        }
    }
}
