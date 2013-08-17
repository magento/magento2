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
 * Adminhtml customer grid block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Catalog_Product_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    protected function _construct()
    {
        parent::_construct();
        $this->setId('productGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('product_filter');

    }

    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _prepareCollection()
    {
        $store = $this->_getStore();
        $collection = Mage::getModel('Mage_Catalog_Model_Product')->getCollection()
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('attribute_set_id')
            ->addAttributeToSelect('type_id');

        if (Mage::helper('Mage_Catalog_Helper_Data')->isModuleEnabled('Mage_CatalogInventory')) {
            $collection->joinField('qty',
                'cataloginventory_stock_item',
                'qty',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left');
        }
        if ($store->getId()) {
            //$collection->setStoreId($store->getId());
            $adminStore = Mage_Core_Model_AppInterface::ADMIN_STORE_ID;
            $collection->addStoreFilter($store);
            $collection->joinAttribute(
                'name',
                'catalog_product/name',
                'entity_id',
                null,
                'inner',
                $adminStore
            );
            $collection->joinAttribute(
                'custom_name',
                'catalog_product/name',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'status',
                'catalog_product/status',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'visibility',
                'catalog_product/visibility',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'price',
                'catalog_product/price',
                'entity_id',
                null,
                'left',
                $store->getId()
            );
        }
        else {
            $collection->addAttributeToSelect('price');
            $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
            $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
        }

        $this->setCollection($collection);

        parent::_prepareCollection();
        $this->getCollection()->addWebsiteNamesToResult();
        return $this;
    }

    protected function _addColumnFilterToCollection($column)
    {
        if ($this->getCollection()) {
            if ($column->getId() == 'websites') {
                $this->getCollection()->joinField('websites',
                    'catalog_product_website',
                    'website_id',
                    'product_id=entity_id',
                    null,
                    'left');
            }
        }
        return parent::_addColumnFilterToCollection($column);
    }

    protected function _prepareColumns()
    {
        $this->addColumn('entity_id',
            array(
                'header'=> Mage::helper('Mage_Catalog_Helper_Data')->__('ID'),
                'width' => '50px',
                'type'  => 'number',
                'index' => 'entity_id',
                'header_css_class'  => 'col-id',
                'column_css_class'  => 'col-id'
        ));
        $this->addColumn('name',
            array(
                'header'=> Mage::helper('Mage_Catalog_Helper_Data')->__('Name'),
                'index' => 'name',
                'class' => 'xxx',
                'header_css_class'  => 'col-name',
                'column_css_class'  => 'col-name'
        ));

        $store = $this->_getStore();
        if ($store->getId()) {
            $this->addColumn('custom_name',
                array(
                    'header'=> Mage::helper('Mage_Catalog_Helper_Data')->__('Name in %s', $store->getName()),
                    'index' => 'custom_name',
                    'header_css_class'  => 'col-name',
                    'column_css_class'  => 'col-name'
            ));
        }

        $this->addColumn('type',
            array(
                'header'=> Mage::helper('Mage_Catalog_Helper_Data')->__('Type'),
                'width' => '60px',
                'index' => 'type_id',
                'type'  => 'options',
                'options' => Mage::getSingleton('Mage_Catalog_Model_Product_Type')->getOptionArray(),
                'header_css_class'  => 'col-type',
                'column_css_class'  => 'col-type'
        ));

        $sets = Mage::getResourceModel('Mage_Eav_Model_Resource_Entity_Attribute_Set_Collection')
            ->setEntityTypeFilter(Mage::getModel('Mage_Catalog_Model_Product')->getResource()->getTypeId())
            ->load()
            ->toOptionHash();

        $this->addColumn('set_name',
            array(
                'header'=> Mage::helper('Mage_Catalog_Helper_Data')->__('Attribute Set'),
                'width' => '100px',
                'index' => 'attribute_set_id',
                'type'  => 'options',
                'options' => $sets,
                'header_css_class'  => 'col-attr-name',
                'column_css_class'  => 'col-attr-name'
        ));

        $this->addColumn('sku',
            array(
                'header'=> Mage::helper('Mage_Catalog_Helper_Data')->__('SKU'),
                'width' => '80px',
                'index' => 'sku',
                'header_css_class'  => 'col-sku',
                'column_css_class'  => 'col-sku'
        ));

        $store = $this->_getStore();
        $this->addColumn('price',
            array(
                'header'=> Mage::helper('Mage_Catalog_Helper_Data')->__('Price'),
                'type'  => 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'index' => 'price',
                'header_css_class'  => 'col-price',
                'column_css_class'  => 'col-price'
        ));

        if (Mage::helper('Mage_Catalog_Helper_Data')->isModuleEnabled('Mage_CatalogInventory')) {
            $this->addColumn('qty',
                array(
                    'header'=> Mage::helper('Mage_Catalog_Helper_Data')->__('Quantity'),
                    'width' => '100px',
                    'type'  => 'number',
                    'index' => 'qty',
                    'header_css_class'  => 'col-qty',
                    'column_css_class'  => 'col-qty'
            ));
        }

        $this->addColumn('visibility',
            array(
                'header'=> Mage::helper('Mage_Catalog_Helper_Data')->__('Visibility'),
                'width' => '70px',
                'index' => 'visibility',
                'type'  => 'options',
                'options' => Mage::getModel('Mage_Catalog_Model_Product_Visibility')->getOptionArray(),
                'header_css_class'  => 'col-visibility',
                'column_css_class'  => 'col-visibility'
        ));

        $this->addColumn('status',
            array(
                'header'=> Mage::helper('Mage_Catalog_Helper_Data')->__('Status'),
                'width' => '70px',
                'index' => 'status',
                'type'  => 'options',
                'options' => Mage::getSingleton('Mage_Catalog_Model_Product_Status')->getOptionArray(),
                'header_css_class'  => 'col-status',
                'column_css_class'  => 'col-status'
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('websites',
                array(
                    'header'=> Mage::helper('Mage_Catalog_Helper_Data')->__('Websites'),
                    'width' => '100px',
                    'sortable'  => false,
                    'index'     => 'websites',
                    'type'      => 'options',
                    'options'   => Mage::getModel('Mage_Core_Model_Website')->getCollection()->toOptionHash(),
                    'header_css_class'  => 'col-websites',
                    'column_css_class'  => 'col-websites'
            ));
        }

        $this->addColumn('edit',
            array(
                'header'    => Mage::helper('Mage_Catalog_Helper_Data')->__('Edit'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('Mage_Catalog_Helper_Data')->__('Edit'),
                        'url'     => array(
                            'base'=>'*/*/edit',
                            'params'=>array('store'=>$this->getRequest()->getParam('store'))
                        ),
                        'field'   => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'header_css_class'  => 'col-action',
                'column_css_class'  => 'col-action'
        ));

        if (Mage::helper('Mage_Catalog_Helper_Data')->isModuleEnabled('Mage_Rss')) {
            $this->addRssList('rss/catalog/notifystock', Mage::helper('Mage_Catalog_Helper_Data')->__('Notify Low Stock RSS'));
        }

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setTemplate('Mage_Catalog::product/grid/massaction_extended.phtml');
        $this->getMassactionBlock()->setFormFieldName('product');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'=> Mage::helper('Mage_Catalog_Helper_Data')->__('Delete'),
             'url'  => $this->getUrl('*/*/massDelete'),
             'confirm' => Mage::helper('Mage_Catalog_Helper_Data')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('Mage_Catalog_Model_Product_Status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('Mage_Catalog_Helper_Data')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));

        if ($this->_authorization->isAllowed('Mage_Catalog::update_attributes')){
            $this->getMassactionBlock()->addItem('attributes', array(
                'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Update Attributes'),
                'url'   => $this->getUrl('*/catalog_product_action_attribute/edit', array('_current'=>true))
            ));
        }

        Mage::dispatchEvent('adminhtml_catalog_product_grid_prepare_massaction', array('block' => $this));
        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array(
            'store'=>$this->getRequest()->getParam('store'),
            'id'=>$row->getId())
        );
    }
}
