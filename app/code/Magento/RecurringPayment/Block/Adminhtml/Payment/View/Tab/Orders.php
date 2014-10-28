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
namespace Magento\RecurringPayment\Block\Adminhtml\Payment\View\Tab;

/**
 * Recurring payment orders grid
 */
class Orders extends \Magento\Backend\Block\Widget\Grid\Extended implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry|null
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Sales\Model\Resource\Order\CollectionFactory
     */
    protected $_orderCollection;

    /**
     * @var \Magento\Sales\Model\Order\ConfigFactory
     */
    protected $_orderConfig;

    /**
     * @var \Magento\RecurringPayment\Model\Resource\Order\CollectionFilter
     */
    protected $_recurringCollectionFilter;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Sales\Model\Resource\Order\CollectionFactory $orderCollection
     * @param \Magento\Sales\Model\Order\ConfigFactory $orderConfig
     * @param \Magento\RecurringPayment\Model\Resource\Order\CollectionFilter $recurringCollectionFilter
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Sales\Model\Resource\Order\CollectionFactory $orderCollection,
        \Magento\Sales\Model\Order\ConfigFactory $orderConfig,
        \Magento\RecurringPayment\Model\Resource\Order\CollectionFilter $recurringCollectionFilter,
        array $data = array()
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_orderCollection = $orderCollection;
        $this->_orderConfig = $orderConfig;
        $this->_recurringCollectionFilter = $recurringCollectionFilter;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Initialize basic parameters
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('recurring_payment_orders')->setUseAjax(true)->setSkipGenerateContent(true);
    }

    /**
     * Prepare grid collection object
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_recurringCollectionFilter->byIds(
            $this->_orderCollection->create(),
            $this->_coreRegistry->registry('current_recurring_payment')->getId()
        );
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare grid columns
     *
     * TODO: fix up this mess
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'real_order_id',
            array('header' => __('Order'), 'width' => '80px', 'type' => 'text', 'index' => 'increment_id')
        );

        if (!$this->_storeManager->isSingleStoreMode()) {
            $this->addColumn(
                'store_id',
                array(
                    'header' => __('Purchase Point'),
                    'index' => 'store_id',
                    'type' => 'store',
                    'store_view' => true,
                    'display_deleted' => true
                )
            );
        }

        $this->addColumn(
            'created_at',
            array('header' => __('Purchased Date'), 'index' => 'created_at', 'type' => 'datetime', 'width' => '100px')
        );

        $this->addColumn('billing_name', array('header' => __('Bill-to Name'), 'index' => 'billing_name'));

        $this->addColumn('shipping_name', array('header' => __('Ship-to Name'), 'index' => 'shipping_name'));

        $this->addColumn(
            'base_grand_total',
            array(
                'header' => __('Grand Total (Base)'),
                'index' => 'base_grand_total',
                'type' => 'currency',
                'currency' => 'base_currency_code'
            )
        );

        $this->addColumn(
            'grand_total',
            array(
                'header' => __('Grand Total (Purchased)'),
                'index' => 'grand_total',
                'type' => 'currency',
                'currency' => 'order_currency_code'
            )
        );

        $this->addColumn(
            'status',
            array(
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'options',
                'width' => '70px',
                'options' => $this->_orderConfig->create()->getStatuses()
            )
        );

        if ($this->_authorization->isAllowed('Magento_Sales::actions_view')) {
            $this->addColumn(
                'action',
                array(
                    'header' => __('Action'),
                    'width' => '50px',
                    'type' => 'action',
                    'getter' => 'getId',
                    'actions' => array(
                        array(
                            'caption' => __('View'),
                            'url' => array('base' => 'sales/order/view'),
                            'field' => 'order_id'
                        )
                    ),
                    'filter' => false,
                    'sortable' => false,
                    'index' => 'stores',
                    'is_system' => true
                )
            );
        }

        return parent::_prepareColumns();
    }

    /**
     * Return row url for js event handlers
     *
     * @param \Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('sales/order/view', array('order_id' => $row->getId()));
    }

    /**
     * Url for ajax grid submission
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getTabUrl();
    }

    /**
     * Url for ajax tab
     *
     * @return string
     */
    public function getTabUrl()
    {
        $recurringPayment = $this->_coreRegistry->registry('current_recurring_payment');
        return $this->getUrl('*/*/orders', array('payment' => $recurringPayment->getId()));
    }

    /**
     * Class for ajax tab
     *
     * @return string
     */
    public function getTabClass()
    {
        return 'ajax';
    }

    /**
     * Label getter
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Related Orders');
    }

    /**
     * Same as label getter
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }
}
