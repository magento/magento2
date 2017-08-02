<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle\Option\Search;

/**
 * Bundle selection product grid
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Bundle data
     *
     * @var \Magento\Bundle\Helper\Data
     * @since 2.0.0
     */
    protected $_bundleData = null;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     * @since 2.0.0
     */
    protected $_productFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Bundle\Helper\Data $bundleData
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Bundle\Helper\Data $bundleData,
        array $data = []
    ) {
        $this->_bundleData = $bundleData;
        $this->_productFactory = $productFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('bundle_selection_search_grid');
        $this->setRowClickCallback('bSelection.productGridRowClick.bind(bSelection)');
        $this->setCheckboxCheckCallback('bSelection.productGridCheckboxCheck.bind(bSelection)');
        $this->setRowInitCallback('bSelection.productGridRowInit.bind(bSelection)');
        $this->setDefaultSort('id');
        $this->setUseAjax(true);
    }

    /**
     * Prepare grid filter buttons
     *
     * @return void
     * @since 2.0.0
     */
    protected function _prepareFilterButtons()
    {
        $this->getChildBlock(
            'reset_filter_button'
        )->setData(
            'onclick',
            $this->getJsObjectName() . '.resetFilter(bSelection.gridUpdateCallback)'
        );
        $this->getChildBlock(
            'search_button'
        )->setData(
            'onclick',
            $this->getJsObjectName() . '.doFilter(bSelection.gridUpdateCallback)'
        );
    }

    /**
     * Initialize grid before rendering
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _beforeToHtml()
    {
        $this->setId($this->getId() . '_' . $this->getIndex());
        return parent::_beforeToHtml();
    }

    /**
     * Apply sorting and filtering to collection
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _prepareCollection()
    {
        $collection = $this->_productFactory->create()->getCollection()->setOrder(
            'id'
        )->addAttributeToSelect(
            'name'
        )->addAttributeToSelect(
            'sku'
        )->addAttributeToSelect(
            'price'
        )->addAttributeToSelect(
            'attribute_set_id'
        )->addAttributeToFilter(
            'entity_id',
            ['nin' => $this->_getSelectedProducts()]
        )->addAttributeToFilter(
            'type_id',
            ['in' => $this->getAllowedSelectionTypes()]
        )->addFilterByRequiredOptions()->addStoreFilter(
            \Magento\Store\Model\Store::DEFAULT_STORE_ID
        );

        if ($this->getFirstShow()) {
            $collection->addIdFilter('-1');
            $this->setEmptyText(__('What are you looking for?'));
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Initialize grid columns
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            [
                'header' => __('ID'),
                'index' => 'entity_id',
                'renderer' => \Magento\Backend\Block\Widget\Grid\Column\Renderer\Checkbox::class,
                'type' => 'skip-list'
            ]
        );

        $this->addColumn(
            'name',
            [
                'header' => __('Product'),
                'index' => 'name',
                'header_css_class' => 'col-name',
                'column_css_class' => 'name col-name'
            ]
        );
        $this->addColumn(
            'sku',
            [
                'header' => __('SKU'),
                'width' => '80px',
                'index' => 'sku',
                'header_css_class' => 'col-sku',
                'column_css_class' => 'sku col-sku'
            ]
        );
        $this->addColumn(
            'price',
            [
                'header' => __('Price'),
                'align' => 'center',
                'type' => 'currency',
                'index' => 'price',
                'header_css_class' => 'col-price',
                'column_css_class' => 'col-price'
            ]
        );
        return parent::_prepareColumns();
    }

    /**
     * Retrieve grid reload url
     *
     * @return string
     * @since 2.0.0
     */
    public function getGridUrl()
    {
        return $this->getUrl(
            'adminhtml/bundle_selection/grid',
            ['index' => $this->getIndex(), 'productss' => implode(',', $this->_getProducts())]
        );
    }

    /**
     * @return mixed
     * @since 2.0.0
     */
    protected function _getSelectedProducts()
    {
        $products = $this->getRequest()->getPost(
            'selected_products',
            explode(',', $this->getRequest()->getParam('productss'))
        );
        return $products;
    }

    /**
     * @return array
     * @since 2.0.0
     */
    protected function _getProducts()
    {
        if ($products = $this->getRequest()->getPost('products', null)) {
            return $products;
        } else {
            if ($productss = $this->getRequest()->getParam('productss', null)) {
                return explode(',', $productss);
            } else {
                return [];
            }
        }
    }

    /**
     * Retrieve array of allowed product types for bundle selection product
     *
     * @return array
     * @since 2.0.0
     */
    public function getAllowedSelectionTypes()
    {
        return $this->_bundleData->getAllowedSelectionTypes();
    }
}
