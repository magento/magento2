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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\User\Block\User\Edit\Tab;

/**
 * Cms page edit form main tab
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic
{
    const CURRENT_USER_PASSWORD_FIELD = 'current_password';

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_authSession;

    /**
     * @var \Magento\Framework\Locale\ListsInterface
     */
    protected $_LocaleLists;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\Locale\ListsInterface $localeLists
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Locale\ListsInterface $localeLists,
        array $data = array()
    ) {
        $this->_authSession = $authSession;
        $this->_LocaleLists = $localeLists;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form fields
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return \Magento\Backend\Block\Widget\Form
     */
    protected function _prepareForm()
    {
        /** @var $model \Magento\User\Model\User */
        $model = $this->_coreRegistry->registry('permissions_user');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('user_');

        $baseFieldset = $form->addFieldset('base_fieldset', array('legend' => __('Account Information')));

        if ($model->getUserId()) {
            $baseFieldset->addField('user_id', 'hidden', array('name' => 'user_id'));
        } else {
            if (!$model->hasData('is_active')) {
                $model->setIsActive(1);
            }
        }

        $baseFieldset->addField(
            'username',
            'text',
            array(
                'name' => 'username',
                'label' => __('User Name'),
                'id' => 'username',
                'title' => __('User Name'),
                'required' => true
            )
        );

        $baseFieldset->addField(
            'firstname',
            'text',
            array(
                'name' => 'firstname',
                'label' => __('First Name'),
                'id' => 'firstname',
                'title' => __('First Name'),
                'required' => true
            )
        );

        $baseFieldset->addField(
            'lastname',
            'text',
            array(
                'name' => 'lastname',
                'label' => __('Last Name'),
                'id' => 'lastname',
                'title' => __('Last Name'),
                'required' => true
            )
        );

        $baseFieldset->addField(
            'email',
            'text',
            array(
                'name' => 'email',
                'label' => __('Email'),
                'id' => 'customer_email',
                'title' => __('User Email'),
                'class' => 'required-entry validate-email',
                'required' => true
            )
        );

        $isNewObject = $model->isObjectNew();
        if ($isNewObject) {
            $passwordLabel = __('Password');
        } else {
            $passwordLabel = __('New Password');
        }
        $confirmationLabel = __('Password Confirmation');
        $this->_addPasswordFields($baseFieldset, $passwordLabel, $confirmationLabel, $isNewObject);

        $baseFieldset->addField(
            'interface_locale',
            'select',
            array(
                'name' => 'interface_locale',
                'label' => __('Interface Locale'),
                'title' => __('Interface Locale'),
                'values' => $this->_LocaleLists->getTranslatedOptionLocales(),
                'class' => 'select'
            )
        );

        if ($this->_authSession->getUser()->getId() != $model->getUserId()) {
            $baseFieldset->addField(
                'is_active',
                'select',
                array(
                    'name' => 'is_active',
                    'label' => __('This account is'),
                    'id' => 'is_active',
                    'title' => __('Account Status'),
                    'class' => 'input-select',
                    'style' => 'width: 80px',
                    'options' => array('1' => __('Active'), '0' => __('Inactive'))
                )
            );
        }

        $baseFieldset->addField('user_roles', 'hidden', array('name' => 'user_roles', 'id' => '_user_roles'));



        $currentUserVerificationFieldset = $form->addFieldset(
            'current_user_verification_fieldset',
            ['legend' => __('Current User Identity Verification')]
        );
        $currentUserVerificationFieldset->addField(
            self::CURRENT_USER_PASSWORD_FIELD,
            'password',
            array(
                'name' => self::CURRENT_USER_PASSWORD_FIELD,
                'label' => __('Your Password'),
                'id' => self::CURRENT_USER_PASSWORD_FIELD,
                'title' => __('Your Password'),
                'class' => 'input-text validate-current-password required-entry',
                'required' => true
            )
        );

        $data = $model->getData();
        unset($data['password']);
        unset($data[self::CURRENT_USER_PASSWORD_FIELD]);
        $form->setValues($data);

        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Add password input fields
     *
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     * @param string $passwordLabel
     * @param string $confirmationLabel
     * @param bool $isRequired
     * @return void
     */
    protected function _addPasswordFields(
        \Magento\Framework\Data\Form\Element\Fieldset $fieldset,
        $passwordLabel,
        $confirmationLabel,
        $isRequired = false
    ) {
        $requiredFieldClass = $isRequired ? ' required-entry' : '';
        $fieldset->addField(
            'password',
            'password',
            array(
                'name' => 'password',
                'label' => $passwordLabel,
                'id' => 'customer_pass',
                'title' => $passwordLabel,
                'class' => 'input-text validate-admin-password' . $requiredFieldClass,
                'required' => $isRequired
            )
        );
        $fieldset->addField(
            'confirmation',
            'password',
            array(
                'name' => 'password_confirmation',
                'label' => $confirmationLabel,
                'id' => 'confirmation',
                'title' => $confirmationLabel,
                'class' => 'input-text validate-cpassword' . $requiredFieldClass,
                'required' => $isRequired
            )
        );
    }
}
