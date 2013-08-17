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
 * @package     Mage_Tag
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml assigned products grid block
 *
 * @category   Mage
 * @package    Mage_Tag
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Tag_Block_Adminhtml_Assigned_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_currentTagModel;

    /**
     * Set grid params
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_currentTagModel = Mage::registry('current_tag');
        $this->setId('tag_assigned_product_grid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        if ($this->_getTagId()) {
            $this->setDefaultFilter(array('in_products'=>1));
        }
    }

    /**
     * Tag ID getter
     *
     * @return int
     */
    protected function _getTagId()
    {
        return $this->_currentTagModel ? $this->_currentTagModel->getId() : null;
    }

    /**
     * Store getter
     *
     * @return Mage_Core_Model_Store
     */
    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    /**
     * Add filter to grid columns
     *
     * @param mixed $column
     * @return Mage_Tag_Block_Adminhtml_Assigned_Grid
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
     * Retrieve Products Collection
     *
     * @return Mage_Tag_Block_Adminhtml_Assigned_Grid
     */
    protected function _prepareCollection()
    {
        $store = $this->_getStore();
        $collection = Mage::getModel('Mage_Catalog_Model_Product')->getCollection()
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('attribute_set_id')
            ->addAttributeToSelect('type_id')
            //->addAttributeToFilter('status', array(''))
            ->joinField('qty',
                'cataloginventory_stock_item',
                'qty',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left');

        if ($store->getId()) {
            $collection->addStoreFilter($store);
            $collection->joinAttribute('custom_name', 'catalog_product/name', 'entity_id', null, 'inner', $store->getId());
            $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner', $store->getId());
            $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner', $store->getId());
            $collection->joinAttribute('price', 'catalog_product/price', 'entity_id', null, 'left', $store->getId());
        } else {
            $collection->addAttributeToSelect('price');
            $collection->addAttributeToSelect('status');
            $collection->addAttributeToSelect('visibility');
        }

        $this->setCollection($collection);

        parent::_prepareCollection();
        $this->getCollection()->addWebsiteNamesToResult();
        return $this;
    }

    /**
     * Prepeare columns for grid
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('in_products', array(
            'header_css_class'  => 'a-center',
            'type'              => 'checkbox',
            'field_name'        => 'in_products',
            'values'            => $this->_getSelectedProducts(),
            'align'             => 'center',
            'index'             => 'entity_id'
        ));

        $this->addColumn('entity_id',
            array(
                'header'=> Mage::helper('Mage_Tag_Helper_Data')->__('ID'),
                'width' => 50,
                'sortable'  => true,
                'type'  => 'number',
                'index' => 'entity_id',
        ));
        $this->addColumn('name',
            array(
                'header'=> Mage::helper('Mage_Tag_Helper_Data')->__('Name'),
                'index' => 'name',
        ));

        $store = $this->_getStore();
        if ($store->getId()) {
            $this->addColumn('custom_name',
                array(
                    'header'=> Mage::helper('Mage_Tag_Helper_Data')->__('Name in %s', $store->getName()),
                    'index' => 'custom_name',
            ));
        }

        $this->addColumn('type',
            array(
                'header'    => Mage::helper('Mage_Tag_Helper_Data')->__('Type'),
                'width'     => 100,
                'index'     => 'type_id',
                'type'      => 'options',
                'options'   => Mage::getSingleton('Mage_Catalog_Model_Product_Type')->getOptionArray(),
        ));

        $sets = Mage::getResourceModel('Mage_Eav_Model_Resource_Entity_Attribute_Set_Collection')
            ->setEntityTypeFilter(Mage::getModel('Mage_Catalog_Model_Product')->getResource()->getTypeId())
            ->load()
            ->toOptionHash();

        $this->addColumn('set_name',
            array(
                'header'    => Mage::helper('Mage_Tag_Helper_Data')->__('Attribute Set'),
                'width'     => 100,
                'index'     => 'attribute_set_id',
                'type'      => 'options',
                'options'   => $sets,
        ));

        $this->addColumn('sku',
            array(
                'header'=> Mage::helper('Mage_Tag_Helper_Data')->__('SKU'),
                'width' => 80,
                'index' => 'sku',
        ));

        $store = $this->_getStore();
        $this->addColumn('price',
            array(
                'header'        => Mage::helper('Mage_Tag_Helper_Data')->__('Price'),
                'type'          => 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'index'         => 'price',
        ));

        $this->addColumn('visibility',
            array(
                'header'    => Mage::helper('Mage_Tag_Helper_Data')->__('Visibility'),
                'width'     => 100,
                'index'     => 'visibility',
                'type'      => 'options',
                'options'   => Mage::getModel('Mage_Catalog_Model_Product_Visibility')->getOptionArray(),
        ));

        $this->addColumn('status',
            array(
                'header'    => Mage::helper('Mage_Tag_Helper_Data')->__('Status'),
                'width'     => 70,
                'index'     => 'status',
                'type'      => 'options',
                'options'   => Mage::getSingleton('Mage_Catalog_Model_Product_Status')->getOptionArray(),
        ));

        return parent::_prepareColumns();
    }

    /**
     * Retrieve related products
     *
     * @return array
     */
    protected function _getSelectedProducts()
    {
        $products = $this->getRequest()->getPost('assigned_products', null);
        if (!is_array($products)) {
            $products = $this->getRelatedProducts();
        }
        return $products;
    }

    /**
     * Retrieve Grid Url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/assignedGridOnly', array('_current' => true));
    }

    /**
     * Retrieve related products
     *
     * @return array
     */
    public function getRelatedProducts()
    {
        return $this->_currentTagModel
            ->setStatusFilter(Mage_Tag_Model_Tag::STATUS_APPROVED)
            ->getRelatedProductIds();
    }
}
