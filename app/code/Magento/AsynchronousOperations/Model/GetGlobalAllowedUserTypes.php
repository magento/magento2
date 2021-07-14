<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AsynchronousOperations\Model;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\AuthorizationInterface;

/**
 * Returns allowed user types for current user
 */
class GetGlobalAllowedUserTypes
{
    private const BULK_LOGGING_ACL_GUESTS
        = "Magento_AsynchronousOperations::system_magento_logging_bulk_operations_guests";
    private const BULK_LOGGING_ACL_CUSTOMERS
        = "Magento_AsynchronousOperations::system_magento_logging_bulk_operations_customers";
    private const BULK_LOGGING_ACL_INTEGRATIONS
        = "Magento_AsynchronousOperations::system_magento_logging_bulk_operations_integrations";
    private const BULK_LOGGING_ACL_ADMIN
        = "Magento_AsynchronousOperations::system_magento_logging_bulk_operations_admin";

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @param AuthorizationInterface $authorization
     */
    public function __construct(AuthorizationInterface $authorization)
    {
        $this->authorization = $authorization;
    }

    /**
     * Returns allowed user types
     *
     * @return array
     */
    public function execute(): array
    {
        $userTypes = [
            self::BULK_LOGGING_ACL_GUESTS => UserContextInterface::USER_TYPE_GUEST,
            self::BULK_LOGGING_ACL_INTEGRATIONS => UserContextInterface::USER_TYPE_INTEGRATION,
            self::BULK_LOGGING_ACL_ADMIN => UserContextInterface::USER_TYPE_ADMIN,
            self::BULK_LOGGING_ACL_CUSTOMERS => UserContextInterface::USER_TYPE_CUSTOMER
        ];

        $allowedUserTypes = [];
        foreach ($userTypes as $resourceId => $userTypeId) {
            if ($this->authorization->isAllowed($resourceId)) {
                $allowedUserTypes[] = $userTypeId;
            }
        }

        return $allowedUserTypes;
    }
}
