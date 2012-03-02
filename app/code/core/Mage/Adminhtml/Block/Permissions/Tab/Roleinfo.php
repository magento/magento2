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
 * implementing now
 *
 */
class Mage_Adminhtml_Block_Permissions_Tab_Roleinfo extends Mage_Adminhtml_Block_Widget_Form implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function getTabLabel()
    {
        return Mage::helper('Mage_Adminhtml_Helper_Data')->__('Role Info');
    }

    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }

    public function _beforeToHtml() {
        $this->_initForm();

        return parent::_beforeToHtml();
    }

    protected function _initForm()
    {
        $roleId = $this->getRequest()->getParam('rid');

        $form = new Varien_Data_Form();

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('Mage_Adminhtml_Helper_Data')->__('Role Information')));

        $fieldset->addField('role_name', 'text',
            array(
                'name'  => 'rolename',
                'label' => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Role Name'),
                'id'    => 'role_name',
                'class' => 'required-entry',
                'required' => true,
            )
        );

        $fieldset->addField('role_id', 'hidden',
            array(
                'name'  => 'role_id',
                'id'    => 'role_id',
            )
        );

        $fieldset->addField('in_role_user', 'hidden',
            array(
                'name'  => 'in_role_user',
                'id'    => 'in_role_userz',
            )
        );

        $fieldset->addField('in_role_user_old', 'hidden', array('name' => 'in_role_user_old'));

        $form->setValues($this->getRole()->getData());
        $this->setForm($form);
    }
}
