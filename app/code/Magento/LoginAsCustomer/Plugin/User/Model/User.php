<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomer\Plugin\User\Model;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\LoginAsCustomerApi\Api\DeleteAuthenticationDataForUserInterface;
use Magento\User\Api\Data\UserInterface;
use Magento\User\Model\User as UserModel;
use Magento\LoginAsCustomer\Model\Validator\UserRolePermission;
use Psr\Log\LoggerInterface;

/**
 * Check whether role has been updated of existing user
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class User
{
    /**
     * @var array
     */
    private array $originalRoleIds = [];

    /**
     * @param DeleteAuthenticationDataForUserInterface $deleteAuthenticationDataForUser
     * @param UserRolePermission $validator
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly DeleteAuthenticationDataForUserInterface $deleteAuthenticationDataForUser,
        private readonly UserRolePermission $validator,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Capture original role ID before save
     *
     * @param UserModel $subject
     * @return void
     * @throws LocalizedException
     */
    public function beforeSave(UserModel $subject)
    {
        if ($subject->getId()) {
            // Get current role IDs before save
            $currentRoles = $subject->getRoles();
            $this->originalRoleIds[(int) $subject->getId()] = $currentRoles ? (int) $currentRoles[0] : null;
        }
    }

    /**
     * Terminate 'Login as Customer' sessions when related permissions are revoked.
     *
     * @param UserInterface $subject
     * @param UserModel $result
     * @return UserModel
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(
        UserInterface $subject,
        UserModel $result
    ) {
        try {
            if (!$result->getSkipRoleResourceValidation()) {
                $userId = (int) $result->getId();
                $oldRoleId = $this->originalRoleIds[$userId] ?? null;

                if ($this->validator->validateUser($result, $oldRoleId)) {
                    $this->deleteAuthenticationDataForUser->execute($userId);
                }
            }
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return $result;
    }
}
