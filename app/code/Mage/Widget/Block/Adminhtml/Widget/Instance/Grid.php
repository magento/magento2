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
 * @package     Mage_Widget
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Widget Instance grid block
 *
 * @category    Mage
 * @package     Mage_Widget
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Widget_Block_Adminhtml_Widget_Instance_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Internal constructor
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('widgetInstanceGrid');
        $this->setDefaultSort('instance_id');
        $this->setDefaultDir('ASC');
    }

    /**
     * Prepare grid collection object
     *
     * @return Mage_Widget_Block_Adminhtml_Widget_Instance_Grid
     */
    protected function _prepareCollection()
    {
        /* @var $collection Mage_Widget_Model_Resource_Widget_Instance_Collection */
        $collection = Mage::getModel('Mage_Widget_Model_Widget_Instance')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare grid columns
     *
     * @return Mage_Widget_Block_Adminhtml_Widget_Instance_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('instance_id', array(
            'header'    => Mage::helper('Mage_Widget_Helper_Data')->__('Widget ID'),
            'align'     => 'left',
            'index'     => 'instance_id',
        ));

        $this->addColumn('title', array(
            'header'    => Mage::helper('Mage_Widget_Helper_Data')->__('Widget Instance Title'),
            'align'     => 'left',
            'index'     => 'title',
        ));

        $this->addColumn('type', array(
            'header'    => Mage::helper('Mage_Widget_Helper_Data')->__('Type'),
            'align'     => 'left',
            'index'     => 'instance_type',
            'type'      => 'options',
            'options'   => $this->getTypesOptionsArray()
        ));

        $this->addColumn('theme_id', array(
            'header'    => Mage::helper('Mage_Widget_Helper_Data')->__('Design Theme'),
            'align'     => 'left',
            'index'     => 'theme_id',
            'type'      => 'options',
            'options'   => Mage::getResourceModel('Mage_Core_Model_Resource_Theme_Collection')->toOptionHash(),
            'with_empty' => true,
        ));

        $this->addColumn('sort_order', array(
            'header'    => Mage::helper('Mage_Widget_Helper_Data')->__('Sort Order'),
            'width'     => '100',
            'align'     => 'center',
            'index'     => 'sort_order',
        ));

        return parent::_prepareColumns();
    }

    /**
     * Retrieve array (widget_type => widget_name) of available widgets
     *
     * @return array
     */
    public function getTypesOptionsArray()
    {
        $widgets = array();
        $widgetsOptionsArr = Mage::getModel('Mage_Widget_Model_Widget_Instance')->getWidgetsOptionArray();
        foreach ($widgetsOptionsArr as $widget) {
            $widgets[$widget['value']] = $widget['label'];
        }
        return $widgets;
    }

    /**
     * Row click url
     *
     * @param Mage_Widget_Model_Widget_Instance $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('instance_id' => $row->getId()));
    }
}
