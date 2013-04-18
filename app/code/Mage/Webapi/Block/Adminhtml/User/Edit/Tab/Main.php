<?php
/**
 * Web API user edit form.
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @method Mage_Webapi_Block_Adminhtml_User_Edit setApiUser() setApiUser(Mage_Webapi_Model_Acl_User $user)
 * @method Mage_Webapi_Model_Acl_User getApiUser() getApiUser()
 */
class Mage_Webapi_Block_Adminhtml_User_Edit_Tab_Main extends Mage_Backend_Block_Widget_Form
{
    /**
     * Prepare Form.
     *
     * @return Mage_Webapi_Block_Adminhtml_User_Edit_Tab_Main
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend' => $this->__('Account Information'))
        );

        $user = $this->getApiUser();
        if ($user->getId()) {
            $fieldset->addField('user_id', 'hidden', array(
                'name' => 'user_id',
                'value' => $user->getId()
            ));
        }

        $fieldset->addField('company_name', 'text', array(
            'name' => 'company_name',
            'id' => 'company_name',
            'required' => false,
            'label' => $this->__('Company Name'),
            'title' => $this->__('Company Name'),
        ));

        $fieldset->addField('contact_email', 'text', array(
            'name' => 'contact_email',
            'id' => 'contact_email',
            'class' => 'validate-email',
            'required' => true,
            'label' => $this->__('Contact Email'),
            'title' => $this->__('Contact Email'),
        ));

        $fieldset->addField('api_key', 'text', array(
            'name' => 'api_key',
            'id' => 'api_key',
            'required' => true,
            'label' => $this->__('API Key'),
            'title' => $this->__('API Key'),
        ));

        $fieldset->addField('secret', 'text', array(
            'name' => 'secret',
            'id' => 'secret',
            'required' => true,
            'label' => $this->__('API Secret'),
            'title' => $this->__('API Secret'),
        ));

        if ($user) {
            $form->setValues($user->getData());
        }
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
