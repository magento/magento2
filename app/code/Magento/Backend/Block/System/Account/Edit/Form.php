<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\System\Account\Edit;

/**
 * Adminhtml edit admin user account form
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    const IDENTITY_VERIFICATION_PASSWORD_FIELD = 'current_password';

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_authSession;

    /**
     * @var \Magento\User\Model\UserFactory
     */
    protected $_userFactory;

    /**
     * @var \Magento\Framework\Locale\ListsInterface
     */
    protected $_localeLists;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\User\Model\UserFactory $userFactory
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\Locale\ListsInterface $localeLists
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\User\Model\UserFactory $userFactory,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Locale\ListsInterface $localeLists,
        array $data = []
    ) {
        $this->_userFactory = $userFactory;
        $this->_authSession = $authSession;
        $this->_localeLists = $localeLists;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareForm()
    {
        $userId = $this->_authSession->getUser()->getId();
        $user = $this->_userFactory->create()->load($userId);
        $user->unsetData('password');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Account Information')]);

        $fieldset->addField(
            'username',
            'text',
            ['name' => 'username', 'label' => __('User Name'), 'title' => __('User Name'), 'required' => true]
        );

        $fieldset->addField(
            'firstname',
            'text',
            ['name' => 'firstname', 'label' => __('First Name'), 'title' => __('First Name'), 'required' => true]
        );

        $fieldset->addField(
            'lastname',
            'text',
            ['name' => 'lastname', 'label' => __('Last Name'), 'title' => __('Last Name'), 'required' => true]
        );

        $fieldset->addField('user_id', 'hidden', ['name' => 'user_id']);

        $fieldset->addField(
            'email',
            'text',
            ['name' => 'email', 'label' => __('Email'), 'title' => __('User Email'), 'required' => true]
        );

        $fieldset->addField(
            'password',
            'password',
            [
                'name' => 'password',
                'label' => __('New Password'),
                'title' => __('New Password'),
                'class' => 'validate-admin-password admin__control-text'
            ]
        );

        $fieldset->addField(
            'confirmation',
            'password',
            [
                'name' => 'password_confirmation',
                'label' => __('Password Confirmation'),
                'class' => 'validate-cpassword admin__control-text'
            ]
        );

        $fieldset->addField(
            'interface_locale',
            'select',
            [
                'name' => 'interface_locale',
                'label' => __('Interface Locale'),
                'title' => __('Interface Locale'),
                'values' => $this->_localeLists->getTranslatedOptionLocales(),
                'class' => 'select'
            ]
        );

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
                'class' => 'validate-current-password required-entry admin__control-text',
                'required' => true
            ]
        );

        $data = $user->getData();
        unset($data[self::IDENTITY_VERIFICATION_PASSWORD_FIELD]);
        $form->setValues($data);
        $form->setAction($this->getUrl('adminhtml/system_account/save'));
        $form->setMethod('post');
        $form->setUseContainer(true);
        $form->setId('edit_form');

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
