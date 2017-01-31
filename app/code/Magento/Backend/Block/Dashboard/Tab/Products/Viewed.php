<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Dashboard\Tab\Products;

use Magento\Catalog\Model\Product;

/**
 * Adminhtml dashboard most viewed products grid
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Viewed extends \Magento\Backend\Block\Dashboard\Grid
{
    /**
     * @var \Magento\Reports\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productsFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Reports\Model\ResourceModel\Product\CollectionFactory $productsFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Reports\Model\ResourceModel\Product\CollectionFactory $productsFactory,
        array $data = []
    ) {
        $this->_productsFactory = $productsFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('productsReviewedGrid');
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareCollection()
    {
        if ($this->getParam('website')) {
            $storeIds = $this->_storeManager->getWebsite($this->getParam('website'))->getStoreIds();
            $storeId = array_pop($storeIds);
        } elseif ($this->getParam('group')) {
            $storeIds = $this->_storeManager->getGroup($this->getParam('group'))->getStoreIds();
            $storeId = array_pop($storeIds);
        } else {
            $storeId = (int)$this->getParam('store');
        }
        $collection = $this->_productsFactory->create()->addAttributeToSelect(
            '*'
        )->addViewsCount()->setStoreId(
            $storeId
        )->addStoreFilter(
            $storeId
        );

        $this->setCollection($collection);

        parent::_prepareCollection();

        /** @var Product $product */
        foreach ($collection as $product) {
            $product->setPrice($product->getFinalPrice());
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareColumns()
    {
        $this->addColumn('name', ['header' => __('Product'), 'sortable' => false, 'index' => 'name']);

        $this->addColumn(
            'price',
            [
                'header' => __('Price'),
                'type' => 'currency',
                'currency_code' => (string)$this->_storeManager->getStore(
                    (int)$this->getParam('store')
                )->getBaseCurrencyCode(),
                'sortable' => false,
                'index' => 'price'
            ]
        );

        $this->addColumn(
            'views',
            [
                'header' => __('Views'),
                'sortable' => false,
                'index' => 'views',
                'header_css_class' => 'col-views',
                'column_css_class' => 'col-views'
            ]
        );

        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);

        return parent::_prepareColumns();
    }

    /**
     * {@inheritdoc}
     */
    public function getRowUrl($row)
    {
        $params = ['id' => $row->getId()];
        if ($this->getRequest()->getParam('store')) {
            $params['store'] = $this->getRequest()->getParam('store');
        }
        return $this->getUrl('catalog/product/edit', $params);
    }
}
