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
 * @package     Mage_XmlConnect
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Application grid block
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Block_Adminhtml_Mobile_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('mobile_apps_grid');
        $this->setDefaultSort('application_id');
        $this->setDefaultDir('ASC');
    }

    /**
     * Initialize grid data collection
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('Mage_XmlConnect_Model_Application')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Declare grid columns
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('name', array(
            'header'    => $this->__('App Name'),
            'align'     => 'left',
            'index'     => 'name',
        ));

        $this->addColumn('code', array(
            'header'    => $this->__('App Code'),
            'align'     => 'left',
            'index'     => 'code',
            'width'     => '200',
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'        => $this->__('Store View'),
                'index'         => 'store_id',
                'type'          => 'store',
                'store_view'    => true,
                'sortable'      => false,
                'width'         => '250',
            ));
        }

        $this->addColumn('type', array(
            'header'    => $this->__('Device'),
            'type'      => 'text',
            'index'     => 'type',
            'align'     => 'center',
            'filter'    => 'Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select',
            'options'   => Mage::helper('Mage_XmlConnect_Helper_Data')->getSupportedDevices(),
            'renderer'  => 'Mage_XmlConnect_Block_Adminhtml_Mobile_Grid_Renderer_Type',
        ));

        $this->addColumn('status', array(
            'header'    => $this->__('Status'),
            'index'     => 'status',
            'renderer'  => 'Mage_XmlConnect_Block_Adminhtml_Mobile_Grid_Renderer_Bool',
            'align'     => 'center',
            'filter'    => 'Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select',
            'options'   => Mage::helper('Mage_XmlConnect_Helper_Data')->getStatusOptions(),

        ));

        return parent::_prepareColumns();
    }

    /**
     * Row click url
     *
     * @param Mage_Catalog_Model_Product|Varien_Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('application_id' => $row->getId()));
    }
}
