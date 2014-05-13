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
namespace Magento\Sales\Block\Adminhtml\Order\Create\Search;

/**
 * Adminhtml sales order create search products block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Sales config
     *
     * @var \Magento\Sales\Model\Config
     */
    protected $_salesConfig;

    /**
     * Session quote
     *
     * @var \Magento\Backend\Model\Session\Quote
     */
    protected $_sessionQuote;

    /**
     * Catalog config
     *
     * @var \Magento\Catalog\Model\Config
     */
    protected $_catalogConfig;

    /**
     * Product factory
     *
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\Config $catalogConfig
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\Sales\Model\Config $salesConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\Config $catalogConfig,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Sales\Model\Config $salesConfig,
        array $data = array()
    ) {
        $this->_productFactory = $productFactory;
        $this->_catalogConfig = $catalogConfig;
        $this->_sessionQuote = $sessionQuote;
        $this->_salesConfig = $salesConfig;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('sales_order_create_search_grid');
        $this->setRowClickCallback('order.productGridRowClick.bind(order)');
        $this->setCheckboxCheckCallback('order.productGridCheckboxCheck.bind(order)');
        $this->setRowInitCallback('order.productGridRowInit.bind(order)');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
        if ($this->getRequest()->getParam('collapse')) {
            $this->setIsCollapsed(true);
        }
    }

    /**
     * Retrieve quote store object
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        return $this->_sessionQuote->getStore();
    }

    /**
     * Retrieve quote object
     *
     * @return \Magento\Sales\Model\Quote
     */
    public function getQuote()
    {
        return $this->_sessionQuote->getQuote();
    }

    /**
     * Add column filter to collection
     *
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag
        if ($column->getId() == 'in_products') {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', array('in' => $productIds));
            } else {
                if ($productIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', array('nin' => $productIds));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * Prepare collection to be displayed in the grid
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $attributes = $this->_catalogConfig->getProductAttributes();
        /* @var $collection \Magento\Catalog\Model\Resource\Product\Collection */
        $collection = $this->_productFactory->create()->getCollection();
        $collection->setStore(
            $this->getStore()
        )->addAttributeToSelect(
            $attributes
        )->addAttributeToSelect(
            'sku'
        )->addStoreFilter()->addAttributeToFilter(
            'type_id',
            $this->_salesConfig->getAvailableProductTypes()
        )->addAttributeToSelect(
            'gift_message_available'
        );

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare columns
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id',
            array(
                'header' => __('ID'),
                'sortable' => true,
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
                'index' => 'entity_id'
            )
        );
        $this->addColumn(
            'name',
            array(
                'header' => __('Product'),
                'renderer' => 'Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid\Renderer\Product',
                'index' => 'name'
            )
        );
        $this->addColumn('sku', array('header' => __('SKU'), 'index' => 'sku'));
        $this->addColumn(
            'price',
            array(
                'header' => __('Price'),
                'column_css_class' => 'price',
                'type' => 'currency',
                'currency_code' => $this->getStore()->getCurrentCurrencyCode(),
                'rate' => $this->getStore()->getBaseCurrency()->getRate($this->getStore()->getCurrentCurrencyCode()),
                'index' => 'price',
                'renderer' => 'Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid\Renderer\Price'
            )
        );

        $this->addColumn(
            'in_products',
            array(
                'header' => __('Select'),
                'type' => 'checkbox',
                'name' => 'in_products',
                'values' => $this->_getSelectedProducts(),
                'align' => 'center',
                'index' => 'entity_id',
                'sortable' => false
            )
        );

        $this->addColumn(
            'qty',
            array(
                'filter' => false,
                'sortable' => false,
                'header' => __('Quantity'),
                'renderer' => 'Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid\Renderer\Qty',
                'name' => 'qty',
                'inline_css' => 'qty',
                'type' => 'input',
                'validate_class' => 'validate-number',
                'index' => 'qty'
            )
        );

        return parent::_prepareColumns();
    }

    /**
     * Get grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl(
            'sales/*/loadBlock',
            array('block' => 'search_grid', '_current' => true, 'collapse' => null)
        );
    }

    /**
     * Get selected products
     *
     * @return mixed
     */
    protected function _getSelectedProducts()
    {
        $products = $this->getRequest()->getPost('products', array());

        return $products;
    }

    /**
     * Add custom options to product collection
     *
     * @return $this
     */
    protected function _afterLoadCollection()
    {
        $this->getCollection()->addOptionsToResult();
        return parent::_afterLoadCollection();
    }
}
