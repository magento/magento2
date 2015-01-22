<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleShopping\Block\Adminhtml\Items;

/**
 * Google Shopping Items
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Item extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Collection factory
     *
     * @var \Magento\GoogleShopping\Model\Resource\Item\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\GoogleShopping\Model\Resource\Item\CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\GoogleShopping\Model\Resource\Item\CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('items');
        $this->setUseAjax(true);
    }

    /**
     * Prepare grid collection object
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_collectionFactory->create();
        $store = $this->_storeManager->getStore($this->getRequest()->getParam('store'));
        $collection->addStoreFilter($store->getId());
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    /**
     * Prepare grid columns
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn('name', ['header' => __('Product'), 'index' => 'name']);

        $this->addColumn(
            'expires',
            [
                'header' => __('Expires'),
                'type' => 'datetime',
                'index' => 'expires',
                'header_css_class' => 'col-period',
                'column_css_class' => 'col-period'
            ]
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
        $this->setMassactionIdField('item_id');
        $this->getMassactionBlock()->setFormFieldName('item');
        $this->setNoFilterMassactionColumn(true);

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('adminhtml/*/massDelete', ['_current' => true]),
                'confirm' => __('Are you sure?')
            ]
        );

        $this->getMassactionBlock()->addItem(
            'refresh',
            [
                'label' => __('Synchronize'),
                'url' => $this->getUrl('adminhtml/*/refresh', ['_current' => true]),
                'confirm' => __(
                    'This action will update items\' attributes and remove items that are not available in Google Content. If an attribute was deleted from the mapping, it will also be deleted from Google. Do you want to continue?'
                )
            ]
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
        return $this->getUrl('adminhtml/*/grid', ['_current' => true]);
    }
}
