<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Model;

use Magento\AsynchronousOperations\Api\Data\BulkSummaryInterfaceFactory;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\EntityManager\EntityManager;

/**
 * Class AccessValidator
 */
class AccessValidator
{
    public const BULK_LOGGING_ACL_ALL = "Magento_Logging::system_magento_logging_bulk_operations";
    public const BULK_LOGGING_ACL_GUESTS = "Magento_Logging::system_magento_logging_bulk_operations_guests";
    public const BULK_LOGGING_ACL_INTEGRATIONS = "Magento_Logging::system_magento_logging_bulk_operations_integrations";
    public const BULK_LOGGING_ACL_ADMIN = "Magento_Logging::system_magento_logging_bulk_operations_admin";
    public const BULK_LOGGING_ACL_CUSTOMERS = "Magento_Logging::system_magento_logging_bulk_operations_customers";
    public const BULK_LOGGING_ACL_OWN = "Magento_Logging::system_magento_logging_bulk_operations_admin_own";

    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var BulkSummaryInterfaceFactory
     */
    private $bulkSummaryFactory;

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * AccessValidator constructor.
     *
     * @param UserContextInterface $userContext
     * @param EntityManager $entityManager
     * @param BulkSummaryInterfaceFactory $bulkSummaryFactory
     * @param AuthorizationInterface $authorization
     */
    public function __construct(
        UserContextInterface $userContext,
        EntityManager $entityManager,
        BulkSummaryInterfaceFactory $bulkSummaryFactory,
        AuthorizationInterface $authorization
    ) {
        $this->userContext = $userContext;
        $this->entityManager = $entityManager;
        $this->bulkSummaryFactory = $bulkSummaryFactory;
        $this->authorization = $authorization;
    }

    /**
     * Check if content allowed for current user
     *
     * @param int $bulkUuid
     * @return bool
     */
    public function isAllowed($bulkUuid)
    {
        if ($this->authorization->isAllowed(self::BULK_LOGGING_ACL_ALL)) {
            return true;
        }

        /** @var BulkSummaryInterface $bulkSummary */
        $bulkSummary = $this->entityManager->load(
            $this->bulkSummaryFactory->create(),
            $bulkUuid
        );

        $allowedUserTypes = $this->getAllowedUserTypes();
        if (in_array($bulkSummary->getUserType(), $allowedUserTypes)){
            return true;
        }

        exit;

        return $bulkSummary->getUserId() === $this->userContext->getUserId()
            && $bulkSummary->getUserType(), === $this->userContext->getUserType();
    }

    public function getAllowedUserTypes()
    {
        $userTypes = [
            self::BULK_LOGGING_ACL_GUESTS => UserContextInterface::USER_TYPE_GUEST,
            self::BULK_LOGGING_ACL_INTEGRATIONS => UserContextInterface::USER_TYPE_INTEGRATION,
            self::BULK_LOGGING_ACL_ADMIN => UserContextInterface::USER_TYPE_ADMIN,
            self::BULK_LOGGING_ACL_CUSTOMERS => UserContextInterface::USER_TYPE_CUSTOMER
        ];

        return array_map(function ($resourceId, $userTypeId) {
            if ($this->authorization->isAllowed($resourceId)) {
                return $userTypeId;
            }
        }, array_keys($userTypes), $userTypes);
    }
}
