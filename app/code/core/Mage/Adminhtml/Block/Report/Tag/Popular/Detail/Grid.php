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
 * Adminhtml tags detail for product report grid block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Report_Tag_Popular_Detail_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('tag_grid');
    }

    /**
     * Prepare collection for grid
     *
     * @return Mage_Adminhtml_Block_Report_Tag_Popular_Detail_Grid
     */
    protected function _prepareCollection()
    {
        /* @var $collection Mage_Reports_Model_Resource_Tag_Customer_Collection */
        $collection = Mage::getResourceModel('Mage_Reports_Model_Resource_Tag_Customer_Collection');
        $collection->addStatusFilter(Mage::getModel('Mage_Tag_Model_Tag')->getApprovedStatus())
                ->addTagFilter($this->getRequest()->getParam('id'))
                ->addDescOrder()
                ->addProductName();

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Form columns for the grid
     *
     * @return Mage_Adminhtml_Block_Report_Tag_Popular_Detail_Grid
     */
    protected function _prepareColumns()
    {

        $this->addColumn('firstname', array(
            'header'    =>Mage::helper('Mage_Reports_Helper_Data')->__('First Name'),
            'sortable'  => false,
            'index'     =>'firstname'
        ));

        $this->addColumn('lastname', array(
            'header'    =>Mage::helper('Mage_Reports_Helper_Data')->__('Last Name'),
            'sortable'  => false,
            'index'     =>'lastname'
        ));

        $this->addColumn('product', array(
            'header'    =>Mage::helper('Mage_Reports_Helper_Data')->__('Product Name'),
            'sortable'  => false,
            'index'     =>'product'
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('added_in', array(
                'header'    => Mage::helper('Mage_Reports_Helper_Data')->__('Submitted In'),
                'sortable'  => false,
                'index'     => 'store_id',
                'type'      => 'store',
                'store_view'=> true
            ));
        }

        $this->setFilterVisibility(false);

        $this->addExportType('*/*/exportTagDetailCsv', Mage::helper('Mage_Reports_Helper_Data')->__('CSV'));
        $this->addExportType('*/*/exportTagDetailExcel', Mage::helper('Mage_Reports_Helper_Data')->__('Excel XML'));

        return parent::_prepareColumns();
    }

}
