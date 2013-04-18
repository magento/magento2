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
 * Adminhtml permissions user grid
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Api_User_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    protected function _construct()
    {
        parent::_construct();
        $this->setId('permissionsUserGrid');
        $this->setDefaultSort('username');
        $this->setDefaultDir('asc');
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('Mage_Api_Model_Resource_User_Collection');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('user_id', array(
            'header'    => Mage::helper('Mage_Adminhtml_Helper_Data')->__('ID'),
            'width'     => 5,
            'align'     => 'right',
            'sortable'  => true,
            'index'     => 'user_id'
        ));

        $this->addColumn('username', array(
            'header'    => Mage::helper('Mage_Adminhtml_Helper_Data')->__('User Name'),
            'index'     => 'username'
        ));

        $this->addColumn('firstname', array(
            'header'    => Mage::helper('Mage_Adminhtml_Helper_Data')->__('First Name'),
            'index'     => 'firstname'
        ));

        $this->addColumn('lastname', array(
            'header'    => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Last Name'),
            'index'     => 'lastname'
        ));

        $this->addColumn('email', array(
            'header'    => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Email'),
            'width'     => 40,
            'align'     => 'left',
            'index'     => 'email'
        ));

        $this->addColumn('is_active', array(
            'header'    => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Status'),
            'index'     => 'is_active',
            'type'      => 'options',
            'options'   => array('1' => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Active'), '0' => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Inactive')),
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('user_id' => $row->getId()));
    }

    public function getGridUrl()
    {
        //$uid = $this->getRequest()->getParam('user_id');
        return $this->getUrl('*/*/roleGrid', array());
    }

}
