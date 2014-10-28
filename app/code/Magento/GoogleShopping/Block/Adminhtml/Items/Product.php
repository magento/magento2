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
namespace Magento\GoogleShopping\Block\Adminhtml\Items;

/**
 * Products Grid to add to Google Content
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Product extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Product type
     *
     * @var \Magento\Catalog\Model\Product\Type
     */
    protected $_productType;

    /**
     * Product factory
     *
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * EAV attribute set collection factory
     *
     * @var \Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory
     */
    protected $_eavCollectionFactory;

    /**
     * Item collection factory
     *
     * @var \Magento\GoogleShopping\Model\Resource\Item\CollectionFactory
     */
    protected $_itemCollectionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\GoogleShopping\Model\Resource\Item\CollectionFactory $itemCollectionFactory
     * @param \Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory $eavCollectionFactory
     * @param \Magento\Catalog\Model\Product\Type $productType
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\GoogleShopping\Model\Resource\Item\CollectionFactory $itemCollectionFactory,
        \Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory $eavCollectionFactory,
        \Magento\Catalog\Model\Product\Type $productType,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        array $data = array()
    ) {
        $this->_itemCollectionFactory = $itemCollectionFactory;
        $this->_eavCollectionFactory = $eavCollectionFactory;
        $this->_productType = $productType;
        $this->_productFactory = $productFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('googleshopping_selection_search_grid');
        $this->setDefaultSort('id');
        $this->setUseAjax(true);
    }

    /**
     * Before rendering html, but after trying to load cache
     *
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->setId($this->getId() . '_' . $this->getIndex());
        $this->getChildBlock('reset_filter_button')->setData('onclick', $this->getJsObjectName() . '.resetFilter()');
        $this->getChildBlock('search_button')->setData('onclick', $this->getJsObjectName() . '.doFilter()');
        return parent::_beforeToHtml();
    }

    /**
     * Prepare grid collection object
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_productFactory->create()->getCollection()->setStore(
            $this->_getStore()
        )->addAttributeToSelect(
            'name'
        )->addAttributeToSelect(
            'sku'
        )->addAttributeToSelect(
            'price'
        )->addAttributeToSelect(
            'attribute_set_id'
        );

        $store = $this->_getStore();
        if ($store->getId()) {
            $collection->addStoreFilter($store);
        }

        $excludeIds = $this->_getGoogleShoppingProductIds();
        if ($excludeIds) {
            $collection->addIdFilter($excludeIds, true);
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare grid columns
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            array('header' => __('ID'), 'sortable' => true, 'width' => '60px', 'index' => 'entity_id')
        );
        $this->addColumn('name', array('header' => __('Product'), 'index' => 'name', 'column_css_class' => 'name'));

        $sets = $this->_eavCollectionFactory->create()->setEntityTypeFilter(
            $this->_productFactory->create()->getResource()->getTypeId()
        )->load()->toOptionHash();

        $this->addColumn(
            'type',
            array(
                'header' => __('Type'),
                'width' => '60px',
                'index' => 'type_id',
                'type' => 'options',
                'options' => $this->_productType->getOptionArray()
            )
        );

        $this->addColumn(
            'set_name',
            array(
                'header' => __('Attribute Set'),
                'width' => '100px',
                'index' => 'attribute_set_id',
                'type' => 'options',
                'options' => $sets
            )
        );

        $this->addColumn(
            'sku',
            array('header' => __('SKU'), 'width' => '80px', 'index' => 'sku', 'column_css_class' => 'sku')
        );
        $this->addColumn(
            'price',
            array(
                'header' => __('Price'),
                'align' => 'center',
                'type' => 'currency',
                'currency_code' => $this->_getStore()->getDefaultCurrencyCode(),
                'rate' => $this->_getStore()->getBaseCurrency()->getRate($this->_getStore()->getDefaultCurrencyCode()),
                'index' => 'price'
            )
        );

        return parent::_prepareColumns();
    }

    /**
     * Prepare grid massaction actions
     *
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('product');

        $this->getMassactionBlock()->addItem(
            'add',
            array(
                'label' => __('Add to Google Content'),
                'url' => $this->getUrl('adminhtml/*/massAdd', array('_current' => true))
            )
        );
        return $this;
    }

    /**
     * Grid url getter
     *
     * @return string current grid url
     */
    public function getGridUrl()
    {
        return $this->getUrl(
            'adminhtml/googleshopping_selection/grid',
            array('index' => $this->getIndex(), '_current' => true)
        );
    }

    /**
     * Get array with product ids, which was exported to Google Content
     *
     * @return int[]
     */
    protected function _getGoogleShoppingProductIds()
    {
        $collection = $this->_itemCollectionFactory->create()->addStoreFilter($this->_getStore()->getId())->load();
        $productIds = array();
        foreach ($collection as $item) {
            $productIds[] = $item->getProductId();
        }
        return $productIds;
    }

    /**
     * Get store model by request param
     *
     * @return \Magento\Store\Model\Store
     */
    protected function _getStore()
    {
        return $this->_storeManager->getStore($this->getRequest()->getParam('store'));
    }
}
