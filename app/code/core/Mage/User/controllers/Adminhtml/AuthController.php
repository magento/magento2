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
 * @category   Mage
 * @package    Mage_User
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Mage_User Auth controller
 *
 * @category   Mage
 * @package    Mage_User
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_User_Adminhtml_AuthController extends Mage_Backend_Controller_ActionAbstract
{
    /**
     * Forgot administrator password action
     */
    public function forgotpasswordAction()
    {
        $email = (string) $this->getRequest()->getParam('email');
        $params = $this->getRequest()->getParams();

        if (!empty($email) && !empty($params)) {
            // Validate received data to be an email address
            if (Zend_Validate::is($email, 'EmailAddress')) {
                $collection = Mage::getResourceModel('Mage_User_Model_Resource_User_Collection');
                /** @var $collection Mage_User_Model_Resource_User_Collection */
                $collection->addFieldToFilter('email', $email);
                $collection->load(false);

                if ($collection->getSize() > 0) {
                    foreach ($collection as $item) {
                        $user = Mage::getModel('Mage_User_Model_User')->load($item->getId());
                        if ($user->getId()) {
                            $newPassResetToken = Mage::helper('Mage_User_Helper_Data')
                                ->generateResetPasswordLinkToken();
                            $user->changeResetPasswordLinkToken($newPassResetToken);
                            $user->save();
                            $user->sendPasswordResetConfirmationEmail();
                        }
                        break;
                    }
                }
                // @codingStandardsIgnoreStart
                $this->_getSession()
                    ->addSuccess(Mage::helper('Mage_User_Helper_Data')->__('If there is an account associated with %s you will receive an email with a link to reset your password.', Mage::helper('Mage_User_Helper_Data')->escapeHtml($email)));
                // @codingStandardsIgnoreEnd
                $this->getResponse()->setRedirect(Mage::helper('Mage_Backend_Helper_Data')->getHomePageUrl());
                return;
            } else {
                $this->_getSession()->addError($this->__('Invalid email address.'));
            }
        } elseif (!empty($params)) {
            $this->_getSession()->addError(Mage::helper('Mage_User_Helper_Data')->__('The email address is empty.'));
        }
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Display reset forgotten password form
     *
     * User is redirected on this action when he clicks on the corresponding link in password reset confirmation email
     */
    public function resetPasswordAction()
    {
        $passwordResetToken = (string) $this->getRequest()->getQuery('token');
        $userId = (int) $this->getRequest()->getQuery('id');
        try {
            $this->_validateResetPasswordLinkToken($userId, $passwordResetToken);

            $this->loadLayout();

            $content = $this->getLayout()->getBlock('content');
            if ($content) {
                $content->setData('user_id', $userId)
                    ->setData('reset_password_link_token', $passwordResetToken);
            }

            $this->renderLayout();
        } catch (Exception $exception) {
            $this->_getSession()->addError(
                Mage::helper('Mage_User_Helper_Data')->__('Your password reset link has expired.')
            );
            $this->_redirect('*/auth/forgotpassword', array('_nosecret' => true));
            return;
        }
    }

    /**
     * Reset forgotten password
     *
     * Used to handle data recieved from reset forgotten password form
     */
    public function resetPasswordPostAction()
    {
        $passwordResetToken = (string) $this->getRequest()->getQuery('token');
        $userId = (int) $this->getRequest()->getQuery('id');
        $password = (string) $this->getRequest()->getPost('password');
        $passwordConfirmation = (string) $this->getRequest()->getPost('confirmation');

        try {
            $this->_validateResetPasswordLinkToken($userId, $passwordResetToken);
        } catch (Exception $exception) {
            $this->_getSession()->addError(
                Mage::helper('Mage_User_Helper_Data')->__('Your password reset link has expired.')
            );
            $this->getResponse()->setRedirect(Mage::helper('Mage_Backend_Helper_Data')->getHomePageUrl());
            return;
        }

        $errorMessages = array();
        if (iconv_strlen($password) <= 0) {
            array_push(
                $errorMessages,
                Mage::helper('Mage_User_Helper_Data')->__('New password field cannot be empty.')
            );
        }
        /** @var $user Mage_User_Model_User */
        $user = Mage::getModel('Mage_User_Model_User')->load($userId);

        $user->setNewPassword($password);
        $user->setPasswordConfirmation($passwordConfirmation);
        $validationErrors = $user->validate();
        if (is_array($validationErrors)) {
            $errorMessages = array_merge($errorMessages, $validationErrors);
        }

        if (!empty($errorMessages)) {
            foreach ($errorMessages as $errorMessage) {
                $this->_getSession()->addError($errorMessage);
            }

            $this->_redirect('*/auth/resetpassword', array(
                '_nosecret' => true,
                '_query' => array(
                    'id' => $userId,
                    'token' => $passwordResetToken
                )
            ));
            return;
        }

        try {
            // Empty current reset password token i.e. invalidate it
            $user->setRpToken(null);
            $user->setRpTokenCreatedAt(null);
            $user->setPasswordConfirmation(null);
            $user->save();
            $this->_getSession()->addSuccess(
                Mage::helper('Mage_User_Helper_Data')->__('Your password has been updated.')
            );
            $this->getResponse()->setRedirect(Mage::helper('Mage_Backend_Helper_Data')->getHomePageUrl());
        } catch (Exception $exception) {
            $this->_getSession()->addError($exception->getMessage());

            $this->_redirect('*/auth/resetpassword', array(
                '_nosecret' => true,
                '_query' => array(
                    'id' => $userId,
                    'token' => $passwordResetToken
                )
            ));
            return;
        }
    }

    /**
     * Check if password reset token is valid
     *
     * @param int $userId
     * @param string $resetPasswordToken
     * @throws Mage_Core_Exception
     */
    protected function _validateResetPasswordLinkToken($userId, $resetPasswordToken)
    {
        if (!is_int($userId)
            || !is_string($resetPasswordToken)
            || empty($resetPasswordToken)
            || empty($userId)
            || $userId < 0
        ) {
            throw Mage::exception(
                'Mage_Core',
                Mage::helper('Mage_User_Helper_Data')->__('Invalid password reset token.')
            );
        }

        /** @var $user Mage_User_Model_User */
        $user = Mage::getModel('Mage_User_Model_User')->load($userId);
        if (!$user->getId()) {
            throw Mage::exception(
                'Mage_Core',
                Mage::helper('Mage_User_Helper_Data')->__('Wrong account specified.')
            );
        }

        $userToken = $user->getRpToken();
        if (strcmp($userToken, $resetPasswordToken) != 0 || $user->isResetPasswordLinkTokenExpired()) {
            throw Mage::exception(
                'Mage_Core',
                Mage::helper('Mage_User_Helper_Data')->__('Your password reset link has expired.')
            );
        }
    }

    /**
     * Check if user has permissions to access this controller
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return true;
    }
}
