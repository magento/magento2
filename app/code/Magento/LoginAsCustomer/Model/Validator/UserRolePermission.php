<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);
namespace Magento\LoginAsCustomer\Model\Validator;

use Magento\Authorization\Model\Rules;
use Magento\Framework\Exception\LocalizedException;
use Magento\User\Model\User as UserModel;
use Magento\Authorization\Model\Acl\AclRetriever;
use Magento\LoginAsCustomerApi\Api\ConfigInterface as LoginAsCustomerConfig;
use Magento\Framework\Acl\RootResource;

/**
 * User role permission validator
 */
class UserRolePermission
{
    /**
     * @var string
     */
    private const ACL_RESOURCE = 'Magento_LoginAsCustomer::login';

    /**
     * @param AclRetriever $aclRetriever
     * @param LoginAsCustomerConfig $config
     * @param RootResource $rootResource
     */
    public function __construct(
        private readonly AclRetriever $aclRetriever,
        private readonly LoginAsCustomerConfig $config,
        private readonly RootResource $rootResource
    ) {
    }

    /**
     * Validate User role with specific resource
     *
     * @param Rules $rule
     * @return array []
     */
    public function validateRoles(Rules $rule): array
    {
        $resources = $rule->getResources();
        $resources = is_array($resources) ? $resources : [];

        if (!in_array(self::ACL_RESOURCE, $resources, true) &&
            !in_array($this->rootResource->getId(), $resources, true)
        ) {
            $adminUsers = $rule->getRoleAssignedUsers();
            $adminUsers = is_array($adminUsers) ? $adminUsers : [];
            if (!empty($adminUsers)) {
                return $adminUsers;
            }
        }

        return [];
    }

    /**
     * Validate User with specific permission
     *
     * @param UserModel $result
     * @param int|null $oldRoleId
     * @return bool
     * @throws LocalizedException
     */
    public function validateUser(UserModel $result, ?int $oldRoleId): bool
    {
        $newRoleId = (int) $result->getRoleId();
        return $oldRoleId !== $newRoleId
            && $this->roleHasResource($newRoleId, self::ACL_RESOURCE);
    }

    /**
     * Check if role has specific resource
     *
     * @param int $roleId
     * @param string $resourceId
     * @return bool
     * @throws LocalizedException
     */
    private function roleHasResource(int $roleId, string $resourceId): bool
    {
        if (!$this->config->isEnabled()) {
            return false;
        }

        $resources = $this->getRoleResources($roleId);
        $resourceSet = array_flip($resources);

        return !isset($resourceSet[$resourceId]) && !isset($resourceSet[$this->rootResource->getId()]);
    }

    /**
     * Get allowed resources for a role
     *
     * @param int $roleId
     * @return array
     * @throws LocalizedException
     */
    private function getRoleResources(int $roleId): array
    {
        try {
            return $this->aclRetriever->getAllowedResourcesByRole($roleId);
        } catch (\Exception $e) {
            throw new LocalizedException(__('An error occurred while saving this user.'));
        }
    }
}
