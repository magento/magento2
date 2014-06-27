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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Authz\Service;

use Magento\Authz\Model\UserIdentifier;
use Magento\Framework\Acl;
use Magento\Framework\Acl\Builder as AclBuilder;
use Magento\Framework\Acl\RootResource as RootAclResource;
use Magento\Framework\Logger;
use Magento\User\Model\Resource\Role\CollectionFactory as RoleCollectionFactory;
use Magento\User\Model\Resource\Rules\CollectionFactory as RulesCollectionFactory;
use Magento\User\Model\Role;
use Magento\User\Model\RoleFactory;
use Magento\User\Model\RulesFactory;
use Magento\Webapi\ServiceException as ServiceException;
use Magento\Webapi\ServiceResourceNotFoundException;

/**
 * Authorization service.
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AuthorizationV1 implements AuthorizationV1Interface
{
    const PERMISSION_ANONYMOUS = 'anonymous';
    const PERMISSION_SELF = 'self';

    /**
     * @var AclBuilder
     */
    protected $_aclBuilder;

    /**
     * @var UserIdentifier
     */
    protected $_userIdentifier;

    /**
     * @var RoleFactory
     */
    protected $_roleFactory;

    /**
     * @var RoleCollectionFactory
     */
    protected $_roleCollectionFactory;

    /**
     * @var RulesFactory
     */
    protected $_rulesFactory;

    /**
     * @var RulesCollectionFactory
     */
    protected $_rulesCollectionFactory;

    /**
     * @var Logger
     */
    protected $_logger;

    /**
     * @var RootAclResource
     */
    protected $_rootAclResource;

    /**
     * Initialize dependencies.
     *
     * @param AclBuilder $aclBuilder
     * @param UserIdentifier $userIdentifier
     * @param RoleFactory $roleFactory
     * @param RoleCollectionFactory $roleCollectionFactory
     * @param RulesFactory $rulesFactory
     * @param RulesCollectionFactory $rulesCollectionFactory
     * @param Logger $logger
     * @param RootAclResource $rootAclResource
     */
    public function __construct(
        AclBuilder $aclBuilder,
        UserIdentifier $userIdentifier,
        RoleFactory $roleFactory,
        RoleCollectionFactory $roleCollectionFactory,
        RulesFactory $rulesFactory,
        RulesCollectionFactory $rulesCollectionFactory,
        Logger $logger,
        RootAclResource $rootAclResource
    ) {
        $this->_aclBuilder = $aclBuilder;
        $this->_userIdentifier = $userIdentifier;
        $this->_roleFactory = $roleFactory;
        $this->_rulesFactory = $rulesFactory;
        $this->_rulesCollectionFactory = $rulesCollectionFactory;
        $this->_roleCollectionFactory = $roleCollectionFactory;
        $this->_logger = $logger;
        $this->_rootAclResource = $rootAclResource;
    }

    /**
     * {@inheritdoc}
     */
    public function isAllowed($resources, UserIdentifier $userIdentifier = null)
    {
        $resources = is_array($resources) ? $resources : [$resources];
        $userIdentifier = $userIdentifier ? $userIdentifier : $this->_userIdentifier;
        if ($this->_isAnonymousOrSelfAllowed($resources, $userIdentifier)) {
            return true;
        }
        return $this->_isUserWithRoleAllowed($resources, $userIdentifier);
    }

    /**
     * {@inheritdoc}
     */
    public function grantPermissions(UserIdentifier $userIdentifier, array $resources)
    {
        try {
            $role = $this->_getUserRole($userIdentifier);
            if (!$role) {
                $role = $this->_createRole($userIdentifier);
            }
            $this->_associateResourcesWithRole($role, $resources);
        } catch (ServiceException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->_logger->logException($e);
            throw new ServiceException(
                __('Error happened while granting permissions. Check exception log for details.')
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function grantAllPermissions(UserIdentifier $userIdentifier)
    {
        $this->grantPermissions($userIdentifier, array($this->_rootAclResource->getId()));
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedResources(UserIdentifier $userIdentifier)
    {
        if ($userIdentifier->getUserType() == UserIdentifier::USER_TYPE_GUEST) {
            return [self::PERMISSION_ANONYMOUS];
        } elseif ($userIdentifier->getUserType() == UserIdentifier::USER_TYPE_CUSTOMER) {
            return [self::PERMISSION_SELF];
        }
        $allowedResources = [];
        try {
            $role = $this->_getUserRole($userIdentifier);
            if (!$role) {
                throw new ServiceException(__('The role associated with the specified user cannot be found.'));
            }
            $rulesCollection = $this->_rulesCollectionFactory->create();
            $rulesCollection->getByRoles($role->getId())->load();
            $acl = $this->_aclBuilder->getAcl();
            /** @var \Magento\User\Model\Rules $ruleItem */
            foreach ($rulesCollection->getItems() as $ruleItem) {
                $resourceId = $ruleItem->getResourceId();
                if ($acl->has($resourceId) && $acl->isAllowed($role->getId(), $resourceId)) {
                    $allowedResources[] = $resourceId;
                }
            }
        } catch (ServiceException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->_logger->logException($e);
            throw new ServiceException(
                __('Error happened while getting a list of allowed resources. Check exception log for details.')
            );
        }
        return $allowedResources;
    }

    /**
     * {@inheritdoc}
     */
    public function removePermissions(UserIdentifier $userIdentifier)
    {
        try {
            $this->_deleteRole($userIdentifier);
        } catch (ServiceException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->_logger->logException($e);
            throw new ServiceException(
                __('Error happened while deleting role and permissions. Check exception log for details.')
            );
        }
    }

    /**
     * Create new ACL role.
     *
     * @param UserIdentifier $userIdentifier
     * @return Role
     * @throws \LogicException
     */
    protected function _createRole($userIdentifier)
    {
        $userType = $userIdentifier->getUserType();
        if (!$this->_canRoleBeCreatedForUserType($userType)) {
            throw new \LogicException("The role with user type '{$userType}' cannot be created");
        }
        $userId = $userIdentifier->getUserId();
        switch ($userType) {
            case UserIdentifier::USER_TYPE_INTEGRATION:
                $roleName = $userType . $userId;
                $roleType = \Magento\User\Model\Acl\Role\User::ROLE_TYPE;
                $parentId = 0;
                $userId = $userIdentifier->getUserId();
                break;
            default:
                throw new \LogicException("Unknown user type: '{$userType}'.");
        }
        $role = $this->_roleFactory->create();
        $role->setRoleName($roleName)
            ->setUserType($userType)
            ->setUserId($userId)
            ->setRoleType($roleType)
            ->setParentId($parentId)
            ->save();
        return $role;
    }

    /**
     * Remove an ACL role. This deletes the cascading permissions
     *
     * @param UserIdentifier $userIdentifier
     * @return Role
     * @throws \LogicException
     */
    protected function _deleteRole($userIdentifier)
    {
        $userType = $userIdentifier->getUserType();
        if (!$this->_canRoleBeCreatedForUserType($userType)) {
            throw new \LogicException("The role with user type '{$userType}' cannot be created or deleted.");
        }
        $userId = $userIdentifier->getUserId();
        switch ($userType) {
            case UserIdentifier::USER_TYPE_INTEGRATION:
                $roleName = $userType . $userId;
                break;
            default:
                throw new \LogicException("Unknown user type: '{$userType}'.");
        }
        $role = $this->_roleFactory->create()->load($roleName, 'role_name');
        return $role->delete();
    }

    /**
     * Identify user role from user identifier.
     *
     * @param UserIdentifier $userIdentifier
     * @return Role|false Return false in case when no role associated with provided user was found.
     * @throws \LogicException
     */
    protected function _getUserRole($userIdentifier)
    {
        if (!$this->_canRoleBeCreatedForUserType($userIdentifier)) {
            throw new \LogicException(
                "The role with user type '{$userIdentifier->getUserType()}' does not exist and cannot be created"
            );
        }
        $roleCollection = $this->_roleCollectionFactory->create();
        $userType = $userIdentifier->getUserType();
        /** @var Role $role */
        $userId = $userIdentifier->getUserId();
        $role = $roleCollection->setUserFilter($userId, $userType)->getFirstItem();
        return $role->getId() ? $role : false;
    }

    /**
     * Associate resources with the specified role. All resources previously assigned to the role will be unassigned.
     *
     * @param Role $role
     * @param string[] $resources
     * @return void
     * @throws \LogicException
     */
    protected function _associateResourcesWithRole($role, array $resources)
    {
        /** @var \Magento\User\Model\Rules $rules */
        $rules = $this->_rulesFactory->create();
        $rules->setRoleId($role->getId())->setResources($resources)->saveRel();
    }

    /**
     * Check if there role can be associated with user having provided user type.
     *
     * Roles cannot be created for guests and customers.
     *
     * @param string $userType
     * @return bool
     */
    protected function _canRoleBeCreatedForUserType($userType)
    {
        return ($userType != UserIdentifier::USER_TYPE_CUSTOMER) && ($userType != UserIdentifier::USER_TYPE_GUEST);
    }

    /**
     * Check if the user has permission to access the requested resources.
     *
     * @param string[] $resources
     * @param UserIdentifier $userIdentifier
     * @return bool
     */
    protected function _isAnonymousOrSelfAllowed($resources, UserIdentifier $userIdentifier)
    {
        if (count($resources) == 1) {
            $resource = reset($resources);
            $isAnonymousAccess = ($resource == self::PERMISSION_ANONYMOUS);
            $isSelfAccess = ($userIdentifier->getUserType() == UserIdentifier::USER_TYPE_CUSTOMER)
                && ($resource == self::PERMISSION_SELF);
            if ($isAnonymousAccess || $isSelfAccess) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if user who has role is allowed to access requested resources.
     *
     * @param string[] $resources
     * @param UserIdentifier $userIdentifier
     * @return bool
     */
    protected function _isUserWithRoleAllowed($resources, UserIdentifier $userIdentifier)
    {
        try {
            $role = $this->_getUserRole($userIdentifier);
            if (!$role) {
                throw new ServiceResourceNotFoundException(
                    __(
                        'Role for user with ID "%1" and user type "%2" cannot be found.',
                        $userIdentifier->getUserId(),
                        $userIdentifier->getUserType()
                    )
                );
            }
            foreach ($resources as $resource) {
                if (!$this->_aclBuilder->getAcl()->isAllowed($role->getId(), $resource)) {
                    return false;
                }
            }
            return true;
        } catch (\Exception $e) {
            $this->_logger->logException($e);
            return false;
        }
    }
}
