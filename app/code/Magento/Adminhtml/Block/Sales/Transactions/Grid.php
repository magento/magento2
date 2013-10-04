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
 * Adminhtml transactions grid
 *
 * @category   Magento
 * @package    Magento_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Adminhtml\Block\Sales\Transactions;

class Grid extends \Magento\Adminhtml\Block\Widget\Grid
{
    /**
     * Core registry
     *
     * @var \Magento\Core\Model\Registry
     */
    protected $_coreRegistry = null;
    
    /**
     * Payment data
     *
     * @var \Magento\Payment\Helper\Data
     */
    protected $_paymentData = null;

    /**
     * @var \Magento\Sales\Model\Order\Payment\Transaction
     */
    protected $_transaction;

    /**
     * @var \Magento\Sales\Model\Resource\Order\Payment\Transaction\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @param \Magento\Sales\Model\Order\Payment\Transaction $transaction
     * @param \Magento\Sales\Model\Resource\Order\Payment\Transaction\CollectionFactory $collectionFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Core\Model\Url $urlModel
     * @param \Magento\Core\Model\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Sales\Model\Order\Payment\Transaction $transaction,
        \Magento\Sales\Model\Resource\Order\Payment\Transaction\CollectionFactory $collectionFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Core\Model\Url $urlModel,
        \Magento\Core\Model\Registry $coreRegistry,
        array $data = array()
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_paymentData = $paymentData;
        $this->_transaction = $transaction;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($coreData, $context, $storeManager, $urlModel, $data);
    }

    /**
     * Set grid params
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('order_transactions');
        $this->setUseAjax(true);
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Prepare collection for grid
     *
     * @return \Magento\Adminhtml\Block\Widget\Grid
     */
    protected function _prepareCollection()
    {
        $collection = $this->getCollection();
        if (!$collection) {
            $collection = $this->_collectionFactory->create();
        }
        $order = $this->_coreRegistry->registry('current_order');
        if ($order) {
            $collection->addOrderIdFilter($order->getId());
        }
        $collection->addOrderInformation(array('increment_id'));
        $collection->addPaymentInformation(array('method'));
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Add columns to grid
     *
     * @return \Magento\Adminhtml\Block\Widget\Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('transaction_id', array(
            'header' => __('ID'),
            'index' => 'transaction_id',
            'type' => 'number',
            'header_css_class' => 'col-id',
            'column_css_class' => 'col-id'
        ));

        $this->addColumn('increment_id', array(
            'header' => __('Order ID'),
            'index' => 'increment_id',
            'type' => 'text',
            'header_css_class' => 'col-order-id',
            'column_css_class' => 'col-order-id'
        ));

        $this->addColumn('txn_id', array(
            'header' => __('Transaction ID'),
            'index' => 'txn_id',
            'type' => 'text',
            'header_css_class' => 'col-transaction-id',
            'column_css_class' => 'col-transaction-id'
        ));

        $this->addColumn('parent_txn_id', array(
            'header' => __('Parent Transaction ID'),
            'index' => 'parent_txn_id',
            'type' => 'text',
            'header_css_class' => 'col-parent-transaction-id',
            'column_css_class' => 'col-parent-transaction-id'
        ));

        $this->addColumn('method', array(
            'header' => __('Payment Method'),
            'index' => 'method',
            'type' => 'options',
            'options' => $this->_paymentData->getPaymentMethodList(true),
            'option_groups' => $this->_paymentData->getPaymentMethodList(true, true, true),
            'header_css_class' => 'col-method',
            'column_css_class' => 'col-method'
        ));

        $this->addColumn('txn_type', array(
            'header' => __('Transaction Type'),
            'index' => 'txn_type',
            'type' => 'options',
            'options' => $this->_transaction->getTransactionTypes(),
            'header_css_class' => 'col-transaction-type',
            'column_css_class' => 'col-transaction-type'
        ));

        $this->addColumn('is_closed', array(
            'header' => __('Closed'),
            'index' => 'is_closed',
            'width' => 1,
            'type' => 'options',
            'align' => 'center',
            'options' => array(
                1 => __('Yes'),
                0 => __('No'),
            ),
            'header_css_class' => 'col-closed',
            'column_css_class' => 'col-closed'
        ));

        $this->addColumn('created_at', array(
            'header' => __('Created'),
            'index' => 'created_at',
            'width' => 1,
            'type' => 'datetime',
            'align' => 'center',
            'default' => __('N/A'),
            'html_decorators' => array('nobr'),
            'header_css_class' => 'col-period',
            'column_css_class' => 'col-period'
        ));

        return parent::_prepareColumns();
    }

    /**
     * Retrieve grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

    /**
     * Retrieve row url
     *
     * @param $item
     * @return string
     */
    public function getRowUrl($item)
    {
        return $this->getUrl('*/*/view', array('txn_id' => $item->getId()));
    }
}
