<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Controller\Adminhtml\User\Role;

/**
 * Class EditRole
 */
class EditRole extends \Magento\User\Controller\Adminhtml\User\Role
{
    /**
     * Edit role action
     *
     * @return void
     */
    public function execute()
    {
        $role = $this->_initRole();
        $this->_initAction();

        $this->restoreFromDataFromSession($role);

        if ($role->getId()) {
            $breadCrumb = __('Edit Role');
            $breadCrumbTitle = __('Edit Role');
        } else {
            $breadCrumb = __('Add New Role');
            $breadCrumbTitle = __('Add New Role');
        }

        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Roles'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(
            $role->getId() ? $role->getRoleName() : __('New Role')
        );

        $this->_addBreadcrumb($breadCrumb, $breadCrumbTitle);

        $this->_view->getLayout()->getBlock(
            'adminhtml.user.role.buttons'
        )->setRoleId(
            $role->getId()
        )->setRoleInfo(
            $role
        );

        $this->_view->renderLayout();
    }

    /**
     * @param \Magento\Authorization\Model\Role $role
     * @return $this
     */
    protected function restoreFromDataFromSession(\Magento\Authorization\Model\Role $role)
    {
        $data = $this->_getSession()->getData('role_edit_form_data', true);
        if (!empty($data['rolename'])) {
            $role->setRoleName($data['rolename']);
        }

        return $this;
    }
}
