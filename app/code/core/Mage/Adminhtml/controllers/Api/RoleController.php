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
 * Adminhtml roles controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Api_RoleController extends Mage_Adminhtml_Controller_Action
{

    protected function _initAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('Mage_Api::system_legacy_api_roles');
        $this->_addBreadcrumb($this->__('Web services'), $this->__('Web services'));
        $this->_addBreadcrumb($this->__('Permissions'), $this->__('Permissions'));
        $this->_addBreadcrumb($this->__('Roles'), $this->__('Roles'));
        return $this;
    }

    public function indexAction()
    {
        $this->_title($this->__('System'))
             ->_title($this->__('Web Services'))
             ->_title($this->__('Roles'));

        $this->_initAction();

        $this->_addContent($this->getLayout()->createBlock('Mage_Adminhtml_Block_Api_Roles'));

        $this->renderLayout();
    }

    public function roleGridAction()
    {
        $this->getResponse()
            ->setBody($this->getLayout()
            ->createBlock('Mage_Adminhtml_Block_Api_Grid_Role')
            ->toHtml()
        );
    }

    public function editRoleAction()
    {
        $this->_title($this->__('System'))
             ->_title($this->__('Web Services'))
             ->_title($this->__('Roles'));

        $this->_initAction();

        $roleId = $this->getRequest()->getParam('rid');
        if( intval($roleId) > 0 ) {
            $breadCrumb = $this->__('Edit Role');
            $breadCrumbTitle = $this->__('Edit Role');
            $this->_title($this->__('Edit Role'));
        } else {
            $breadCrumb = $this->__('Add New Role');
            $breadCrumbTitle = $this->__('Add New Role');
            $this->_title($this->__('New Role'));
        }
        $this->_addBreadcrumb($breadCrumb, $breadCrumbTitle);

        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

        $this->_addLeft(
            $this->getLayout()->createBlock('Mage_Adminhtml_Block_Api_Editroles')
        );
        $resources = Mage::getModel('Mage_Api_Model_Roles')->getResourcesList();
        $this->_addContent(
            $this->getLayout()->createBlock('Mage_Adminhtml_Block_Api_Buttons')
                ->setRoleId($roleId)
                ->setRoleInfo(Mage::getModel('Mage_Api_Model_Roles')->load($roleId))
                ->setTemplate('api/roleinfo.phtml')
        );
        $this->_addJs(
            $this->getLayout()
                ->createBlock('Mage_Adminhtml_Block_Template')
                ->setTemplate('api/role_users_grid_js.phtml')
        );
        $this->renderLayout();
    }

    public function deleteAction()
    {
        $rid = $this->getRequest()->getParam('rid', false);

        try {
            Mage::getModel('Mage_Api_Model_Roles')->load($rid)->delete();
            Mage::getSingleton('Mage_Adminhtml_Model_Session')->addSuccess($this->__('The role has been deleted.'));
        } catch (Exception $e) {
            Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError($this->__('An error occurred while deleting this role.'));
        }

        $this->_redirect("*/*/");
    }

    public function saveRoleAction()
    {

        $rid        = $this->getRequest()->getParam('role_id', false);
        $role = Mage::getModel('Mage_Api_Model_Roles')->load($rid);
        if (!$role->getId() && $rid) {
            Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError($this->__('This Role no longer exists'));
            $this->_redirect('*/*/');
            return;
        }

        $resource   = explode(',', $this->getRequest()->getParam('resource', false));
        $roleUsers  = $this->getRequest()->getParam('in_role_user', null);
        parse_str($roleUsers, $roleUsers);
        $roleUsers = array_keys($roleUsers);

        $oldRoleUsers = $this->getRequest()->getParam('in_role_user_old');
        parse_str($oldRoleUsers, $oldRoleUsers);
        $oldRoleUsers = array_keys($oldRoleUsers);

        $isAll = $this->getRequest()->getParam('all');
        if ($isAll) {
            $resource = array("all");
        }

        try {
            $role = $role
                    ->setName($this->getRequest()->getParam('rolename', false))
                    ->setPid($this->getRequest()->getParam('parent_id', false))
                    ->setRoleType('G')
                    ->save();

            Mage::getModel('Mage_Api_Model_Rules')
                ->setRoleId($role->getId())
                ->setResources($resource)
                ->saveRel();

            foreach($oldRoleUsers as $oUid) {
                $this->_deleteUserFromRole($oUid, $role->getId());
            }

            foreach ($roleUsers as $nRuid) {
                $this->_addUserToRole($nRuid, $role->getId());
            }

            $rid = $role->getId();
            Mage::getSingleton('Mage_Adminhtml_Model_Session')->addSuccess($this->__('The role has been saved.'));
        } catch (Exception $e) {
            Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError($this->__('An error occurred while saving this role.'));
        }

        //$this->getResponse()->setRedirect($this->getUrl("*/*/editrole/rid/$rid"));
        $this->_redirect('*/*/editrole', array('rid' => $rid));
        return;
    }

    public function editrolegridAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('Mage_Adminhtml_Block_Api_Role_Grid_User')->toHtml()
        );
    }

    protected function _deleteUserFromRole($userId, $roleId)
    {
        try {
            Mage::getModel('Mage_Api_Model_User')
                ->setRoleId($roleId)
                ->setUserId($userId)
                ->deleteFromRole();
        } catch (Exception $e) {
            throw $e;
            return false;
        }
        return true;
    }

    protected function _addUserToRole($userId, $roleId)
    {
        $user = Mage::getModel('Mage_Api_Model_User')->load($userId);
        $user->setRoleId($roleId)->setUserId($userId);

        if( $user->roleUserExists() === true ) {
            return false;
        } else {
            $user->add();
            return true;
        }
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('Mage_Core_Model_Authorization')->isAllowed('Mage_Api::roles');
    }
}
