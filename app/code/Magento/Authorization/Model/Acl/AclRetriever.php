<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Authorization\Model\Acl;

use Magento\Authorization\Model\Resource\Role\CollectionFactory as RoleCollectionFactory;
use Magento\Authorization\Model\Resource\Rules\CollectionFactory as RulesCollectionFactory;
use Magento\Authorization\Model\Role;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Acl\Builder as AclBuilder;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface as Logger;

/**
 * Permission tree retriever
 */
class AclRetriever
{
    const PERMISSION_ANONYMOUS = 'anonymous';
    const PERMISSION_SELF = 'self';

    /** @var Logger */
    protected $logger;

    /** @var RulesCollectionFactory */
    protected $rulesCollectionFactory;

    /** @var AclBuilder */
    protected $aclBuilder;

    /** @var RoleCollectionFactory */
    protected $roleCollectionFactory;

    /**
     * Initialize dependencies.
     *
     * @param AclBuilder $aclBuilder
     * @param RoleCollectionFactory $roleCollectionFactory
     * @param RulesCollectionFactory $rulesCollectionFactory
     * @param Logger $logger
     */
    public function __construct(
        AclBuilder $aclBuilder,
        RoleCollectionFactory $roleCollectionFactory,
        RulesCollectionFactory $rulesCollectionFactory,
        Logger $logger
    ) {
        $this->logger = $logger;
        $this->rulesCollectionFactory = $rulesCollectionFactory;
        $this->aclBuilder = $aclBuilder;
        $this->roleCollectionFactory = $roleCollectionFactory;
    }

    /**
     * Get a list of available resources using user details
     *
     * @param string $userType
     * @param int $userId
     * @return string[]
     * @throws AuthorizationException
     * @throws LocalizedException
     */
    public function getAllowedResourcesByUser($userType, $userId)
    {
        if ($userType == UserContextInterface::USER_TYPE_GUEST) {
            return [self::PERMISSION_ANONYMOUS];
        } elseif ($userType == UserContextInterface::USER_TYPE_CUSTOMER) {
            return [self::PERMISSION_SELF];
        }
        try {
            $role = $this->_getUserRole($userType, $userId);
            if (!$role) {
                throw new AuthorizationException('The role associated with the specified user cannot be found.');
            }
            $allowedResources = $this->getAllowedResourcesByRole($role->getId());
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new LocalizedException(
                'Error happened while getting a list of allowed resources. Check exception log for details.'
            );
        }
        return $allowedResources;
    }

    /**
     * Get a list of available resource using user role id
     *
     * @param string $roleId
     * @return string[]
     */
    public function getAllowedResourcesByRole($roleId)
    {
        $allowedResources = [];
        $rulesCollection = $this->rulesCollectionFactory->create();
        $rulesCollection->getByRoles($roleId)->load();
        $acl = $this->aclBuilder->getAcl();
        /** @var \Magento\Authorization\Model\Rules $ruleItem */
        foreach ($rulesCollection->getItems() as $ruleItem) {
            $resourceId = $ruleItem->getResourceId();
            if ($acl->has($resourceId) && $acl->isAllowed($roleId, $resourceId)) {
                $allowedResources[] = $resourceId;
            }
        }
        return $allowedResources;
    }

    /**
     * Identify user role from user identifier.
     *
     * @param string $userType
     * @param int $userId
     * @return \Magento\Authorization\Model\Role|bool False if no role associated with provided user was found.
     * @throws \LogicException
     */
    protected function _getUserRole($userType, $userId)
    {
        if (!$this->_canRoleBeCreatedForUserType($userType)) {
            throw new \LogicException(
                "The role with user type '{$userType}' does not exist and cannot be created"
            );
        }
        $roleCollection = $this->roleCollectionFactory->create();
        /** @var Role $role */
        $role = $roleCollection->setUserFilter($userId, $userType)->getFirstItem();
        return $role->getId() ? $role : false;
    }

    /**
     * Check if the role can be associated with user having provided user type.
     *
     * Roles can be created for integrations and admin users only.
     *
     * @param int $userType
     * @return bool
     */
    protected function _canRoleBeCreatedForUserType($userType)
    {
        return ($userType == UserContextInterface::USER_TYPE_INTEGRATION)
            || ($userType == UserContextInterface::USER_TYPE_ADMIN);
    }
}
