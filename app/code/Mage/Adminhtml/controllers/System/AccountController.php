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
 * Adminhtml account controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Adminhtml_System_AccountController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_title($this->__('My Account'));

        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Saving edited user information
     */
    public function saveAction()
    {
        $userId = $this->_objectManager->get('Mage_Backend_Model_Auth_Session')->getUser()->getId();
        $password = (string)$this->getRequest()->getParam('password');
        $passwordConfirmation = (string)$this->getRequest()->getParam('password_confirmation');
        $interfaceLocale = (string)$this->getRequest()->getParam('interface_locale', false);

        /** @var $user Mage_User_Model_User */
        $user = $this->_objectManager->create('Mage_User_Model_User')->load($userId);

        $user->setId($userId)
            ->setUsername($this->getRequest()->getParam('username', false))
            ->setFirstname($this->getRequest()->getParam('firstname', false))
            ->setLastname($this->getRequest()->getParam('lastname', false))
            ->setEmail(strtolower($this->getRequest()->getParam('email', false)));

        if ($password !== '') {
            $user->setPassword($password);
        }
        if ($passwordConfirmation !== '') {
            $user->setPasswordConfirmation($passwordConfirmation);
        }

        if ($this->_objectManager->get('Mage_Core_Model_Locale_Validator')->isValid($interfaceLocale)) {

            $user->setInterfaceLocale($interfaceLocale);
            $this->_objectManager->get('Mage_Backend_Model_Locale_Manager')
                ->switchBackendInterfaceLocale($interfaceLocale);
        }

        try {
            $user->save();
            $user->sendPasswordResetNotificationEmail();
            $this->_getSession()->addSuccess(
                $this->__('The account has been saved.')
            );
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addMessages($e->getMessages());
        } catch (Exception $e) {
            $this->_getSession()->addError(
                $this->__('An error occurred while saving account.')
            );
        }
        $this->getResponse()->setRedirect($this->getUrl("*/*/"));
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Mage_Adminhtml::myaccount');
    }
}
