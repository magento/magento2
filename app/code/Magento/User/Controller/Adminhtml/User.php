<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Controller\Adminhtml;

use Magento\Backend\App\AbstractAction;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\User\Model\UserFactory;

abstract class User extends AbstractAction
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_User::acl_users';

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * User model factory
     *
     * @var UserFactory
     */
    protected $_userFactory;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param UserFactory $userFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        UserFactory $userFactory
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_userFactory = $userFactory;
    }

    /**
     * @return $this
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu(
            'Magento_User::system_acl_users'
        )->_addBreadcrumb(
            __('System'),
            __('System')
        )->_addBreadcrumb(
            __('Permissions'),
            __('Permissions')
        )->_addBreadcrumb(
            __('Users'),
            __('Users')
        );
        return $this;
    }

    /**
     * Retrieve well-formed admin user data from the form input
     *
     * @param array $data
     * @return array
     */
    protected function _getAdminUserData(array $data)
    {
        if (isset($data['password']) && $data['password'] === '') {
            unset($data['password']);
        }
        if (!isset($data['password'])
            && isset($data['password_confirmation'])
            && $data['password_confirmation'] === ''
        ) {
            unset($data['password_confirmation']);
        }
        return $data;
    }
}
