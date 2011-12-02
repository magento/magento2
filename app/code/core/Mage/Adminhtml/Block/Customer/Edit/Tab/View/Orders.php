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
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml customer recent orders grid block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Customer_Edit_Tab_View_Orders extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('customer_view_orders_grid');
        $this->setDefaultSort('created_at', 'desc');
        $this->setSortable(false);
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
    }

    protected function _preparePage()
    {
        $this->getCollection()
            ->setPageSize(5)
            ->setCurPage(1);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('Mage_Sales_Model_Resource_Order_Grid_Collection')
            ->addFieldToFilter('customer_id', Mage::registry('current_customer')->getId())
            ->setIsCustomerMode(true);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $this->addColumn('increment_id', array(
            'header'    => Mage::helper('Mage_Customer_Helper_Data')->__('Order #'),
            'align'     => 'center',
            'index'     => 'increment_id',
            'width'     => '100px',
        ));

        $this->addColumn('created_at', array(
            'header'    => Mage::helper('Mage_Customer_Helper_Data')->__('Purchased At'),
            'index'     => 'created_at',
            'type'      => 'datetime',
        ));

        $this->addColumn('billing_name', array(
            'header'    => Mage::helper('Mage_Customer_Helper_Data')->__('Bill to Name'),
            'index'     => 'billing_name',
        ));

        $this->addColumn('shipping_name', array(
            'header'    => Mage::helper('Mage_Customer_Helper_Data')->__('Shipped to Name'),
            'index'     => 'shipping_name',
        ));

        $this->addColumn('grand_total', array(
            'header'    => Mage::helper('Mage_Customer_Helper_Data')->__('Grand Total'),
            'index'     => 'grand_total',
            'type'      => 'currency',
            'currency'  => 'order_currency_code',
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'    => Mage::helper('Mage_Customer_Helper_Data')->__('Bought From'),
                'index'     => 'store_id',
                'type'      => 'store',
                'store_view' => true,
            ));
        }

        $this->addColumn('action', array(
            'header'    =>  ' ',
            'filter'    =>  false,
            'sortable'  =>  false,
            'width'     => '100px',
            'renderer'  =>  'Mage_Adminhtml_Block_Sales_Reorder_Renderer_Action'
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/sales_order/view', array('order_id' => $row->getId()));
    }

    public function getHeadersVisibility()
    {
        return ($this->getCollection()->getSize() > 0);
    }

}
