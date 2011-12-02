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
 * Adminhtml tags by products report grid block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Report_Tag_Product_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('gridProducts');
    }

    protected function _prepareCollection()
    {

        $collection = Mage::getResourceModel('Mage_Reports_Model_Resource_Tag_Product_Collection');

        $collection->addUniqueTagedCount()
            ->addAllTagedCount()
            ->addStatusFilter(Mage::getModel('Mage_Tag_Model_Tag')->getApprovedStatus())
            ->addGroupByProduct();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $this->addColumn('entity_id', array(
            'header'    =>Mage::helper('Mage_Reports_Helper_Data')->__('ID'),
            'width'     =>'50px',
            'align'     =>'right',
            'index'     =>'entity_id'
        ));

        $this->addColumn('name', array(
            'header'    =>Mage::helper('Mage_Reports_Helper_Data')->__('Product Name'),
            'index'     =>'name'
        ));

        $this->addColumn('utaged', array(
            'header'    =>Mage::helper('Mage_Reports_Helper_Data')->__('Number of Unique Tags'),
            'width'     =>'50px',
            'align'     =>'right',
            'index'     =>'utaged'
        ));

        $this->addColumn('taged', array(
            'header'    =>Mage::helper('Mage_Reports_Helper_Data')->__('Number of Total Tags'),
            'width'     =>'50px',
            'align'     =>'right',
            'index'     =>'taged'
        ));

        $this->addColumn('action',
            array(
                'header'    => Mage::helper('Mage_Catalog_Helper_Data')->__('Action'),
                'width'     => '100%',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('Mage_Catalog_Helper_Data')->__('Show Tags'),
                        'url'     => array(
                            'base'=>'*/*/productDetail'
                        ),
                        'field'   => 'id'
                    )
                ),
                'is_system' => true,
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
        ));

        $this->setFilterVisibility(false);

        $this->addExportType('*/*/exportProductCsv', Mage::helper('Mage_Reports_Helper_Data')->__('CSV'));
        $this->addExportType('*/*/exportProductExcel', Mage::helper('Mage_Reports_Helper_Data')->__('Excel XML'));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/productDetail', array('id'=>$row->getId()));
    }

}
