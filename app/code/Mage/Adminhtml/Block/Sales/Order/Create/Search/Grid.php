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
 * Adminhtml sales order create search products block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Sales_Order_Create_Search_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

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
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        return Mage::getSingleton('Mage_Adminhtml_Model_Session_Quote')->getStore();
    }

    /**
     * Retrieve quote object
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return Mage::getSingleton('Mage_Adminhtml_Model_Session_Quote')->getQuote();
    }

    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag
        if ($column->getId() == 'in_products') {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', array('in'=>$productIds));
            } else {
                if($productIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', array('nin'=>$productIds));
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
     * @return Mage_Adminhtml_Block_Sales_Order_Create_Search_Grid
     */
    protected function _prepareCollection()
    {
        $attributes = Mage::getSingleton('Mage_Catalog_Model_Config')->getProductAttributes();
        /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $collection = Mage::getModel('Mage_Catalog_Model_Product')->getCollection();
        $collection
            ->setStore($this->getStore())
            ->addAttributeToSelect($attributes)
            ->addAttributeToSelect('sku')
            ->addStoreFilter()
            ->addAttributeToFilter('type_id', array_keys(
                Mage::getConfig()->getNode('adminhtml/sales/order/create/available_product_types')->asArray()
            ))
            ->addAttributeToSelect('gift_message_available');

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare columns
     *
     * @return Mage_Adminhtml_Block_Sales_Order_Create_Search_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('entity_id', array(
            'header'    => Mage::helper('Mage_Sales_Helper_Data')->__('ID'),
            'sortable'  => true,
            'width'     => '60',
            'index'     => 'entity_id'
        ));
        $this->addColumn('name', array(
            'header'    => Mage::helper('Mage_Sales_Helper_Data')->__('Product'),
            'renderer'  => 'Mage_Adminhtml_Block_Sales_Order_Create_Search_Grid_Renderer_Product',
            'index'     => 'name'
        ));
        $this->addColumn('sku', array(
            'header'    => Mage::helper('Mage_Sales_Helper_Data')->__('SKU'),
            'width'     => '80',
            'index'     => 'sku'
        ));
        $this->addColumn('price', array(
            'header'    => Mage::helper('Mage_Sales_Helper_Data')->__('Price'),
            'column_css_class' => 'price',
            'align'     => 'center',
            'type'      => 'currency',
            'currency_code' => $this->getStore()->getCurrentCurrencyCode(),
            'rate'      => $this->getStore()->getBaseCurrency()->getRate($this->getStore()->getCurrentCurrencyCode()),
            'index'     => 'price',
            'renderer'  => 'Mage_Adminhtml_Block_Sales_Order_Create_Search_Grid_Renderer_Price',
        ));

        $this->addColumn('in_products', array(
            'header'    => Mage::helper('Mage_Sales_Helper_Data')->__('Select'),
            'header_css_class' => 'a-center',
            'type'      => 'checkbox',
            'name'      => 'in_products',
            'values'    => $this->_getSelectedProducts(),
            'align'     => 'center',
            'index'     => 'entity_id',
            'sortable'  => false,
        ));

        $this->addColumn('qty', array(
            'filter'    => false,
            'sortable'  => false,
            'header'    => Mage::helper('Mage_Sales_Helper_Data')->__('Quantity'),
            'renderer'  => 'Mage_Adminhtml_Block_Sales_Order_Create_Search_Grid_Renderer_Qty',
            'name'      => 'qty',
            'inline_css'=> 'qty',
            'align'     => 'center',
            'type'      => 'input',
            'validate_class' => 'validate-number',
            'index'     => 'qty',
            'width'     => '1',
        ));

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/loadBlock', array('block'=>'search_grid', '_current' => true, 'collapse' => null));
    }

    protected function _getSelectedProducts()
    {
        $products = $this->getRequest()->getPost('products', array());

        return $products;
    }

    /*
     * Add custom options to product collection
     *
     * return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _afterLoadCollection() {
        $this->getCollection()->addOptionsToResult();
        return parent::_afterLoadCollection();
    }
}
