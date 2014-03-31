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
 * @package     Magento_Sales
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Block\Adminhtml\Order\View\Tab;

/**
 * Order Invoices grid
 *
 * @category   Magento
 * @package    Magento_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Invoices extends \Magento\Backend\Block\Widget\Grid\Extended implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Core registry
     *
     * @var \Magento\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Order invoice
     *
     * @var \Magento\Sales\Model\Order\Invoice
     */
    protected $_orderInvoice;

    /**
     * Collection factory
     *
     * @var \Magento\Sales\Model\Resource\Order\Collection\Factory
     */
    protected $_collectionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Sales\Model\Resource\Order\Collection\Factory $collectionFactory
     * @param \Magento\Sales\Model\Order\Invoice $orderInvoice
     * @param \Magento\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Sales\Model\Resource\Order\Collection\Factory $collectionFactory,
        \Magento\Sales\Model\Order\Invoice $orderInvoice,
        \Magento\Registry $coreRegistry,
        array $data = array()
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_orderInvoice = $orderInvoice;
        $this->_collectionFactory = $collectionFactory;
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
        $this->setId('order_invoices');
        $this->setUseAjax(true);
    }

    /**
     * Retrieve collection class
     *
     * @return string
     */
    protected function _getCollectionClass()
    {
        return 'Magento\Sales\Model\Resource\Order\Invoice\Grid\Collection';
    }

    /**
     * Apply sorting and filtering to collection
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_collectionFactory->create(
            $this->_getCollectionClass()
        )->addFieldToSelect(
            'entity_id'
        )->addFieldToSelect(
            'created_at'
        )->addFieldToSelect(
            'order_id'
        )->addFieldToSelect(
            'increment_id'
        )->addFieldToSelect(
            'state'
        )->addFieldToSelect(
            'grand_total'
        )->addFieldToSelect(
            'base_grand_total'
        )->addFieldToSelect(
            'store_currency_code'
        )->addFieldToSelect(
            'base_currency_code'
        )->addFieldToSelect(
            'order_currency_code'
        )->addFieldToSelect(
            'billing_name'
        )->setOrderFilter(
            $this->getOrder()
        );
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
            'increment_id',
            array(
                'header' => __('Invoice'),
                'index' => 'increment_id',
                'header_css_class' => 'col-invoice-number',
                'column_css_class' => 'col-invoice-number'
            )
        );

        $this->addColumn(
            'billing_name',
            array(
                'header' => __('Bill-to Name'),
                'index' => 'billing_name',
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name'
            )
        );

        $this->addColumn(
            'created_at',
            array(
                'header' => __('Invoice Date'),
                'index' => 'created_at',
                'type' => 'datetime',
                'header_css_class' => 'col-period',
                'column_css_class' => 'col-period'
            )
        );

        $this->addColumn(
            'state',
            array(
                'header' => __('Status'),
                'index' => 'state',
                'type' => 'options',
                'options' => $this->_orderInvoice->getStates(),
                'header_css_class' => 'col-status',
                'column_css_class' => 'col-status'
            )
        );

        $this->addColumn(
            'base_grand_total',
            array(
                'header' => __('Amount'),
                'index' => 'base_grand_total',
                'type' => 'currency',
                'currency' => 'base_currency_code',
                'header_css_class' => 'col-qty',
                'column_css_class' => 'col-qty'
            )
        );

        return parent::_prepareColumns();
    }

    /**
     * Retrieve order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    /**
     * Row URL getter
     *
     * @param \Magento\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl(
            'sales/order_invoice/view',
            array('invoice_id' => $row->getId(), 'order_id' => $row->getOrderId())
        );
    }

    /**
     * Grid URL getter
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('sales/*/invoices', array('_current' => true));
    }

    /**
     * ######################## TAB settings #################################
     */

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Invoices');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Order Invoices');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}
