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
 * Adminhtml sales order's statuses grid
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Sales_Order_Status_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('sales_order_status_grid');
        //$this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
        $this->setDefaultSort('state');
        $this->setDefaultDir('DESC');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('Mage_Sales_Model_Resource_Order_Status_Collection');
        $collection->joinStates();
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('label', array(
            'header' => Mage::helper('Mage_Sales_Helper_Data')->__('Status'),
            'index' => 'label',
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('Mage_Sales_Helper_Data')->__('Status Code'),
            'type'  => 'text',
            'index' => 'status',
            'filter_index' => 'main_table.status',
            'width'     => '200px',
        ));

        $this->addColumn('is_default', array(
            'header'    => Mage::helper('Mage_Sales_Helper_Data')->__('Default Status'),
            'index'     => 'is_default',
            'width'     => '100px',
            'type'      => 'options',
            'options'   => array(0 => $this->__('No'), 1 => $this->__('Yes')),
            'sortable'  => false,
        ));

        $this->addColumn('state', array(
            'header'=> Mage::helper('Mage_Sales_Helper_Data')->__('State Code [State Title]'),
            'type'  => 'text',
            'index' => 'state',
            'width'     => '250px',
            'frame_callback' => array($this, 'decorateState')
        ));

        $this->addColumn('unassign', array(
            'header'    => Mage::helper('Mage_Sales_Helper_Data')->__('Action'),
            'index'     => 'unassign',
            'width'     => '100px',
            'type'      => 'text',
            'frame_callback' => array($this, 'decorateAction'),
            'sortable'  => false,
            'filter'    => false,
        ));

        return parent::_prepareColumns();
    }

    /**
     * Decorate status column values
     *
     * @return string
     */
    public function decorateState($value, $row, $column, $isExport)
    {
        if ($value) {
            $cell = $value . ' [' . Mage::getSingleton('Mage_Sales_Model_Order_Config')->getStateLabel($value) . ']';
        } else {
            $cell = $value;
        }
        return $cell;
    }

    public function decorateAction($value, $row, $column, $isExport)
    {
        $cell = '';
        $state = $row->getState();
        if (!empty($state)) {
            $url = $this->getUrl(
                '*/*/unassign',
                array('status' => $row->getStatus(), 'state' => $row->getState())
            );
            $label = Mage::helper('Mage_Sales_Helper_Data')->__('Unassign');
            $cell = '<a href="' . $url . '">' . $label . '</a>';
        }
        return $cell;
    }

    /**
     * No pegination for this grid
     */
    protected function _preparePage()
    {
        return $this;
    }


    public function getRowUrl($row)
    {
        return $this->getUrl('*/sales_order_status/edit', array('status' => $row->getStatus()));
    }
}
