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
namespace Magento\User\Controller\Adminhtml\User\Role;

use Magento\Authorization\Model\Acl\Role\Group as RoleGroup;
use Magento\Authorization\Model\UserContextInterface;

class SaveRole extends \Magento\User\Controller\Adminhtml\User\Role
{
    /**
     * Assign user to role
     *
     * @param int $userId
     * @param int $roleId
     * @return bool
     */
    protected function _addUserToRole($userId, $roleId)
    {
        $user = $this->_userFactory->create()->load($userId);
        $user->setRoleId($roleId);

        if ($user->roleUserExists() === true) {
            return false;
        } else {
            $user->save();
            return true;
        }
    }

    /**
     * Remove user from role
     *
     * @param int $userId
     * @param int $roleId
     * @return bool
     * @throws \Exception
     */
    protected function _deleteUserFromRole($userId, $roleId)
    {
        try {
            $this->_userFactory->create()->setRoleId($roleId)->setUserId($userId)->deleteFromRole();
        } catch (\Exception $e) {
            throw $e;
        }
        return true;
    }

    /**
     * Role form submit action to save or create new role
     *
     * @return void
     */
    public function execute()
    {
        $rid = $this->getRequest()->getParam('role_id', false);
        $resource = $this->getRequest()->getParam('resource', false);
        $roleUsers = $this->getRequest()->getParam('in_role_user', null);
        parse_str($roleUsers, $roleUsers);
        $roleUsers = array_keys($roleUsers);

        $oldRoleUsers = $this->getRequest()->getParam('in_role_user_old');
        parse_str($oldRoleUsers, $oldRoleUsers);
        $oldRoleUsers = array_keys($oldRoleUsers);

        $isAll = $this->getRequest()->getParam('all');
        if ($isAll) {
            $resource = array($this->_objectManager->get('Magento\Framework\Acl\RootResource')->getId());
        }

        $role = $this->_initRole('role_id');
        if (!$role->getId() && $rid) {
            $this->messageManager->addError(__('This role no longer exists.'));
            $this->_redirect('adminhtml/*/');
            return;
        }

        try {
            $roleName = $this->getRequest()->getParam('rolename', false);

            $role->setName($roleName)
                ->setPid($this->getRequest()->getParam('parent_id', false))
                ->setRoleType(RoleGroup::ROLE_TYPE)
                ->setUserType(UserContextInterface::USER_TYPE_ADMIN);
            $this->_eventManager->dispatch(
                'admin_permissions_role_prepare_save',
                array('object' => $role, 'request' => $this->getRequest())
            );
            $role->save();

            $this->_rulesFactory->create()->setRoleId($role->getId())->setResources($resource)->saveRel();

            foreach ($oldRoleUsers as $oUid) {
                $this->_deleteUserFromRole($oUid, $role->getId());
            }

            foreach ($roleUsers as $nRuid) {
                $this->_addUserToRole($nRuid, $role->getId());
            }
            $this->messageManager->addSuccess(__('You saved the role.'));
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('An error occurred while saving this role.'));
        }
        $this->_redirect('adminhtml/*/');
        return;
    }
}
