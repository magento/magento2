<?php
/**
 * Controller for web API users management in Magento admin panel.
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
 */
class Mage_Webapi_Adminhtml_Webapi_UserController extends Mage_Backend_Controller_ActionAbstract
{
    /**
     * Initialize breadcrumbs.
     *
     * @return Mage_Webapi_Adminhtml_Webapi_UserController
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('Mage_Webapi::system_api_webapi_users')
            ->_addBreadcrumb(
                $this->__('Web Services'),
                $this->__('Web Services')
            )
            ->_addBreadcrumb(
                $this->__('API Users'),
                $this->__('API Users')
            );

        return $this;
    }

    /**
     * Show web API users grid.
     */
    public function indexAction()
    {
        $this->_initAction();
        $this->_title($this->__('System'))
            ->_title($this->__('Web Services'))
            ->_title($this->__('API Users'));

        $this->renderLayout();
    }

    /**
     * Create New Web API user.
     */
    public function newAction()
    {
        $this->getRequest()->setParam('user_id', null);
        $this->_forward('edit');
    }

    /**
     * Edit Web API user.
     */
    public function editAction()
    {
        $this->_initAction();
        $this->_title($this->__('System'))
            ->_title($this->__('Web Services'))
            ->_title($this->__('API Users'));

        $userId = (int)$this->getRequest()->getParam('user_id');
        $user = $this->_loadApiUser($userId);
        if (!$user) {
            return;
        }

        // Update title and breadcrumb record.
        $actionTitle = $user->getId()
            ? $this->_objectManager->get('Mage_Webapi_Helper_Data')->escapeHtml($user->getApiKey())
            : $this->__('New API User');
        $this->_title($actionTitle);
        $this->_addBreadcrumb($actionTitle, $actionTitle);

        // Restore previously entered form data from session.
        $data = $this->_getSession()->getWebapiUserData(true);
        if (!empty($data)) {
            $user->setData($data);
        }

        /** @var Mage_Webapi_Block_Adminhtml_User_Edit $editBlock */
        $editBlock = $this->getLayout()->getBlock('webapi.user.edit');
        if ($editBlock) {
            $editBlock->setApiUser($user);
        }
        /** @var Mage_Webapi_Block_Adminhtml_User_Edit_Tabs $tabsBlock */
        $tabsBlock = $this->getLayout()->getBlock('webapi.user.edit.tabs');
        if ($tabsBlock) {
            $tabsBlock->setApiUser($user);
        }

        $this->renderLayout();
    }

    /**
     * Save Web API user.
     */
    public function saveAction()
    {
        $userId = (int)$this->getRequest()->getPost('user_id');
        $data = $this->getRequest()->getPost();
        $redirectBack = false;
        if ($data) {
            $user = $this->_loadApiUser($userId);
            if (!$user) {
                return;
            }

            $user->setData($data);
            try {
                $this->_validateUserData($user);
                $user->save();
                $userId = $user->getId();

                $this->_getSession()
                    ->setWebapiUserData(null)
                    ->addSuccess($this->__('The API user has been saved.'));
                $redirectBack = $this->getRequest()->has('back');
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()
                    ->setWebapiUserData($data)
                    ->addError($e->getMessage());
                $redirectBack = true;
            } catch (Exception $e) {
                $this->_objectManager->get('Mage_Core_Model_Logger')->logException($e);
                $this->_getSession()
                    ->setWebapiUserData($data)
                    ->addError($e->getMessage());
                $redirectBack = true;
            }
        }
        if ($redirectBack) {
            $this->_redirect('*/*/edit', array('user_id' => $userId));
        } else {
            $this->_redirect('*/*/');
        }
    }

    /**
     * Delete user.
     */
    public function deleteAction()
    {
        $userId = (int)$this->getRequest()->getParam('user_id');
        if ($userId) {
            $user = $this->_loadApiUser($userId);
            if (!$user) {
                return;
            }
            try {
                $user->delete();

                $this->_getSession()->addSuccess(
                    $this->__('The API user has been deleted.')
                );
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('user_id' => $userId));
                return;
            }
        }
        $this->_getSession()->addError(
            $this->__('Unable to find a user to be deleted.')
        );
        $this->_redirect('*/*/');
    }

    /**
     * AJAX Web API users grid.
     */
    public function gridAction()
    {
        $this->loadLayout(false);
        $this->renderLayout();
    }

    /**
     * Web API user roles grid.
     */
    public function rolesgridAction()
    {
        $this->loadLayout(false);
        $this->renderLayout();
    }

    /**
     * Check ACL.
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_objectManager->get('Mage_Core_Model_Authorization')->isAllowed('Mage_Webapi::webapi_users');
    }

    /**
     * Validate Web API user data.
     *
     * @param Mage_Webapi_Model_Acl_User $user
     * @throws Magento_Validator_Exception
     */
    protected function _validateUserData($user)
    {
        $group = $user->isObjectNew() ? 'create' : 'update';
        $validator = $this->_objectManager->get('Mage_Core_Model_Validator_Factory')
            ->createValidator('api_user', $group);
        if (!$validator->isValid($user)) {
            throw new Magento_Validator_Exception($validator->getMessages());
        }
    }

    /**
     * Load Web API user.
     *
     * @param int $userId
     * @return bool|Mage_Webapi_Model_Acl_User
     */
    protected function _loadApiUser($userId)
    {
        /** @var Mage_Webapi_Model_Acl_User $user */
        $user = $this->_objectManager->create('Mage_Webapi_Model_Acl_User')->load($userId);
        if (!$user->getId() && $userId) {
            $this->_getSession()->addError(
                $this->__('This user no longer exists.')
            );
            $this->_redirect('*/*/');
            return false;
        }
        return $user;
    }
}
