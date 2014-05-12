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
namespace Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle\Option\Search;

/**
 * Bundle selection product grid
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Bundle data
     *
     * @var \Magento\Bundle\Helper\Data
     */
    protected $_bundleData = null;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Bundle\Helper\Data $bundleData
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Bundle\Helper\Data $bundleData,
        array $data = array()
    ) {
        $this->_bundleData = $bundleData;
        $this->_productFactory = $productFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
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
            array('nin' => $this->_getSelectedProducts())
        )->addAttributeToFilter(
            'type_id',
            array('in' => $this->getAllowedSelectionTypes())
        )->addFilterByRequiredOptions()->addStoreFilter(
            \Magento\Store\Model\Store::DEFAULT_STORE_ID
        );

        if ($this->getFirstShow()) {
            $collection->addIdFilter('-1');
            $this->setEmptyText(__('Please enter search conditions to view products.'));
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Initialize grid columns
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            array(
                'header' => __('ID'),
                'index' => 'entity_id',
                'renderer' => 'Magento\Backend\Block\Widget\Grid\Column\Renderer\Checkbox',
                'type' => 'skip-list'
            )
        );

        $this->addColumn(
            'name',
            array(
                'header' => __('Product'),
                'index' => 'name',
                'header_css_class' => 'col-name',
                'column_css_class' => 'name col-name'
            )
        );
        $this->addColumn(
            'sku',
            array(
                'header' => __('SKU'),
                'width' => '80px',
                'index' => 'sku',
                'header_css_class' => 'col-sku',
                'column_css_class' => 'sku col-sku'
            )
        );
        $this->addColumn(
            'price',
            array(
                'header' => __('Price'),
                'align' => 'center',
                'type' => 'currency',
                'index' => 'price',
                'header_css_class' => 'col-price',
                'column_css_class' => 'col-price'
            )
        );
        return parent::_prepareColumns();
    }

    /**
     * Retrieve grid reload url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl(
            'adminhtml/bundle_selection/grid',
            array('index' => $this->getIndex(), 'productss' => implode(',', $this->_getProducts()))
        );
    }

    /**
     * @return mixed
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
     */
    protected function _getProducts()
    {
        if ($products = $this->getRequest()->getPost('products', null)) {
            return $products;
        } else {
            if ($productss = $this->getRequest()->getParam('productss', null)) {
                return explode(',', $productss);
            } else {
                return array();
            }
        }
    }

    /**
     * Retrieve array of allowed product types for bundle selection product
     *
     * @return array
     */
    public function getAllowedSelectionTypes()
    {
        return $this->_bundleData->getAllowedSelectionTypes();
    }
}
