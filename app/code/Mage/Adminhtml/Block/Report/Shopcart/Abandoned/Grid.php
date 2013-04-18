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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml abandoned shopping carts report grid block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Report_Shopcart_Abandoned_Grid extends Mage_Adminhtml_Block_Report_Grid_Shopcart
{

    protected function _construct()
    {
        parent::_construct();
        $this->setId('gridAbandoned');
    }

    protected function _prepareCollection()
    {
        /** @var $collection Mage_Reports_Model_Resource_Quote_Collection */
        $collection = Mage::getResourceModel('Mage_Reports_Model_Resource_Quote_Collection');

        $filter = $this->getParam($this->getVarNameFilter(), array());
        if ($filter) {
            $filter = base64_decode($filter);
            parse_str(urldecode($filter), $data);
        }

        if (!empty($data)) {
            $collection->prepareForAbandonedReport($this->_storeIds, $data);
        } else {
            $collection->prepareForAbandonedReport($this->_storeIds);
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _addColumnFilterToCollection($column)
    {
        $field = ( $column->getFilterIndex() ) ? $column->getFilterIndex() : $column->getIndex();
        $skip = array('subtotal', 'customer_name', 'email'/*, 'created_at', 'updated_at'*/);

        if (in_array($field, $skip)) {
            return $this;
        }

        parent::_addColumnFilterToCollection($column);
        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('customer_name', array(
            'header'    => Mage::helper('Mage_Reports_Helper_Data')->__('Customer Name'),
            'index'     => 'customer_name',
            'sortable'  => false,
            'header_css_class'  => 'col-name',
            'column_css_class'  => 'col-name'
        ));

        $this->addColumn('email', array(
            'header'    => Mage::helper('Mage_Reports_Helper_Data')->__('Email'),
            'index'     => 'email',
            'sortable'  => false,
            'header_css_class'  => 'col-email',
            'column_css_class'  => 'col-email'
        ));

        $this->addColumn('items_count', array(
            'header'    => Mage::helper('Mage_Reports_Helper_Data')->__('Number of Items'),
            'index'     => 'items_count',
            'sortable'  => false,
            'type'      => 'number',
            'header_css_class'  => 'col-number',
            'column_css_class'  => 'col-number'
        ));

        $this->addColumn('items_qty', array(
            'header'    => Mage::helper('Mage_Reports_Helper_Data')->__('Quantity of Items'),
            'index'     => 'items_qty',
            'sortable'  => false,
            'type'      => 'number',
            'header_css_class'  => 'col-qty',
            'column_css_class'  => 'col-qty'
        ));

        if ($this->getRequest()->getParam('website')) {
            $storeIds = Mage::app()->getWebsite($this->getRequest()->getParam('website'))->getStoreIds();
        } else if ($this->getRequest()->getParam('group')) {
            $storeIds = Mage::app()->getGroup($this->getRequest()->getParam('group'))->getStoreIds();
        } else if ($this->getRequest()->getParam('store')) {
            $storeIds = array((int)$this->getRequest()->getParam('store'));
        } else {
            $storeIds = array();
        }
        $this->setStoreIds($storeIds);
        $currencyCode = $this->getCurrentCurrencyCode();

        $this->addColumn('subtotal', array(
            'header'        => Mage::helper('Mage_Reports_Helper_Data')->__('Subtotal'),
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'index'         => 'subtotal',
            'sortable'      => false,
            'renderer'      => 'Mage_Adminhtml_Block_Report_Grid_Column_Renderer_Currency',
            'rate'          => $this->getRate($currencyCode),
            'header_css_class'  => 'col-subtotal',
            'column_css_class'  => 'col-subtotal'
        ));

        $this->addColumn('coupon_code', array(
            'header'    => Mage::helper('Mage_Reports_Helper_Data')->__('Applied Coupon'),
            'index'     => 'coupon_code',
            'sortable'  => false,
            'header_css_class'  => 'col-coupon',
            'column_css_class'  => 'col-coupon'
        ));

        $this->addColumn('created_at', array(
            'header'    => Mage::helper('Mage_Reports_Helper_Data')->__('Created At'),
            'type'      => 'datetime',
            'index'     => 'created_at',
            'filter_index'=> 'main_table.created_at',
            'sortable'  => false,
            'header_css_class'  => 'col-created',
            'column_css_class'  => 'col-created'
        ));

        $this->addColumn('updated_at', array(
            'header'    => Mage::helper('Mage_Reports_Helper_Data')->__('Updated At'),
            'type'      => 'datetime',
            'index'     => 'updated_at',
            'filter_index'=> 'main_table.updated_at',
            'sortable'  => false,
            'header_css_class'  => 'col-updated',
            'column_css_class'  => 'col-updated'
        ));

        $this->addColumn('remote_ip', array(
            'header'    => Mage::helper('Mage_Reports_Helper_Data')->__('IP Address'),
            'index'     => 'remote_ip',
            'sortable'  => false,
            'header_css_class'  => 'col-ip',
            'column_css_class'  => 'col-ip'
        ));

        $this->addExportType('*/*/exportAbandonedCsv', Mage::helper('Mage_Reports_Helper_Data')->__('CSV'));
        $this->addExportType('*/*/exportAbandonedExcel', Mage::helper('Mage_Reports_Helper_Data')->__('Excel XML'));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/customer/edit', array('id'=>$row->getCustomerId(), 'active_tab'=>'cart'));
    }
}
