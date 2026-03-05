<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomer\Plugin\Authorization\Model\ResourceModel;

use Exception;
use Magento\Authorization\Model\Rules;
use Magento\Authorization\Model\ResourceModel\Rules as Subject;
use Magento\LoginAsCustomerApi\Api\DeleteAuthenticationDataForListOfUserInterface;
use Magento\LoginAsCustomer\Model\Validator\UserRolePermission;
use Psr\Log\LoggerInterface;

/**
 * Detect current users resources change
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class RulesPlugin
{
    /**
     * @param DeleteAuthenticationDataForListOfUserInterface $deleteAuthenticationDataForListOfUser
     * @param UserRolePermission $validator
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly DeleteAuthenticationDataForListOfUserInterface $deleteAuthenticationDataForListOfUser,
        private readonly UserRolePermission $validator,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Terminate 'Login as Customer' sessions when related permissions are revoked.
     *
     * @param Subject $subject
     * @param void $result
     * @param Rules $rule
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSaveRel(Subject $subject, $result, Rules $rule): void
    {
        try {
            $usersToTerminate = array_merge(
                $this->validator->validateRoles($rule),
                $this->ensureArray($rule->getRoleUnassignedUsers())
            );
            if ($usersToTerminate) {
                $this->deleteAuthenticationDataForListOfUser->execute(array_unique($usersToTerminate));
            }
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Ensure a value is an array
     *
     * @param mixed $value
     * @return array
     */
    private function ensureArray($value): array
    {
        return is_array($value) ? $value : [];
    }
}
