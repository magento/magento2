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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml store content block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Adminhtml_Block_System_Store_Store extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    protected function _construct()
    {
        $this->_controller  = 'system_store';
        $this->_headerText  = Mage::helper('Mage_Adminhtml_Helper_Data')->__('Manage Stores');
        parent::_construct();
    }

    protected function _prepareLayout()
    {
        /* Update default add button to add website button */
        $this->_updateButton('add', 'label', Mage::helper('Mage_Core_Helper_Data')->__('Create Website'));
        $this->_updateButton('add', 'onclick', "setLocation('".$this->getUrl('*/*/newWebsite')."')");

        /* Add Store Group button */
        $this->_addButton('add_group', array(
            'label'     => Mage::helper('Mage_Core_Helper_Data')->__('Create Store'),
            'onclick'   => 'setLocation(\'' . $this->getUrl('*/*/newGroup') .'\')',
            'class'     => 'add',
        ));

        /* Add Store button */
        $this->_addButton('add_store', array(
            'label'     => Mage::helper('Mage_Core_Helper_Data')->__('Create Store View'),
            'onclick'   => 'setLocation(\'' . $this->getUrl('*/*/newStore') .'\')',
            'class'     => 'add',
        ));

        return parent::_prepareLayout();
    }

    public function getAddNewButtonHtml()
    {
        return join(' ', array(
            $this->getChildHtml('add_new_website'),
            $this->getChildHtml('add_new_group'),
            $this->getChildHtml('add_new_store')
        ));
    }
}
