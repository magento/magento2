<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Controller\Adminhtml\User\Role;

use Magento\Authorization\Model\Acl\Role\Group as RoleGroup;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\State\UserLockedException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveRole extends \Magento\User\Controller\Adminhtml\User\Role
{
    /**
     * Session keys for Info form data
     */
    const ROLE_EDIT_FORM_DATA_SESSION_KEY = 'role_edit_form_data';

    /**
     * Session keys for Users form data
     */
    const IN_ROLE_USER_FORM_DATA_SESSION_KEY = 'in_role_user_form_data';

    /**
     * Session keys for original Users form data
     */
    const IN_ROLE_OLD_USER_FORM_DATA_SESSION_KEY = 'in_role_old_user_form_data';

    /**
     * Session keys for Use all resources flag form data
     */
    const RESOURCE_ALL_FORM_DATA_SESSION_KEY = 'resource_all_form_data';

    /**
     * Session keys for Resource form data
     */
    const RESOURCE_FORM_DATA_SESSION_KEY = 'resource_form_data';

    /**
     * @var \Magento\Security\Helper\SecurityCookie
     */
    protected $securityCookieHelper;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $backendAuthSession;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Authorization\Model\RoleFactory $roleFactory
     * @param \Magento\User\Model\UserFactory $userFactory
     * @param \Magento\Authorization\Model\RulesFactory $rulesFactory
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param \Magento\Security\Helper\SecurityCookie $securityCookieHelper
     * @param \Magento\Backend\Model\Auth\Session $backendAuthSession
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Authorization\Model\RoleFactory $roleFactory,
        \Magento\User\Model\UserFactory $userFactory,
        \Magento\Authorization\Model\RulesFactory $rulesFactory,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magento\Security\Helper\SecurityCookie $securityCookieHelper,
        \Magento\Backend\Model\Auth\Session $backendAuthSession
    ) {
        parent::__construct(
            $context,
            $coreRegistry,
            $roleFactory,
            $userFactory,
            $rulesFactory,
            $authSession,
            $filterManager
        );
        $this->securityCookieHelper = $securityCookieHelper;
        $this->backendAuthSession = $backendAuthSession;
    }

    /**
     * Role form submit action to save or create new role
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $rid = $this->getRequest()->getParam('role_id', false);
        $resource = $this->getRequest()->getParam('resource', false);
        $roleUsers = $this->getRequest()->getParam('in_role_user', null);
        parse_str($roleUsers, $roleUsers);
        $roleUsers = array_keys($roleUsers);



        $isAll = $this->getRequest()->getParam('all');
        if ($isAll) {
            $resource = [$this->_objectManager->get('Magento\Framework\Acl\RootResource')->getId()];
        }

        $role = $this->_initRole('role_id');
        if (!$role->getId() && $rid) {
            $this->messageManager->addError(__('This role no longer exists.'));
            return $resultRedirect->setPath('adminhtml/*/');
        }

        try {
            $this->validateUser();
            $roleName = $this->_filterManager->removeTags($this->getRequest()->getParam('rolename', false));
            $role->setName($roleName)
                ->setPid($this->getRequest()->getParam('parent_id', false))
                ->setRoleType(RoleGroup::ROLE_TYPE)
                ->setUserType(UserContextInterface::USER_TYPE_ADMIN);
            $this->_eventManager->dispatch(
                'admin_permissions_role_prepare_save',
                ['object' => $role, 'request' => $this->getRequest()]
            );
            $role->save();

            $this->_rulesFactory->create()->setRoleId($role->getId())->setResources($resource)->saveRel();

            $this->processPreviousUsers($role);

            foreach ($roleUsers as $nRuid) {
                $this->addUserToRole($nRuid, $role->getId());
            }
            $this->messageManager->addSuccess(__('You saved the role.'));
        } catch (UserLockedException $e) {
            $this->_auth->logout();
            $this->securityCookieHelper->setLogoutReasonCookie(
                \Magento\Security\Model\AdminSessionsManager::LOGOUT_REASON_USER_LOCKED
            );
            return $resultRedirect->setPath('*');
        } catch (\Magento\Framework\Exception\AuthenticationException $e) {
            $this->messageManager->addError(__('You have entered an invalid password for current user.'));
            return $this->saveDataToSessionAndRedirect($role, $this->getRequest()->getPostValue(), $resultRedirect);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('An error occurred while saving this role.'));
        }

        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Validate current user password
     *
     * @return $this
     * @throws UserLockedException
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    protected function validateUser()
    {
        $password = $this->getRequest()->getParam(
            \Magento\User\Block\Role\Tab\Info::IDENTITY_VERIFICATION_PASSWORD_FIELD
        );
        $user = $this->backendAuthSession->getUser();
        $user->performIdentityCheck($password);

        return $this;
    }

    /**
     * @param \Magento\Authorization\Model\Role $role
     * @return $this
     * @throws \Exception
     */
    protected function processPreviousUsers(\Magento\Authorization\Model\Role $role)
    {
        $oldRoleUsers = $this->getRequest()->getParam('in_role_user_old');
        parse_str($oldRoleUsers, $oldRoleUsers);
        $oldRoleUsers = array_keys($oldRoleUsers);

        foreach ($oldRoleUsers as $oUid) {
            $this->deleteUserFromRole($oUid, $role->getId());
        }

        return $this;
    }

    /**
     * Assign user to role
     *
     * @param int $userId
     * @param int $roleId
     * @return bool
     */
    protected function addUserToRole($userId, $roleId)
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
    protected function deleteUserFromRole($userId, $roleId)
    {
        try {
            $this->_userFactory->create()->setRoleId($roleId)->setUserId($userId)->deleteFromRole();
        } catch (\Exception $e) {
            throw $e;
        }
        return true;
    }

    /**
     * @param \Magento\Authorization\Model\Role $role
     * @param array $data
     * @param \Magento\Backend\Model\View\Result\Redirect $resultRedirect
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function saveDataToSessionAndRedirect($role, $data, $resultRedirect)
    {
        $this->_getSession()->setData(self::ROLE_EDIT_FORM_DATA_SESSION_KEY, ['rolename' => $data['rolename']]);
        $this->_getSession()->setData(self::IN_ROLE_USER_FORM_DATA_SESSION_KEY, $data['in_role_user']);
        $this->_getSession()->setData(self::IN_ROLE_OLD_USER_FORM_DATA_SESSION_KEY, $data['in_role_user_old']);
        if ($data['all']) {
            $this->_getSession()->setData(self::RESOURCE_ALL_FORM_DATA_SESSION_KEY, $data['all']);
        } else {
            $resource = isset($data['resource']) ? $data['resource'] : [];
            $this->_getSession()->setData(self::RESOURCE_FORM_DATA_SESSION_KEY, $resource);
        }
        $arguments = $role->getId() ? ['rid' => $role->getId()] : [];
        return $resultRedirect->setPath('*/*/editrole', $arguments);
    }
}
