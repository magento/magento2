<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Block\Role\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Data\Form;
use Magento\Framework\Phrase;
use Magento\User\Controller\Adminhtml\User\Role\SaveRole;

/**
 * Info
 *
 * User role tab info
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Info extends Generic implements TabInterface
{
    /**
     * Password input filed name
     */
    const IDENTITY_VERIFICATION_PASSWORD_FIELD = 'current_password';

    /**
     * Get tab label
     *
     * @return Phrase
     */
    public function getTabLabel()
    {
        return __('Role Info');
    }

    /**
     * Get tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Can show tab
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Is tab hidden
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Before html rendering
     *
     * @return $this
     */
    public function _beforeToHtml()
    {
        $this->_initForm();

        return parent::_beforeToHtml();
    }

    /**
     * Form initializatiion
     *
     * @return void
     */
    protected function _initForm()
    {
        /** @var Form $form */
        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Role Information')]);

        $fieldset->addField(
            'role_name',
            'text',
            [
                'name' => 'rolename',
                'label' => __('Role Name'),
                'id' => 'role_name',
                'class' => 'required-entry',
                'required' => true
            ]
        );

        $fieldset->addField('role_id', 'hidden', ['name' => 'role_id', 'id' => 'role_id']);

        $fieldset->addField('in_role_user', 'hidden', ['name' => 'in_role_user', 'id' => 'in_role_userz']);

        $fieldset->addField('in_role_user_old', 'hidden', ['name' => 'in_role_user_old']);

        $verificationFieldset = $form->addFieldset(
            'current_user_verification_fieldset',
            ['legend' => __('Current User Identity Verification')]
        );
        $verificationFieldset->addField(
            self::IDENTITY_VERIFICATION_PASSWORD_FIELD,
            'password',
            [
                'name' => self::IDENTITY_VERIFICATION_PASSWORD_FIELD,
                'label' => __('Your Password'),
                'id' => self::IDENTITY_VERIFICATION_PASSWORD_FIELD,
                'title' => __('Your Password'),
                'class' => 'validate-current-password required-entry',
                'required' => true
            ]
        );

        $data =  ['in_role_user_old' => $this->getOldUsers()];
        if ($this->getRole() && is_array($this->getRole()->getData())) {
            $data = array_merge($this->getRole()->getData(), $data);
        }
        $form->setValues($data);
        $this->setForm($form);
    }

    /**
     * Get old Users Form Data
     *
     * @return null|string
     */
    protected function getOldUsers()
    {
        return $this->_coreRegistry->registry(
            SaveRole::IN_ROLE_OLD_USER_FORM_DATA_SESSION_KEY
        );
    }
}
