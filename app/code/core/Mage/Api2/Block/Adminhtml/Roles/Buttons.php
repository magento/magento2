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
 * @copyright  Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Block for rendering buttons
 *
 * @category   Mage
 * @package    Mage_Api2
 * @author     Magento Core Team <core@magentocommerce.com>
 * @method Mage_Api2_Block_Adminhtml_Roles_Buttons setRole(Mage_Api2_Model_Acl_Global_Role $role)
 * @method Mage_Api2_Model_Acl_Global_Role getRole()
 */
class Mage_Api2_Block_Adminhtml_Roles_Buttons extends Mage_Adminhtml_Block_Template
{
    /**
     * Construct
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('role/buttons.phtml');
    }

    /**
     * Preparing global layout
     *
     * @return Mage_Api2_Block_Adminhtml_Roles_Buttons
     */
    protected function _prepareLayout()
    {
        $buttons = array(
            'backButton'    => array(
                'label'     => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Back'),
                'onclick'   => sprintf("window.location.href='%s';", $this->getUrl('*/*/')),
                'class'     => 'back'
            ),
            'resetButton'   => array(
                'label'     => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Reset'),
                'onclick'   => 'window.location.reload()'
            ),
            'saveButton'    => array(
                'label'     => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Save Role'),
                'class'     => 'save',
                'data_attr'  => array(
                    'widget-button' => array('event' => 'save', 'related' => '#role-edit-form')
                )
            ),
            'deleteButton'  => array(
                'label'     => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Delete Role'),
                'onclick'   => '',  //roleId is not set at this moment, so we set script later
                'class'     => 'delete'
            ),
        );

        foreach ($buttons as $name=>$data) {
            $button = $this->getLayout()->createBlock('Mage_Adminhtml_Block_Widget_Button')->setData($data);
            $this->setChild($name, $button);
        }

        return parent::_prepareLayout();
    }

    /**
     * Get back button HTML
     *
     * @return string
     */
    public function getBackButtonHtml()
    {
        return $this->getChildHtml('backButton');
    }

    /**
     * Get reset button HTML
     *
     * @return string
     */
    public function getResetButtonHtml()
    {
        return $this->getChildHtml('resetButton');
    }

    /**
     * Get save button HTML
     *
     * @return string
     */
    public function getSaveButtonHtml()
    {
        return $this->getChildHtml('saveButton');
    }

    /**
     * Get delete button HTML
     *
     * @return string
     */
    public function getDeleteButtonHtml()
    {
        if(!$this->getRole() || !$this->getRole()->getId()
            || Mage_Api2_Model_Acl_Global_Role::isSystemRole($this->getRole())) {

            return '';
        }

        $this->getChildBlock('deleteButton')->setData('onclick', sprintf("deleteConfirm('%s', '%s')",
            Mage::helper('Mage_Adminhtml_Helper_Data')->__('Are you sure you want to do this?'),
            $this->getUrl('*/*/delete', array('id' => $this->getRole()->getId()))
        ));

        return $this->getChildHtml('deleteButton');
    }

    /**
     * Get block caption
     *
     * @return string
     */
    public function getCaption()
    {
        return $this->getRole() && $this->getRole()->getId()
                ? ($this->__('Edit Role') . " '{$this->escapeHtml($this->getRole()->getRoleName())}'")
                : $this->__('Add New Role');
    }
}
