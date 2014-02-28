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

/**
 * Order Credit Memos grid
 *
 * @category   Magento
 * @package    Magento_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Sales\Block\Adminhtml\Order\View\Tab;

class Creditmemos
    extends \Magento\Backend\Block\Widget\Grid\Extended
    implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Core registry
     *
     * @var \Magento\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Sales\Model\Order\Creditmemo
     */
    protected $_orderCreditmemo;

    /**
     * @var \Magento\Sales\Model\Resource\Order\Collection\Factory
     */
    protected $_collectionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Sales\Model\Resource\Order\Collection\Factory $collectionFactory
     * @param \Magento\Sales\Model\Order\Creditmemo $orderCreditmemo
     * @param \Magento\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Sales\Model\Resource\Order\Collection\Factory $collectionFactory,
        \Magento\Sales\Model\Order\Creditmemo $orderCreditmemo,
        \Magento\Registry $coreRegistry,
        array $data = array()
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_orderCreditmemo = $orderCreditmemo;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('order_creditmemos');
        $this->setUseAjax(true);
    }

    /**
     * Retrieve collection class
     *
     * @return string
     */
    protected function _getCollectionClass()
    {
        return 'Magento\Sales\Model\Resource\Order\Creditmemo\Grid\Collection';
    }


    protected function _prepareCollection()
    {
        $collection = $this->_collectionFactory->create($this->_getCollectionClass())
            ->addFieldToSelect('entity_id')
            ->addFieldToSelect('created_at')
            ->addFieldToSelect('increment_id')
            ->addFieldToSelect('order_currency_code')
            ->addFieldToSelect('store_currency_code')
            ->addFieldToSelect('base_currency_code')
            ->addFieldToSelect('state')
            ->addFieldToSelect('grand_total')
            ->addFieldToSelect('base_grand_total')
            ->addFieldToSelect('billing_name')
            ->setOrderFilter($this->getOrder())
        ;
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('increment_id', array(
            'header' => __('Credit Memo'),
            'index' => 'increment_id',
            'header_css_class'  => 'col-memo',
            'column_css_class'  => 'col-memo'
        ));

        $this->addColumn('billing_name', array(
            'header' => __('Bill-to Name'),
            'index' => 'billing_name',
            'header_css_class'  => 'col-name',
            'column_css_class'  => 'col-name'
        ));

        $this->addColumn('created_at', array(
            'header' => __('Created'),
            'index' => 'created_at',
            'type' => 'datetime',
            'header_css_class'  => 'col-period',
            'column_css_class'  => 'col-period'
        ));

        $this->addColumn('state', array(
            'header'    => __('Status'),
            'index'     => 'state',
            'type'      => 'options',
            'options'   => $this->_orderCreditmemo->getStates(),
            'header_css_class'  => 'col-status',
            'column_css_class'  => 'col-status'
        ));

        $this->addColumn('base_grand_total', array(
            'header'    => __('Refunded'),
            'index'     => 'base_grand_total',
            'type'      => 'currency',
            'currency'  => 'base_currency_code',
            'header_css_class'  => 'col-refunded',
            'column_css_class'  => 'col-refunded'
        ));

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

    public function getRowUrl($row)
    {
        return $this->getUrl(
            '*/order_creditmemo/view',
            array(
                'creditmemo_id' => $row->getId(),
                'order_id' => $row->getOrderId()
             ));
    }

    public function getGridUrl()
    {
        return $this->getUrl('sales/*/creditmemos', array('_current' => true));
    }

    /**
     * ######################## TAB settings #################################
     */
    public function getTabLabel()
    {
        return __('Credit Memos');
    }

    public function getTabTitle()
    {
        return __('Order Credit Memos');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }
}
