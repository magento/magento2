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
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml transactions grid
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Sales_Transactions_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
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
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $collection = $this->getCollection();
        if (!$collection) {
            $collection = Mage::getResourceModel('Mage_Sales_Model_Resource_Order_Payment_Transaction_Collection');
        }
        $order = Mage::registry('current_order');
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
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('transaction_id', array(
            'header'    => Mage::helper('Mage_Sales_Helper_Data')->__('ID #'),
            'index'     => 'transaction_id',
            'type'      => 'number'
        ));

        $this->addColumn('increment_id', array(
            'header'    => Mage::helper('Mage_Sales_Helper_Data')->__('Order ID'),
            'index'     => 'increment_id',
            'type'      => 'text'
        ));

        $this->addColumn('txn_id', array(
            'header'    => Mage::helper('Mage_Sales_Helper_Data')->__('Transaction ID'),
            'index'     => 'txn_id',
            'type'      => 'text'
        ));

        $this->addColumn('parent_txn_id', array(
            'header'    => Mage::helper('Mage_Sales_Helper_Data')->__('Parent Transaction ID'),
            'index'     => 'parent_txn_id',
            'type'      => 'text'
        ));

        $this->addColumn('method', array(
            'header'    => Mage::helper('Mage_Sales_Helper_Data')->__('Payment Method Name'),
            'index'     => 'method',
            'type'      => 'options',
            'options'       => Mage::helper('Mage_Payment_Helper_Data')->getPaymentMethodList(true),
            'option_groups' => Mage::helper('Mage_Payment_Helper_Data')->getPaymentMethodList(true, true, true),
        ));

        $this->addColumn('txn_type', array(
            'header'    => Mage::helper('Mage_Sales_Helper_Data')->__('Transaction Type'),
            'index'     => 'txn_type',
            'type'      => 'options',
            'options'   => Mage::getSingleton('Mage_Sales_Model_Order_Payment_Transaction')->getTransactionTypes()
        ));

        $this->addColumn('is_closed', array(
            'header'    => Mage::helper('Mage_Sales_Helper_Data')->__('Is Closed'),
            'index'     => 'is_closed',
            'width'     => 1,
            'type'      => 'options',
            'align'     => 'center',
            'options'   => array(
                1  => Mage::helper('Mage_Sales_Helper_Data')->__('Yes'),
                0  => Mage::helper('Mage_Sales_Helper_Data')->__('No'),
            )
        ));

        $this->addColumn('created_at', array(
            'header'    => Mage::helper('Mage_Sales_Helper_Data')->__('Created At'),
            'index'     => 'created_at',
            'width'     => 1,
            'type'      => 'datetime',
            'align'     => 'center',
            'default'   => $this->__('N/A'),
            'html_decorators' => array('nobr')
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
     * @return string
     */
    public function getRowUrl($item)
    {
        return $this->getUrl('*/*/view', array('txn_id' => $item->getId()));
    }
}
