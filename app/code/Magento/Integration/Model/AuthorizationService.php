<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Model;

use Magento\Authorization\Model\ResourceModel\Role\CollectionFactory as RoleCollectionFactory;
use Magento\Authorization\Model\ResourceModel\Rules\CollectionFactory as RulesCollectionFactory;
use Magento\Authorization\Model\Role;
use Magento\Authorization\Model\RoleFactory;
use Magento\Authorization\Model\RulesFactory;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Acl;
use Magento\Framework\Acl\Builder as AclBuilder;
use Magento\Framework\Acl\RootResource as RootAclResource;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface as Logger;

/**
 * Service for integration permissions management.
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class AuthorizationService implements \Magento\Integration\Api\AuthorizationServiceInterface
{
    /**
     * @var AclBuilder
     * @since 2.0.0
     */
    protected $_aclBuilder;

    /**
     * @var RoleFactory
     * @since 2.0.0
     */
    protected $_roleFactory;

    /**
     * @var RoleCollectionFactory
     * @since 2.0.0
     */
    protected $_roleCollectionFactory;

    /**
     * @var RulesFactory
     * @since 2.0.0
     */
    protected $_rulesFactory;

    /**
     * @var RulesCollectionFactory
     * @since 2.0.0
     */
    protected $_rulesCollectionFactory;

    /**
     * @var Logger
     * @since 2.0.0
     */
    protected $_logger;

    /**
     * @var RootAclResource
     * @since 2.0.0
     */
    protected $_rootAclResource;

    /**
     * Initialize dependencies.
     *
     * @param AclBuilder $aclBuilder
     * @param RoleFactory $roleFactory
     * @param RoleCollectionFactory $roleCollectionFactory
     * @param RulesFactory $rulesFactory
     * @param RulesCollectionFactory $rulesCollectionFactory
     * @param Logger $logger
     * @param RootAclResource $rootAclResource
     * @since 2.0.0
     */
    public function __construct(
        AclBuilder $aclBuilder,
        RoleFactory $roleFactory,
        RoleCollectionFactory $roleCollectionFactory,
        RulesFactory $rulesFactory,
        RulesCollectionFactory $rulesCollectionFactory,
        Logger $logger,
        RootAclResource $rootAclResource
    ) {
        $this->_aclBuilder = $aclBuilder;
        $this->_roleFactory = $roleFactory;
        $this->_rulesFactory = $rulesFactory;
        $this->_rulesCollectionFactory = $rulesCollectionFactory;
        $this->_roleCollectionFactory = $roleCollectionFactory;
        $this->_logger = $logger;
        $this->_rootAclResource = $rootAclResource;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function grantPermissions($integrationId, $resources)
    {
        try {
            $role = $this->_getUserRole($integrationId);
            if (!$role) {
                $role = $this->_createRole($integrationId);
            }
            $this->_associateResourcesWithRole($role, $resources);
        } catch (\Exception $e) {
            $this->_logger->critical($e);
            throw new LocalizedException(
                __('Sorry, something went wrong granting permissions. You can find out more in the exceptions log.')
            );
        }
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function grantAllPermissions($integrationId)
    {
        $this->grantPermissions($integrationId, [$this->_rootAclResource->getId()]);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function removePermissions($integrationId)
    {
        try {
            $this->_deleteRole($integrationId);
        } catch (\Exception $e) {
            $this->_logger->critical($e);
            throw new LocalizedException(
                __(
                    'Something went wrong while deleting roles and permissions.'
                    . ' You can find out more in the exceptions log.'
                )
            );
        }
    }

    /**
     * Create new ACL role.
     *
     * @param int $integrationId
     * @return \Magento\Authorization\Model\Role
     * @since 2.0.0
     */
    protected function _createRole($integrationId)
    {
        $roleName = UserContextInterface::USER_TYPE_INTEGRATION . $integrationId;
        $role = $this->_roleFactory->create();
        $role->setRoleName($roleName)
            ->setUserType(UserContextInterface::USER_TYPE_INTEGRATION)
            ->setUserId($integrationId)
            ->setRoleType(\Magento\Authorization\Model\Acl\Role\User::ROLE_TYPE)
            ->setParentId(0)
            ->save();
        return $role;
    }

    /**
     * Remove integration role. This deletes the cascading permissions
     *
     * @param int $integrationId
     * @return \Magento\Authorization\Model\Role
     * @since 2.0.0
     */
    protected function _deleteRole($integrationId)
    {
        $roleName = UserContextInterface::USER_TYPE_INTEGRATION . $integrationId;
        $role = $this->_roleFactory->create()->load($roleName, 'role_name');
        return $role->delete();
    }

    /**
     * Identify authorization role associated with provided integration.
     *
     * @param int $integrationId
     * @return \Magento\Authorization\Model\Role|false Return false in case when no role associated with user was found.
     * @since 2.0.0
     */
    protected function _getUserRole($integrationId)
    {
        $roleCollection = $this->_roleCollectionFactory->create();
        /** @var Role $role */
        $role = $roleCollection
            ->setUserFilter($integrationId, UserContextInterface::USER_TYPE_INTEGRATION)
            ->getFirstItem();
        return $role->getId() ? $role : false;
    }

    /**
     * Associate resources with the specified role. All resources previously assigned to the role will be unassigned.
     *
     * @param \Magento\Authorization\Model\Role $role
     * @param string[] $resources
     * @return void
     * @throws \LogicException
     * @since 2.0.0
     */
    protected function _associateResourcesWithRole($role, $resources)
    {
        /** @var \Magento\Authorization\Model\Rules $rules */
        $rules = $this->_rulesFactory->create();
        $rules->setRoleId($role->getId())->setResources($resources)->saveRel();
    }
}
