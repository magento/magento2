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
 * @package     Mage_Api2
 * @copyright  Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * API2 roles controller
 *
 * @category   Mage
 * @package    Mage_Api2
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Api2_Adminhtml_Api2_RoleController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Show grid
     */
    public function indexAction()
    {
        $this->_title($this->__('System'))
             ->_title($this->__('Web Services'))
             ->_title($this->__('REST Roles'));

        $this->loadLayout()->_setActiveMenu('Mage_Api2::system_api_rest_roles');
        $this->_addBreadcrumb($this->__('Web services'), $this->__('Web services'));
        $this->_addBreadcrumb($this->__('REST Roles'), $this->__('REST Roles'));
        $this->_addBreadcrumb($this->__('Roles'), $this->__('Roles'));

        $this->renderLayout();
    }

    /**
     * Updating grid by ajax
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Updating users grid by ajax
     */
    public function usersGridAction()
    {
        $id = $this->getRequest()->getParam('id', false);

        $this->loadLayout();
        /** @var $grid Mage_Api2_Block_Adminhtml_Roles_Tab_Users */
        $grid = $this->getLayout()->getBlock('adminhtml.role.edit.tab.users');
        $grid->setUsers($this->_getUsers($id));

        $this->renderLayout();
    }

    /**
     * Create new role
     */
    public function newAction()
    {
        $this->_title($this->__('System'))
             ->_title($this->__('Web Services'))
             ->_title($this->__('Rest Roles'));

        $this->loadLayout()->_setActiveMenu('Mage_Api2::system_api_rest_roles');
        $this->_addBreadcrumb($this->__('Web services'), $this->__('Web services'));
        $this->_addBreadcrumb($this->__('REST Roles'), $this->__('REST Roles'));
        $this->_addBreadcrumb($this->__('Roles'), $this->__('Roles'));

        $breadCrumb = $this->__('Add New Role');
        $breadCrumbTitle = $this->__('Add New Role');
        $this->_title($this->__('New Role'));

        $this->_addBreadcrumb($breadCrumb, $breadCrumbTitle);

        $this->renderLayout();
    }

    /**
     * Edit role
     */
    public function editAction()
    {
        $id = (int) $this->getRequest()->getParam('id');
        /** @var $role Mage_Api2_Model_Acl_Global_Role */
        $role = Mage::getModel('Mage_Api2_Model_Acl_Global_Role')->load($id);

        if (!$role->getId()) {
            $this->_getSession()->addError($this->__('Role "%s" not found.', $id));
            $this->_redirect('*/*/');
            return;
        }

        $this->loadLayout()->_setActiveMenu('Mage_Api2::system_api_rest_roles');

        $this->_title($this->__('System'))
             ->_title($this->__('Web Services'))
             ->_title($this->__('Rest Roles'));

        $breadCrumb = $this->__('Edit Role');
        $breadCrumbTitle = $this->__('Edit Role');
        $this->_title($this->__('Edit Role'));
        $this->_addBreadcrumb($breadCrumb, $breadCrumbTitle);

        /** @var $tabs Mage_Api2_Block_Adminhtml_Roles_Tabs */
        $tabs = $this->getLayout()->getBlock('adminhtml.role.edit.tabs');
        $tabs->setRole($role);
        /** @var $child Mage_Adminhtml_Block_Template */
        foreach ($tabs->getChildNames() as $childName) {
            $child = $this->getLayout()->getBlock($childName);
            if ($child) {
                $child->setData('role', $role);
            }
        }

        /** @var $buttons Mage_Api2_Block_Adminhtml_Roles_Buttons */
        $buttons = $this->getLayout()->getBlock('adminhtml.roles.buttons');
        $buttons->setRole($role);

        /** @var $users Mage_Api2_Block_Adminhtml_Roles_Tab_Users */
        $users = $this->getLayout()->getBlock('adminhtml.role.edit.tab.users');
        $users->setUsers($this->_getUsers($id));

        //$this->getLayout()->getBlock('adminhtml.role.edit.tab.resources')->getResTreeJson();
        //exit;

        $this->renderLayout();
    }

    /**
     * Save role
     */
    public function saveAction()
    {
        $request = $this->getRequest();

        $id = $request->getParam('id', false);
        /** @var $role Mage_Api2_Model_Acl_Global_Role */
        $role = Mage::getModel('Mage_Api2_Model_Acl_Global_Role')->load($id);

        if (!$role->getId() && $id) {
            $this->_getSession()->addError(
                $this->__('Role "%s" no longer exists', $role->getData('role_name')));
            $this->_redirect('*/*/');
            return;
        }

        $roleUsers  = $request->getParam('in_role_users', null);
        parse_str($roleUsers, $roleUsers);
        $roleUsers = array_keys($roleUsers);

        $oldRoleUsers = $this->getRequest()->getParam('in_role_users_old');
        parse_str($oldRoleUsers, $oldRoleUsers);
        $oldRoleUsers = array_keys($oldRoleUsers);

        /** @var $session Mage_Adminhtml_Model_Session */
        $session = $this->_getSession();

        try {
            $role->setRoleName($this->getRequest()->getParam('role_name', false))
                    ->save();

            foreach($oldRoleUsers as $oUid) {
                $this->_deleteUserFromRole($oUid, $role->getId());
            }

            foreach ($roleUsers as $nRuid) {
                $this->_addUserToRole($nRuid, $role->getId());
            }

            /**
             * Save rules with resources
             */
            /** @var $rule Mage_Api2_Model_Acl_Global_Rule */
            $rule = Mage::getModel('Mage_Api2_Model_Acl_Global_Rule');
            if ($id) {
                $collection = $rule->getCollection();
                $collection->addFilterByRoleId($role->getId());

                /** @var $model Mage_Api2_Model_Acl_Global_Rule */
                foreach ($collection as $model) {
                    $model->delete();
                }
            }

            /** @var $ruleTree Mage_Api2_Model_Acl_Global_Rule_Tree */
            $ruleTree = Mage::getSingleton(
                'Mage_Api2_Model_Acl_Global_Rule_Tree',
                array('type' => Mage_Api2_Model_Acl_Global_Rule_Tree::TYPE_PRIVILEGE)
            );
            $resources = $ruleTree->getPostResources();
            $id = $role->getId();
            foreach ($resources as $resourceId => $privileges) {
                foreach ($privileges as $privilege => $allow) {
                    if (!$allow) {
                        continue;
                    }

                    $rule->setId(null)
                            ->isObjectNew(true);

                    $rule->setRoleId($id)
                            ->setResourceId($resourceId)
                            ->setPrivilege($privilege)
                            ->save();
                }
            }

            $session->addSuccess($this->__('The role has been saved.'));
        } catch (Mage_Core_Exception $e) {
            $session->addError($e->getMessage());
        } catch (Exception $e) {
            $session->addException($e, $this->__('An error occurred while saving role.'));
        }

        $this->_redirect('*/*/edit', array('id'=>$id));
    }

    /**
     * Delete role
     */
    public function deleteAction()
    {
        $id = $this->getRequest()->getParam('id', false);

        try {
            /** @var $model Mage_Api2_Model_Acl_Global_Role */
            $model = Mage::getModel('Mage_Api2_Model_Acl_Global_Role');
            $model->load($id)->delete();
            $this->_getSession()->addSuccess($this->__('Role has been deleted.'));
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('An error occurred while deleting the role.'));
        }

        $this->_redirect("*/*/");
    }

    /**
     * Check against ACL
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        /** @var $session Mage_Backend_Model_Auth_Session */
        $session = Mage::getSingleton('Mage_Backend_Model_Auth_Session');
        return $session->isAllowed('system/api/roles_rest');
    }

    /**
     * Get API2 roles ajax grid action
     */
    public function rolesGridAction()
    {
        /** @var $model Mage_User_Model_User */
        $model = Mage::getModel('Mage_User_Model_User');
        $model->load($this->getRequest()->getParam('user_id'));

        Mage::register('permissions_user', $model);
        $this->getResponse()
            ->setBody($this->getLayout()->createBlock('Mage_Api2_Block_Adminhtml_Permissions_User_Edit_Tab_Roles')->toHtml());
    }

    /**
     * Get users possessing the role
     *
     * @param int $id
     * @return array|mixed
     */
    protected function _getUsers($id)
    {
        if ( $this->getRequest()->getParam('in_role_users') != "" ) {
            return $this->getRequest()->getParam('in_role_users');
        }

        /** @var $role Mage_Api2_Model_Acl_Global_Role */
        $role = Mage::getModel('Mage_Api2_Model_Acl_Global_Role');
        $role->setId($id);

        /** @var $resource Mage_Api2_Model_Resource_Acl_Global_Role  */
        $resource = $role->getResource();
        $users = $resource->getRoleUsers($role);

        if (sizeof($users) == 0) {
            $users = array();
        }

        return $users;
    }

    /**
     * Take away user role
     *
     * @param int $adminId
     * @param int $roleId
     * @return Mage_Api2_Adminhtml_Api2_RoleController
     */
    protected function _deleteUserFromRole($adminId, $roleId)
    {
        /** @var $resourceModel Mage_Api2_Model_Resource_Acl_Global_Role */
        $resourceModel = Mage::getResourceModel('Mage_Api2_Model_Resource_Acl_Global_Role');
        $resourceModel->deleteAdminToRoleRelation($adminId, $roleId);
        return $this;
    }

    /**
     * Give user a role
     *
     * @param int $adminId
     * @param int $roleId
     * @return Mage_Api2_Adminhtml_Api2_RoleController
     */
    protected function _addUserToRole($adminId, $roleId)
    {
        /** @var $resourceModel Mage_Api2_Model_Resource_Acl_Global_Role */
        $resourceModel = Mage::getResourceModel('Mage_Api2_Model_Resource_Acl_Global_Role');
        $resourceModel->saveAdminToRoleRelation($adminId, $roleId);
        return $this;
    }
}
