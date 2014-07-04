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
namespace Magento\Catalog\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Catalog\Model\Product\Validator;

/**
 * Catalog product controller
 */
class Product extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Processor
     */
    protected $_productPriceIndexerProcessor;

    /**
     * Array of actions which can be processed without secret key validation
     *
     * @var array
     */
    protected $_publicActions = array('edit');

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
     */
    protected $_dateFilter;

    /**
     * @var Product\Initialization\Helper
     */
    protected $initializationHelper;

    /**
     * @var Product\Initialization\StockDataFilter
     */
    protected $stockFilter;

    /**
     * @var \Magento\Catalog\Model\Product\Copier
     */
    protected $productCopier;

    /**
     * @var Product\Builder
     */
    protected $productBuilder;

    /**
     * @var \Magento\Catalog\Model\Product\TypeTransitionManager
     */
    protected $productTypeManager;

    /**
     * @var \Magento\Catalog\Model\Product\Validator
     */
    protected $productValidator;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
     * @param Product\Initialization\Helper $initializationHelper
     * @param Product\Initialization\StockDataFilter $stockFilter
     * @param \Magento\Catalog\Model\Product\Copier $productCopier
     * @param Product\Builder $productBuilder
     * @param Validator $productValidator
     * @param \Magento\Catalog\Model\Product\TypeTransitionManager $productTypeManager
     * @param \Magento\Catalog\Model\Indexer\Product\Price\Processor $productPriceIndexerProcessor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $initializationHelper,
        \Magento\Catalog\Controller\Adminhtml\Product\Initialization\StockDataFilter $stockFilter,
        \Magento\Catalog\Model\Product\Copier $productCopier,
        Product\Builder $productBuilder,
        Validator $productValidator,
        \Magento\Catalog\Model\Product\TypeTransitionManager $productTypeManager,
        \Magento\Catalog\Model\Indexer\Product\Price\Processor $productPriceIndexerProcessor
    ) {
        $this->stockFilter = $stockFilter;
        $this->initializationHelper = $initializationHelper;
        $this->registry = $registry;
        $this->_dateFilter = $dateFilter;
        $this->productCopier = $productCopier;
        $this->_productPriceIndexerProcessor = $productPriceIndexerProcessor;
        $this->productBuilder = $productBuilder;
        $this->productValidator = $productValidator;
        $this->productTypeManager = $productTypeManager;
        parent::__construct($context);
    }

    /**
     * Create serializer block for a grid
     *
     * @param string $inputName
     * @param \Magento\Backend\Block\Widget\Grid $gridBlock
     * @param array $productsArray
     * @return \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Ajax\Serializer
     */
    protected function _createSerializerBlock(
        $inputName,
        \Magento\Backend\Block\Widget\Grid $gridBlock,
        $productsArray
    ) {
        return $this->_view->getLayout()
            ->createBlock('Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Ajax\Serializer')
            ->setGridBlock($gridBlock)
            ->setProducts($productsArray)
            ->setInputElementName($inputName);
    }

    /**
     * Output specified blocks as a text list
     *
     * @return void
     */
    protected function _outputBlocks()
    {
        $blocks = func_get_args();
        $output = $this->_view->getLayout()->createBlock('Magento\Backend\Block\Text\ListText');
        foreach ($blocks as $block) {
            $output->insert($block, '', true);
        }
        $this->getResponse()->setBody($output->toHtml());
    }

    /**
     * Product list page
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_title->add(__('Products'));
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_Catalog::catalog_products');
        $this->_view->renderLayout();
    }

    /**
     * Create new product page
     *
     * @return void
     */
    public function newAction()
    {
        if (!$this->getRequest()->getParam('set')) {
            $this->_forward('noroute');
            return;
        }
        $this->_title->add(__('Products'));

        $product = $this->productBuilder->build($this->getRequest());

        $productData = $this->getRequest()->getPost('product');
        if ($productData) {
            $stockData = isset($productData['stock_data']) ? $productData['stock_data'] : array();
            $productData['stock_data'] = $this->stockFilter->filter($stockData);
            $product->addData($productData);
        }

        $this->_title->add(__('New Product'));

        $this->_eventManager->dispatch('catalog_product_new_action', array('product' => $product));

        if ($this->getRequest()->getParam('popup')) {
            $this->_view->loadLayout(array(
                'popup',
                strtolower($this->_request->getFullActionName()),
                'catalog_product_' . $product->getTypeId()
            ));
        } else {
            $this->_view->loadLayout(
                array(
                    'default',
                    strtolower($this->_request->getFullActionName()),
                    'catalog_product_' . $product->getTypeId()
                )
            );
            $this->_setActiveMenu('Magento_Catalog::catalog_products');
        }

        $this->_view->getLayout()->getBlock('head')->setCanLoadExtJs(true);

        $block = $this->_view->getLayout()->getBlock('catalog.wysiwyg.js');
        if ($block) {
            $block->setStoreId($product->getStoreId());
        }

        $this->_view->renderLayout();
    }

    /**
     * Product edit form
     *
     * @return void
     */
    public function editAction()
    {
        $this->_title->add(__('Products'));
        $productId = (int) $this->getRequest()->getParam('id');
        $product = $this->productBuilder->build($this->getRequest());

        if ($productId && !$product->getId()) {
            $this->messageManager->addError(__('This product no longer exists.'));
            $this->_redirect('catalog/*/');
            return;
        }

        $this->_title->add($product->getName());

        $this->_eventManager->dispatch('catalog_product_edit_action', array('product' => $product));

        $this->_view->loadLayout(
            array(
                'default',
                strtolower($this->_request->getFullActionName()),
                'catalog_product_' . $product->getTypeId()
            )
        );

        $this->_setActiveMenu('Magento_Catalog::catalog_products');

        if (!$this->_objectManager->get(
            'Magento\Store\Model\StoreManagerInterface'
        )->isSingleStoreMode() && ($switchBlock = $this->_view->getLayout()->getBlock(
            'store_switcher'
        ))
        ) {
            $switchBlock->setDefaultStoreName(__('Default Values'))
                ->setWebsiteIds($product->getWebsiteIds())
                ->setSwitchUrl(
                    $this->getUrl(
                        'catalog/*/*',
                        array('_current' => true, 'active_tab' => null, 'tab' => null, 'store' => null)
                    )
                );
        }

        $this->_view->getLayout()->getBlock('head')->setCanLoadExtJs(true);

        $block = $this->_view->getLayout()->getBlock('catalog.wysiwyg.js');
        if ($block) {
            $block->setStoreId($product->getStoreId());
        }

        $this->_view->renderLayout();
    }

    /**
     * WYSIWYG editor action for ajax request
     *
     * @return void
     */
    public function wysiwygAction()
    {
        $elementId = $this->getRequest()->getParam('element_id', md5(microtime()));
        $storeId = $this->getRequest()->getParam('store_id', 0);
        $storeMediaUrl = $this->_objectManager->get(
            'Magento\Store\Model\StoreManagerInterface'
        )->getStore(
            $storeId
        )->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        );

        $content = $this->_view->getLayout()->createBlock(
            'Magento\Catalog\Block\Adminhtml\Helper\Form\Wysiwyg\Content',
            '',
            array(
                'data' => array(
                    'editor_element_id' => $elementId,
                    'store_id' => $storeId,
                    'store_media_url' => $storeMediaUrl
                )
            )
        );
        $this->getResponse()->setBody($content->toHtml());
    }

    /**
     * Product grid for AJAX request
     *
     * @return void
     */
    public function gridAction()
    {
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }

    /**
     * Get specified tab grid
     *
     * @return void
     */
    public function gridOnlyAction()
    {
        $this->_title->add(__('Products'));

        $this->productBuilder->build($this->getRequest());
        $this->_view->loadLayout();

        $block = $this->getRequest()->getParam('gridOnlyBlock');
        $blockClassSuffix = str_replace(' ', '_', ucwords(str_replace('_', ' ', $block)));

        $this->getResponse()->setBody(
            $this->_view->getLayout()->createBlock(
                'Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\\' . $blockClassSuffix
            )->toHtml()
        );
    }

    /**
     * Get categories fieldset block
     *
     * @return void
     */
    public function categoriesAction()
    {
        $this->productBuilder->build($this->getRequest());
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }

    /**
     * Get options fieldset block
     *
     * @return void
     */
    public function optionsAction()
    {
        $this->productBuilder->build($this->getRequest());
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }

    /**
     * Get related products grid and serializer block
     *
     * @return void
     */
    public function relatedAction()
    {
        $this->productBuilder->build($this->getRequest());
        $this->_view->loadLayout();
        $this->_view->getLayout()->getBlock('catalog.product.edit.tab.related')
            ->setProductsRelated($this->getRequest()->getPost('products_related', null));
        $this->_view->renderLayout();
    }

    /**
     * Get upsell products grid and serializer block
     *
     * @return void
     */
    public function upsellAction()
    {
        $this->productBuilder->build($this->getRequest());
        $this->_view->loadLayout();
        $this->_view->getLayout()->getBlock('catalog.product.edit.tab.upsell')
            ->setProductsUpsell($this->getRequest()->getPost('products_upsell', null));
        $this->_view->renderLayout();
    }

    /**
     * Get crosssell products grid and serializer block
     *
     * @return void
     */
    public function crosssellAction()
    {
        $this->productBuilder->build($this->getRequest());
        $this->_view->loadLayout();
        $this->_view->getLayout()->getBlock('catalog.product.edit.tab.crosssell')
            ->setProductsCrossSell($this->getRequest()->getPost('products_crosssell', null));
        $this->_view->renderLayout();
    }

    /**
     * Get related products grid
     *
     * @return void
     */
    public function relatedGridAction()
    {
        $this->productBuilder->build($this->getRequest());
        $this->_view->loadLayout();
        $this->_view->getLayout()->getBlock('catalog.product.edit.tab.related')
            ->setProductsRelated($this->getRequest()->getPost('products_related', null));
        $this->_view->renderLayout();
    }

    /**
     * Get upsell products grid
     *
     * @return void
     */
    public function upsellGridAction()
    {
        $this->productBuilder->build($this->getRequest());
        $this->_view->loadLayout();
        $this->_view->getLayout()->getBlock('catalog.product.edit.tab.upsell')
            ->setProductsRelated($this->getRequest()->getPost('products_upsell', null));
        $this->_view->renderLayout();
    }

    /**
     * Get crosssell products grid
     *
     * @return void
     */
    public function crosssellGridAction()
    {
        $this->productBuilder->build($this->getRequest());
        $this->_view->loadLayout();
        $this->_view->getLayout()->getBlock('catalog.product.edit.tab.crosssell')
            ->setProductsRelated($this->getRequest()->getPost('products_crosssell', null));
        $this->_view->renderLayout();
    }

    /**
     * Validate product
     *
     * @return void
     */
    public function validateAction()
    {
        $response = new \Magento\Framework\Object();
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

            $dateFieldFilters = array();
            $attributes = $product->getAttributes();
            foreach ($attributes as $attrKey => $attribute) {
                if ($attribute->getBackend()->getType() == 'datetime') {
                    if (array_key_exists($attrKey, $productData) && $productData[$attrKey] != '') {
                        $dateFieldFilters[$attrKey] = $this->_dateFilter;
                    }
                }
            }
            $inputFilter = new \Zend_Filter_Input($dateFieldFilters, array(), $productData);
            $productData = $inputFilter->getUnescaped();
            $product->addData($productData);

            /* set restrictions for date ranges */
            $resource = $product->getResource();
            $resource->getAttribute('special_from_date')->setMaxValue($product->getSpecialToDate());
            $resource->getAttribute('news_from_date')->setMaxValue($product->getNewsToDate());
            $resource->getAttribute('custom_design_from')->setMaxValue($product->getCustomDesignTo());

            $this->productValidator->validate($product, $this->getRequest(), $response);
        } catch (\Magento\Eav\Model\Entity\Attribute\Exception $e) {
            $response->setError(true);
            $response->setAttribute($e->getAttributeCode());
            $response->setMessage($e->getMessage());
        } catch (\Magento\Framework\Model\Exception $e) {
            $response->setError(true);
            $response->setMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_view->getLayout()->initMessages();
            $response->setError(true);
            $response->setHtmlMessage($this->_view->getLayout()->getMessagesBlock()->getGroupedHtml());
        }

        $this->getResponse()->representJson($response->toJson());
    }

    /**
     * Save product action
     *
     * @return void
     */
    public function saveAction()
    {
        $storeId = $this->getRequest()->getParam('store');
        $redirectBack = $this->getRequest()->getParam('back', false);
        $productId = $this->getRequest()->getParam('id');

        $data = $this->getRequest()->getPost();
        if ($data) {
            $product = $this->initializationHelper->initialize($this->productBuilder->build($this->getRequest()));
            $this->productTypeManager->processProduct($product);

            try {
                if (isset($data['product'][$product->getIdFieldName()])) {
                    throw new \Magento\Framework\Model\Exception(__('Unable to save product'));
                }

                $originalSku = $product->getSku();
                $product->save();
                $productId = $product->getId();

                /**
                 * Do copying data to stores
                 */
                if (isset($data['copy_to_stores'])) {
                    foreach ($data['copy_to_stores'] as $storeTo => $storeFrom) {
                        $this->_objectManager->create('Magento\Catalog\Model\Product')
                            ->setStoreId($storeFrom)
                            ->load($productId)
                            ->setStoreId($storeTo)
                            ->save();
                    }
                }

                $this->_objectManager->create('Magento\CatalogRule\Model\Rule')->applyAllRulesToProduct($productId);

                $this->messageManager->addSuccess(__('You saved the product.'));
                if ($product->getSku() != $originalSku) {
                    $this->messageManager->addNotice(
                        __(
                            'SKU for product %1 has been changed to %2.',
                            $this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($product->getName()),
                            $this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($product->getSku())
                        )
                    );
                }

                $this->_eventManager->dispatch(
                    'controller_action_catalog_product_save_entity_after',
                    array('controller' => $this)
                );

                if ($redirectBack === 'duplicate') {
                    $newProduct = $this->productCopier->copy($product);
                    $this->messageManager->addSuccess(__('You duplicated the product.'));
                }
            } catch (\Magento\Framework\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_session->setProductData($data);
                $redirectBack = true;
            } catch (\Exception $e) {
                $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
                $this->messageManager->addError($e->getMessage());
                $redirectBack = true;
            }
        }

        if ($redirectBack === 'new') {
            $this->_redirect(
                'catalog/*/new',
                array('set' => $product->getAttributeSetId(), 'type' => $product->getTypeId())
            );
        } elseif ($redirectBack === 'duplicate' && isset($newProduct)) {
            $this->_redirect(
                'catalog/*/edit',
                array('id' => $newProduct->getId(), 'back' => null, '_current' => true)
            );
        } elseif ($redirectBack) {
            $this->_redirect('catalog/*/edit', array('id' => $productId, '_current' => true));
        } else {
            $this->_redirect('catalog/*/', array('store' => $storeId));
        }
    }

    /**
     * Create product duplicate
     *
     * @return void
     */
    public function duplicateAction()
    {
        $product = $this->productBuilder->build($this->getRequest());
        try {
            $newProduct = $this->productCopier->copy($product);
            $this->messageManager->addSuccess(__('You duplicated the product.'));
            $this->_redirect('catalog/*/edit', array('_current' => true, 'id' => $newProduct->getId()));
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
            $this->messageManager->addError($e->getMessage());
            $this->_redirect('catalog/*/edit', array('_current' => true));
        }
    }

    /**
     * Get alerts price grid
     *
     * @return void
     */
    public function alertsPriceGridAction()
    {
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }

    /**
     * Get alerts stock grid
     *
     * @return void
     */
    public function alertsStockGridAction()
    {
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }

    /**
     * @return void
     */
    public function massDeleteAction()
    {
        $productIds = $this->getRequest()->getParam('product');
        if (!is_array($productIds) || empty($productIds)) {
            $this->messageManager->addError(__('Please select product(s).'));
        } else {
            try {
                foreach ($productIds as $productId) {
                    $product = $this->_objectManager->get('Magento\Catalog\Model\Product')->load($productId);
                    $product->delete();
                }
                $this->messageManager->addSuccess(
                    __('A total of %1 record(s) have been deleted.', count($productIds))
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        $this->_redirect('catalog/*/index');
    }

    /**
     * Update product(s) status action
     *
     * @return void
     */
    public function massStatusAction()
    {
        $productIds = (array) $this->getRequest()->getParam('product');
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        $status = (int) $this->getRequest()->getParam('status');

        try {
            $this->_validateMassStatus($productIds, $status);
            $this->_objectManager->get('Magento\Catalog\Model\Product\Action')
                ->updateAttributes($productIds, array('status' => $status), $storeId);
            $this->messageManager->addSuccess(__('A total of %1 record(s) have been updated.', count($productIds)));
            $this->_productPriceIndexerProcessor->reindexList($productIds);
        } catch (\Magento\Core\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->_getSession()->addException($e, __('Something went wrong while updating the product(s) status.'));
        }

        $this->_redirect('catalog/*/', array('store' => $storeId));
    }

    /**
     * Validate batch of products before theirs status will be set
     *
     * @param array $productIds
     * @param int $status
     * @return void
     * @throws \Magento\Framework\Model\Exception
     */
    public function _validateMassStatus(array $productIds, $status)
    {
        if ($status == \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED) {
            if (!$this->_objectManager->create('Magento\Catalog\Model\Product')->isProductsHasSku($productIds)) {
                throw new \Magento\Framework\Model\Exception(
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
     * @return bool
     */
    public function showUpdateResultAction()
    {
        $session = $this->_objectManager->get('Magento\Backend\Model\Session');
        if ($session->hasCompositeProductResult()
            && $session->getCompositeProductResult() instanceof \Magento\Framework\Object
        ) {
            $this->_objectManager->get('Magento\Catalog\Helper\Product\Composite')
                ->renderUpdateResult($session->getCompositeProductResult());
            $session->unsCompositeProductResult();
        } else {
            $session->unsCompositeProductResult();
            return false;
        }
    }

    /**
     * Show product grid for custom options import popup
     *
     * @return void
     */
    public function optionsImportGridAction()
    {
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }

    /**
     * Show custom options in JSON format for specified products
     *
     * @return void
     */
    public function customOptionsAction()
    {
        $this->registry->register('import_option_products', $this->getRequest()->getPost('products'));
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }

    /**
     * Action for product template selector
     *
     * @return void
     */
    public function suggestProductTemplatesAction()
    {
        $this->productBuilder->build($this->getRequest());
        $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode(
                $this->_view->getLayout()->createBlock('Magento\Catalog\Block\Product\TemplateSelector')
                    ->getSuggestedTemplates($this->getRequest()->getParam('label_part'))
            )
        );
    }

    /**
     * Search for attributes by part of attribute's label in admin store
     *
     * @return void
     */
    public function suggestAttributesAction()
    {
        $this->getResponse()->srepresentJson(
            $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode(
                $this->_view->getLayout()->createBlock(
                    'Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes\Search'
                )->getSuggestedAttributes(
                    $this->getRequest()->getParam('label_part')
                )
            )
        );
    }

    /**
     * Add attribute to product template
     *
     * @return void
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
            $attributeGroupCollection = $this->_objectManager->get(
                'Magento\Eav\Model\Resource\Entity\Attribute\Group\Collection'
            );
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

            $this->getResponse()->representJson($attribute->toJson());
        } catch (\Exception $e) {
            $response = new \Magento\Framework\Object();
            $response->setError(false);
            $response->setMessage($e->getMessage());
            $this->getResponse()->representJson($response->toJson());
        }
    }
}
