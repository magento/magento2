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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml tags detail for customer report grid block
 *
 * @category   Mage
 * @package    Mage_Tag
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Tag_Block_Adminhtml_Report_Customer_Detail_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    protected function _construct()
    {
        parent::_construct();
        $this->setId('customers_grid');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('Mage_Tag_Model_Tag')
            ->getEntityCollection()
            ->joinAttribute('original_name', 'catalog_product/name', 'entity_id')
            ->addCustomerFilter($this->getRequest()->getParam('id'))
            ->addStatusFilter(Mage_Tag_Model_Tag::STATUS_APPROVED)
            ->addStoresVisibility()
            ->setActiveFilter()
            ->addGroupByTag()
            ->setRelationId();

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('name', array(
            'header'    =>Mage::helper('Mage_Tag_Helper_Data')->__('Product Name'),
            'index'     =>'original_name'
        ));

        $this->addColumn('tag_name', array(
            'header'    =>Mage::helper('Mage_Tag_Helper_Data')->__('Tag Name'),
            'index'     =>'tag_name'
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('visible', array(
                'header'    => Mage::helper('Mage_Tag_Helper_Data')->__('Visible In'),
                'index'     => 'stores',
                'type'      => 'store',
                'sortable'  => false,
                'store_view'=> true
            ));

            $this->addColumn('added_in', array(
                'header'    =>Mage::helper('Mage_Tag_Helper_Data')->__('Submitted In'),
                'index'     =>'store_id',
                'type'      =>'store',
                'store_view'=>true
            ));
        }

        $this->addColumn('created_at', array(
            'header'    =>Mage::helper('Mage_Tag_Helper_Data')->__('Submitted On'),
            'width'     => '140px',
            'type'      => 'datetime',
            'index'     => 'created_at'
        ));

        $this->setFilterVisibility(false);

        $this->addExportType('*/*/exportCustomerDetailCsv', Mage::helper('Mage_Tag_Helper_Data')->__('CSV'));
        $this->addExportType('*/*/exportCustomerDetailExcel', Mage::helper('Mage_Tag_Helper_Data')->__('Excel XML'));

        return parent::_prepareColumns();
    }
}
