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
 * @package     Mage_User
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_User_Adminhtml_UserController extends Mage_Backend_Controller_ActionAbstract
{

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('Mage_User::system_acl_users')
            ->_addBreadcrumb($this->__('System'), $this->__('System'))
            ->_addBreadcrumb($this->__('Permissions'), $this->__('Permissions'))
            ->_addBreadcrumb($this->__('Users'), $this->__('Users'))
        ;
        return $this;
    }

    public function indexAction()
    {
        $this->_title($this->__('System'))
             ->_title($this->__('Permissions'))
             ->_title($this->__('Users'));

        $this->_initAction();
        $this->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $this->_title($this->__('System'))
             ->_title($this->__('Permissions'))
             ->_title($this->__('Users'));

        $userId = $this->getRequest()->getParam('user_id');
        $model = Mage::getModel('Mage_User_Model_User');

        if ($userId) {
            $model->load($userId);
            if (! $model->getId()) {
                Mage::getSingleton('Mage_Backend_Model_Session')->addError($this->__('This user no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
        }

        $this->_title($model->getId() ? $model->getName() : $this->__('New User'));

        // Restore previously entered form data from session
        $data = Mage::getSingleton('Mage_Backend_Model_Session')->getUserData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        Mage::register('permissions_user', $model);

        if (isset($userId)) {
            $breadcrumb = $this->__('Edit User');
        } else {
            $breadcrumb = $this->__('New User');
        }
        $this->_initAction()->_addBreadcrumb($breadcrumb, $breadcrumb);
        $this->renderLayout();
    }

    public function saveAction()
    {
        $userId = $this->getRequest()->getParam('user_id');
        $data = $this->getRequest()->getPost();
        if (!$data) {
            $this->_redirect('*/*/');
            return null;
        }

        $model = $this->_prepareUserForSave($userId, $data);

        if ($model === null) {
            return;
        }

        try {
            $uRoles = $this->getRequest()->getParam('roles', array());
            if (count($uRoles)) {
                $model->setRoleId($uRoles[0]);
            }
            $model->save();
            Mage::getSingleton('Mage_Backend_Model_Session')->addSuccess($this->__('The user has been saved.'));
            Mage::getSingleton('Mage_Backend_Model_Session')->setUserData(false);
            $this->_redirect('*/*/');
            return;
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('Mage_Backend_Model_Session')->addError($e->getMessage());
            Mage::getSingleton('Mage_Backend_Model_Session')->setUserData($data);
            $this->_redirect('*/*/edit', array('user_id' => $model->getUserId()));
            return;
        }

        $this->_redirect('*/*/');
    }

    /**
     * Retrieve user save params and validate them
     *
     * @param int $userId
     * @param array $data
     * @return Mage_Core_Model_Abstract|null
     */
    protected function _prepareUserForSave($userId, array $data)
    {
        $model = Mage::getModel('Mage_User_Model_User')->load($userId);
        if (!$model->getId() && $userId) {
            Mage::getSingleton('Mage_Backend_Model_Session')->addError($this->__('This user no longer exists.'));
            $this->_redirect('*/*/');
            return null;
        }
        $model->setData($data);

        /*
         * Unsetting new password and password confirmation if they are blank
         */
        if ($model->hasNewPassword() && $model->getNewPassword() === '') {
            $model->unsNewPassword();
        }
        if ($model->hasPasswordConfirmation() && $model->getPasswordConfirmation() === '') {
            $model->unsPasswordConfirmation();
        }

        $result = $model->validate();
        if (is_array($result)) {
            Mage::getSingleton('Mage_Backend_Model_Session')->setUserData($data);
            foreach ($result as $message) {
                Mage::getSingleton('Mage_Backend_Model_Session')->addError($message);
            }
            $this->_redirect('*/*/edit', array('_current' => true));
            return null;
        }
        return $model;
    }

    public function deleteAction()
    {
        $currentUser = Mage::getSingleton('Mage_Backend_Model_Auth_Session')->getUser();

        if ($userId = $this->getRequest()->getParam('user_id')) {
            if ( $currentUser->getId() == $userId ) {
                Mage::getSingleton('Mage_Backend_Model_Session')->addError(
                    $this->__('You cannot delete your own account.')
                );
                $this->_redirect('*/*/edit', array('user_id' => $userId));
                return;
            }
            try {
                $model = Mage::getModel('Mage_User_Model_User');
                $model->setId($userId);
                $model->delete();
                Mage::getSingleton('Mage_Backend_Model_Session')->addSuccess($this->__('The user has been deleted.'));
                $this->_redirect('*/*/');
                return;
            }
            catch (Exception $e) {
                Mage::getSingleton('Mage_Backend_Model_Session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('user_id' => $this->getRequest()->getParam('user_id')));
                return;
            }
        }
        Mage::getSingleton('Mage_Backend_Model_Session')->addError($this->__('Unable to find a user to delete.'));
        $this->_redirect('*/*/');
    }

    public function rolesGridAction()
    {
        $userId = $this->getRequest()->getParam('user_id');
        $model = Mage::getModel('Mage_User_Model_User');

        if ($userId) {
            $model->load($userId);
        }
        Mage::register('permissions_user', $model);
        $this->loadLayout();
        $this->renderLayout();
    }

    public function roleGridAction()
    {
        $this->loadLayout(false);
        $this->renderLayout();
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('Mage_Core_Model_Authorization')->isAllowed('Mage_User::acl_users');
    }

}
