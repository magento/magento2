<?php
/**
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Backend\Controller\Adminhtml\System\Account;

class Save extends \Magento\Backend\Controller\Adminhtml\System\Account
{
    /**
     * Saving edited user information
     *
     * @return void
     */
    public function execute()
    {
        $userId = $this->_objectManager->get('Magento\Backend\Model\Auth\Session')->getUser()->getId();
        $password = (string)$this->getRequest()->getParam('password');
        $passwordConfirmation = (string)$this->getRequest()->getParam('password_confirmation');
        $interfaceLocale = (string)$this->getRequest()->getParam('interface_locale', false);

        /** @var $user \Magento\User\Model\User */
        $user = $this->_objectManager->create('Magento\User\Model\User')->load($userId);

        $user->setId(
            $userId
        )->setUsername(
            $this->getRequest()->getParam('username', false)
        )->setFirstname(
            $this->getRequest()->getParam('firstname', false)
        )->setLastname(
            $this->getRequest()->getParam('lastname', false)
        )->setEmail(
            strtolower($this->getRequest()->getParam('email', false))
        );

        if ($this->_objectManager->get('Magento\Framework\Locale\Validator')->isValid($interfaceLocale)) {
            $user->setInterfaceLocale($interfaceLocale);
            $this->_objectManager->get(
                'Magento\Backend\Model\Locale\Manager'
            )->switchBackendInterfaceLocale(
                $interfaceLocale
            );
        }
        /** Before updating admin user data, ensure that password of current admin user is entered and is correct */
        $currentUserPasswordField = \Magento\User\Block\User\Edit\Tab\Main::CURRENT_USER_PASSWORD_FIELD;
        $currentUserPassword = $this->getRequest()->getParam($currentUserPasswordField);
        $isCurrentUserPasswordValid = !empty($currentUserPassword) && is_string($currentUserPassword);
        try {
            if (!($isCurrentUserPasswordValid && $user->verifyIdentity($currentUserPassword))) {
                throw new \Magento\Backend\Model\Auth\Exception(
                    __('You have entered an invalid password for current user.')
                );
            }
            if ($password !== '') {
                $user->setPassword($password);
                $user->setPasswordConfirmation($passwordConfirmation);
            }
            $user->save();
            $user->sendPasswordResetNotificationEmail();
            $this->messageManager->addSuccess(__('The account has been saved.'));
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->messageManager->addMessages($e->getMessages());
            if ($e->getMessage()) {
                $this->messageManager->addError($e->getMessage());
            }
        } catch (\Exception $e) {
            $this->messageManager->addError(__('An error occurred while saving account.'));
        }
        $this->getResponse()->setRedirect($this->getUrl("*/*/"));
    }
}
