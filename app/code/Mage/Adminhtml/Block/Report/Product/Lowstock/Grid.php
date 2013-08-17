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
 * Adminhtml low stock products report grid block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Report_Product_Lowstock_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
//    protected $_saveParametersInSession = true;

    protected function _construct()
    {
        parent::_construct();
        $this->setId('gridLowstock');
        $this->setUseAjax(false);
    }

    protected function _prepareCollection()
    {
        if ($this->getRequest()->getParam('website')) {
            $storeIds = Mage::app()->getWebsite($this->getRequest()->getParam('website'))->getStoreIds();
            $storeId = array_pop($storeIds);
        } else if ($this->getRequest()->getParam('group')) {
            $storeIds = Mage::app()->getGroup($this->getRequest()->getParam('group'))->getStoreIds();
            $storeId = array_pop($storeIds);
        } else if ($this->getRequest()->getParam('store')) {
            $storeId = (int)$this->getRequest()->getParam('store');
        } else {
            $storeId = '';
        }

        /** @var $collection Mage_Reports_Model_Resource_Product_Lowstock_Collection  */
        $collection = Mage::getResourceModel('Mage_Reports_Model_Resource_Product_Lowstock_Collection')
            ->addAttributeToSelect('*')
            ->setStoreId($storeId)
            ->filterByIsQtyProductTypes()
            ->joinInventoryItem('qty')
            ->useManageStockFilter($storeId)
            ->useNotifyStockQtyFilter($storeId)
            ->setOrder('qty', Varien_Data_Collection::SORT_ORDER_ASC);

        if( $storeId ) {
            $collection->addStoreFilter($storeId);
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('name', array(
            'header'    =>Mage::helper('Mage_Reports_Helper_Data')->__('Product'),
            'sortable'  =>false,
            'index'     =>'name',
            'header_css_class'  => 'col-product',
            'column_css_class'  => 'col-product'
        ));

        $this->addColumn('sku', array(
            'header'    =>Mage::helper('Mage_Reports_Helper_Data')->__('SKU'),
            'sortable'  =>false,
            'index'     =>'sku',
            'header_css_class'  => 'col-sku',
            'column_css_class'  => 'col-sku'
        ));

        $this->addColumn('qty', array(
            'header'    =>Mage::helper('Mage_Reports_Helper_Data')->__('Stock Quantity'),
            'sortable'  =>false,
            'filter'    =>'Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Range',
            'index'     =>'qty',
            'type'      =>'number',
            'header_css_class'  => 'col-qty',
            'column_css_class'  => 'col-qty'
        ));

        $this->addExportType('*/*/exportLowstockCsv', Mage::helper('Mage_Reports_Helper_Data')->__('CSV'));
        $this->addExportType('*/*/exportLowstockExcel', Mage::helper('Mage_Reports_Helper_Data')->__('Excel XML'));

        return parent::_prepareColumns();
    }
}
