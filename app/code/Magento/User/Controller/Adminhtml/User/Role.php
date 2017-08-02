<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Controller\Adminhtml\User;

use Magento\Authorization\Model\Acl\Role\Group as RoleGroup;

/**
 * Class \Magento\User\Controller\Adminhtml\User\Role
 *
 * @since 2.0.0
 */
abstract class Role extends \Magento\Backend\App\AbstractAction
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
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $_coreRegistry = null;

    /**
     * Factory for user role model
     *
     * @var \Magento\Authorization\Model\RoleFactory
     * @since 2.0.0
     */
    protected $_roleFactory;

    /**
     * User model factory
     *
     * @var \Magento\User\Model\UserFactory
     * @since 2.0.0
     */
    protected $_userFactory;

    /**
     * Rules model factory
     *
     * @var \Magento\Authorization\Model\RulesFactory
     * @since 2.0.0
     */
    protected $_rulesFactory;

    /**
     * Backend auth session
     *
     * @var \Magento\Backend\Model\Auth\Session
     * @since 2.0.0
     */
    protected $_authSession;

    /**
     * @var \Magento\Framework\Filter\FilterManager
     * @since 2.0.0
     */
    protected $_filterManager;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Authorization\Model\RoleFactory $roleFactory
     * @param \Magento\User\Model\UserFactory $userFactory
     * @param \Magento\Authorization\Model\RulesFactory $rulesFactory
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Authorization\Model\RoleFactory $roleFactory,
        \Magento\User\Model\UserFactory $userFactory,
        \Magento\Authorization\Model\RulesFactory $rulesFactory,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Filter\FilterManager $filterManager
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
     * @since 2.0.0
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
     * @since 2.0.0
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
