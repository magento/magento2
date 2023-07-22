<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Controller\Adminhtml\User;

use Magento\Authorization\Model\Acl\Role\Group as RoleGroup;
use Magento\Authorization\Model\RoleFactory;
use Magento\Authorization\Model\RulesFactory;
use Magento\Backend\App\AbstractAction;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Registry;
use Magento\User\Model\UserFactory;

abstract class Role extends AbstractAction
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_User::acl_roles';

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * Factory for user role model
     *
     * @var RoleFactory
     */
    protected $_roleFactory;

    /**
     * User model factory
     *
     * @var UserFactory
     */
    protected $_userFactory;

    /**
     * Rules model factory
     *
     * @var RulesFactory
     */
    protected $_rulesFactory;

    /**
     * Backend auth session
     *
     * @var Session
     */
    protected $_authSession;

    /**
     * @var FilterManager
     */
    protected $_filterManager;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param RoleFactory $roleFactory
     * @param UserFactory $userFactory
     * @param RulesFactory $rulesFactory
     * @param Session $authSession
     * @param FilterManager $filterManager
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        RoleFactory $roleFactory,
        UserFactory $userFactory,
        RulesFactory $rulesFactory,
        Session $authSession,
        FilterManager $filterManager
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_roleFactory = $roleFactory;
        $this->_userFactory = $userFactory;
        $this->_rulesFactory = $rulesFactory;
        $this->_authSession = $authSession;
        $this->_filterManager = $filterManager;
    }

    /**
     * Preparing layout for output
     *
     * @return Role
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_User::system_acl_roles');
        $this->_addBreadcrumb(__('System'), __('System'));
        $this->_addBreadcrumb(__('Permissions'), __('Permissions'));
        $this->_addBreadcrumb(__('Roles'), __('Roles'));
        return $this;
    }

    /**
     * Initialize role model by passed parameter in request
     *
     * @param string $requestVariable
     * @return \Magento\Authorization\Model\Role
     */
    protected function _initRole($requestVariable = 'rid')
    {
        $role = $this->_roleFactory->create()->load($this->getRequest()->getParam($requestVariable));
        // preventing edit of relation role
        if ($role->getId() && $role->getRoleType() != RoleGroup::ROLE_TYPE) {
            $role->unsetData($role->getIdFieldName());
        }

        $this->_coreRegistry->register('current_role', $role);
        return $this->_coreRegistry->registry('current_role');
    }
}
