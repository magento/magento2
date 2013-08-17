<?php
/**
 * Controller for web API roles management in Magento admin panel.
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
class Mage_Webapi_Adminhtml_Webapi_RoleController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Init.
     *
     * @return Mage_Webapi_Adminhtml_Webapi_RoleController
     */
    protected function _initAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('Mage_Webapi::system_api_webapi_roles');
        $this->_addBreadcrumb(
            $this->__('Web Api'),
            $this->__('Web Api')
        );
        $this->_addBreadcrumb(
            $this->__('API Roles'),
            $this->__('API Roles')
        );
        return $this;
    }

    /**
     * Web API roles grid.
     */
    public function indexAction()
    {
        $this->_title($this->__('API Roles'));
        $this->_initAction();
        $this->renderLayout();
    }

    /**
     * AJAX Web API roles grid.
     */
    public function rolegridAction()
    {
        $this->loadLayout(false);
        $this->renderLayout();
    }

    /**
     * Grid in edit role form.
     */
    public function usersgridAction()
    {
        $this->loadLayout(false);
        $this->renderLayout();
    }

    /**
     * Edit Web API role.
     */
    public function editAction()
    {
        $this->_initAction();
        $this->_title($this->__('API Roles'));

        $roleId = $this->getRequest()->getParam('role_id');

        /** @var Mage_Webapi_Model_Acl_Role $role */
        $role = $this->_objectManager->create('Mage_Webapi_Model_Acl_Role');
        if ($roleId) {
            $role->load($roleId);
            if (!$role->getId()) {
                $this->_getSession()->addError(
                    $this->__('This API role no longer exists.')
                );
                $this->_redirect('*/*/');
                return;
            }
            $this->_addBreadcrumb(
                $this->__('Edit API Role'),
                $this->__('Edit API Role')
            );
            $this->_title($this->__('Edit API Role'));
        } else {
            $this->_addBreadcrumb(
                $this->__('Add New API Role'),
                $this->__('Add New API Role')
            );
            $this->_title($this->__('New API Role'));
        }

        // Restore previously entered form data from session
        $data = $this->_getSession()->getWebapiUserData(true);
        if (!empty($data)) {
            $role->setData($data);
        }

        /** @var Mage_Webapi_Block_Adminhtml_Role_Edit $editBlock */
        $editBlock = $this->getLayout()->getBlock('webapi.role.edit');
        if ($editBlock) {
            $editBlock->setApiRole($role);
        }

        /** @var Mage_Webapi_Block_Adminhtml_Role_Edit_Tabs $tabsBlock */
        $tabsBlock = $this->getLayout()->getBlock('webapi.role.edit.tabs');
        if ($tabsBlock) {
            $tabsBlock->setApiRole($role);
        }

        $this->renderLayout();
    }

    /**
     * Remove role.
     */
    public function deleteAction()
    {
        $roleId = $this->getRequest()->getParam('role_id', false);

        try {
            $this->_objectManager->create('Mage_Webapi_Model_Acl_Role')->load($roleId)->delete();
            $this->_getSession()->addSuccess(
                $this->__('The API role has been deleted.')
            );
        } catch (Exception $e) {
            $this->_getSession()->addError(
                $this->__('An error occurred while deleting this role.')
            );
        }

        $this->_redirect("*/*/");
    }

    /**
     * Save role.
     */
    public function saveAction()
    {
        $data = $this->getRequest()->getPost();
        if ($data) {
            $roleId = $this->getRequest()->getPost('role_id', false);
            /** @var Mage_Webapi_Model_Acl_Role $role */
            $role = $this->_objectManager->create('Mage_Webapi_Model_Acl_Role')->load($roleId);
            if (!$role->getId() && $roleId) {
                $this->_getSession()->addError(
                    $this->__('This role no longer exists.')
                );
                $this->_redirect('*/*/');
                return;
            }
            $role->setData($data);

            try {
                $this->_validateRole($role);
                $role->save();

                $isNewRole = empty($roleId);
                $this->_saveResources($role->getId(), $isNewRole);
                $this->_saveUsers($role->getId());

                $this->_getSession()->addSuccess(
                    $this->__('The API role has been saved.')
                );
                $this->_getSession()->setWebapiRoleData(false);

                if ($roleId && !$this->getRequest()->has('continue')) {
                    $this->_redirect('*/*/');
                } else {
                    $this->_redirect('*/*/edit', array('role_id' => $role->getId()));
                }
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $this->_getSession()->setWebapiRoleData($data);
                $this->_redirect('*/*/edit', array('role_id' => $role->getId()));
            }
        }
    }

    /**
     * Validate Web API role data.
     *
     * @param Mage_Webapi_Model_Acl_Role $role
     * @throws Magento_Validator_Exception
     */
    protected function _validateRole($role)
    {
        $group = $role->isObjectNew() ? 'create' : 'update';
        $validator = $this->_objectManager->get('Mage_Core_Model_Validator_Factory')
            ->createValidator('api_role', $group);
        if (!$validator->isValid($role)) {
            throw new Magento_Validator_Exception($validator->getMessages());
        }
    }

    /**
     * Save role resources.
     *
     * @param integer $roleId
     * @param boolean $isNewRole
     */
    protected function _saveResources($roleId, $isNewRole)
    {
        // parse resource list
        $resources = explode(',', $this->getRequest()->getParam('resource', false));
        $isAll = $this->getRequest()->getParam('all');
        $rootResource = $this->_objectManager->get('Mage_Core_Model_Acl_RootResource');
        if ($isAll) {
            $resources = array($rootResource->getId());
        } elseif (in_array($rootResource->getId(), $resources)) {
            unset($resources[array_search(
                $rootResource->getId(),
                $resources
            )]);
        }

        $saveResourcesFlag = true;
        if (!$isNewRole) {
            // Check changes
            /** @var Mage_Webapi_Model_Resource_Acl_Rule $ruleResource */
            $ruleResource = $this->_objectManager->get('Mage_Webapi_Model_Resource_Acl_Rule');
            $oldResources = $ruleResource->getResourceIdsByRole($roleId);
            if (count($oldResources) == count($resources) && !array_diff($oldResources, $resources)) {
                $saveResourcesFlag = false;
            }
        }

        if ($saveResourcesFlag) {
            $this->_objectManager->create('Mage_Webapi_Model_Acl_Rule')
                ->setRoleId($roleId)
                ->setResources($resources)
                ->saveResources();
        }
    }

    /**
     * Save linked users.
     *
     * @param integer $roleId
     */
    protected function _saveUsers($roleId)
    {
        // parse users list
        $roleUsers = $this->_parseRoleUsers($this->getRequest()->getParam('in_role_user'));
        $oldRoleUsers = $this->_parseRoleUsers($this->getRequest()->getParam('in_role_user_old'));

        if ($roleUsers != $oldRoleUsers) {
            foreach ($oldRoleUsers as $userId) {
                $user = $this->_objectManager->create('Mage_Webapi_Model_Acl_User')->load($userId);
                $user->setRoleId(null)->save();
            }

            foreach ($roleUsers as $userId) {
                $user = $this->_objectManager->create('Mage_Webapi_Model_Acl_User')->load($userId);
                $user->setRoleId($roleId)->save();
            }
        }
    }

    /**
     * Parse request string with users.
     *
     * @param string $roleUsers
     * @return array
     */
    protected function _parseRoleUsers($roleUsers)
    {
        parse_str($roleUsers, $roleUsers);
        if ($roleUsers && count($roleUsers)) {
            return array_keys($roleUsers);
        }

        return array();
    }

    /**
     * Check access rights.
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Mage_Webapi::webapi_roles');
    }

}
