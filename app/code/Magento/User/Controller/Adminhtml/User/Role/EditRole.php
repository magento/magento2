<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Controller\Adminhtml\User\Role;

/**
 * Class EditRole
 * @since 2.0.0
 */
class EditRole extends \Magento\User\Controller\Adminhtml\User\Role
{
    /**
     * Edit role action
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $this->restoreResourcesDataFromSession();
        $this->restoreUsersDataFromSession();

        $role = $this->_initRole();
        $this->restoreFormDataFromSession($role);

        $this->_initAction();

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
     * Restore Users Form Data from Session and save one in Registry
     *
     * @return void
     * @since 2.1.0
     */
    protected function restoreUsersDataFromSession()
    {
        $this->_coreRegistry->register(
            SaveRole::IN_ROLE_USER_FORM_DATA_SESSION_KEY,
            $this->_session->getData(SaveRole::IN_ROLE_USER_FORM_DATA_SESSION_KEY, true)
        );
        $this->_coreRegistry->register(
            SaveRole::IN_ROLE_OLD_USER_FORM_DATA_SESSION_KEY,
            $this->_session->getData(SaveRole::IN_ROLE_OLD_USER_FORM_DATA_SESSION_KEY, true)
        );
    }

    /**
     * Restore Resources Form Data from Session and save one in Registry
     *
     * @return void
     * @since 2.1.0
     */
    protected function restoreResourcesDataFromSession()
    {
        $this->_coreRegistry->register(
            SaveRole::RESOURCE_ALL_FORM_DATA_SESSION_KEY,
            $this->_session->getData(SaveRole::RESOURCE_ALL_FORM_DATA_SESSION_KEY, true)
        );
        $this->_coreRegistry->register(
            SaveRole::RESOURCE_FORM_DATA_SESSION_KEY,
            $this->_session->getData(SaveRole::RESOURCE_FORM_DATA_SESSION_KEY, true)
        );
    }

    /**
     * Restore general information Form Data from Session and save one in Registry
     *
     * @param \Magento\Authorization\Model\Role $role
     * @return $this
     * @since 2.1.0
     */
    protected function restoreFormDataFromSession(\Magento\Authorization\Model\Role $role)
    {
        $data = $this->_getSession()->getData(SaveRole::ROLE_EDIT_FORM_DATA_SESSION_KEY, true);
        if (!empty($data['rolename'])) {
            $role->setRoleName($data['rolename']);
        }

        return $this;
    }
}
