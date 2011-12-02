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
 * Adminhtml search report grid block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Report_Search_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Initialize Grid Properties
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('searchReportGrid');
        $this->setDefaultSort('query_id');
        $this->setDefaultDir('desc');
    }

    /**
     * Prepare Search Report collection for grid
     *
     * @return Mage_Adminhtml_Block_Report_Search_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('Mage_CatalogSearch_Model_Resource_Query_Collection');
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare Grid columns
     *
     * @return Mage_Adminhtml_Block_Report_Search_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('query_id', array(
            'header'    =>Mage::helper('Mage_Reports_Helper_Data')->__('ID'),
            'width'     =>'50px',
            'filter'    =>false,
            'index'     =>'query_id',
            'type'      =>'number'
        ));

        $this->addColumn('query_text', array(
            'header'    =>Mage::helper('Mage_Reports_Helper_Data')->__('Search Query'),
            'index'     =>'query_text'
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'        => Mage::helper('Mage_Catalog_Helper_Data')->__('Store'),
                'index'         => 'store_id',
                'type'          => 'store',
                'store_view'    => true,
                'sortable'      => false
            ));
        }

        $this->addColumn('num_results', array(
            'header'    =>Mage::helper('Mage_Reports_Helper_Data')->__('Results'),
            'width'     =>'50px',
            'align'     =>'right',
            'type'      =>'number',
            'index'     =>'num_results'
        ));

        $this->addColumn('popularity', array(
            'header'    =>Mage::helper('Mage_Reports_Helper_Data')->__('Hits'),
            'width'     =>'50px',
            'align'     =>'right',
            'type'      =>'number',
            'index'     =>'popularity'
        ));

        $this->addExportType('*/*/exportSearchCsv', Mage::helper('Mage_Reports_Helper_Data')->__('CSV'));
        $this->addExportType('*/*/exportSearchExcel', Mage::helper('Mage_Reports_Helper_Data')->__('Excel XML'));

        return parent::_prepareColumns();
    }

    /**
     * Retrieve Row Click callback URL
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/catalog_search/edit', array('id' => $row->getId()));
    }
}

