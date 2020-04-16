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
 * Class AccessManager providing information
 * about valid resources for Bulk Reports
 */
class AccessManager
{
    public const BULK_LOGGING_ACL_GUESTS
        = "Magento_AsynchronousOperations::system_magento_logging_bulk_operations_guests";
    public const BULK_LOGGING_ACL_CUSTOMERS
        = "Magento_AsynchronousOperations::system_magento_logging_bulk_operations_customers";
    public const BULK_LOGGING_ACL_INTEGRATIONS
        = "Magento_AsynchronousOperations::system_magento_logging_bulk_operations_integrations";
    public const BULK_LOGGING_ACL_ADMIN
        = "Magento_AsynchronousOperations::system_magento_logging_bulk_operations_admin";
    public const BULK_LOGGING_ACL
        = "Magento_AsynchronousOperations::system_magento_logging_bulk_operations";

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
     * @var array
     */
    private $allowedUserTypes;

    /**
     * AccessManager constructor.
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
        $this->allowedUserTypes = $this->getGlobalAllowedUserTypes();
    }

    /**
     * Check if content allowed for current use depends from assigned user roles and bulkUuid
     *
     * @param int $bulkUuid
     * @return bool
     */
    public function isAllowedForBulkUuid($bulkUuid)
    {

        /** @var BulkSummaryInterface $bulkSummary */
        $bulkSummary = $this->entityManager->load(
            $this->bulkSummaryFactory->create(),
            $bulkUuid
        );

        if (in_array($bulkSummary->getUserType(), $this->allowedUserTypes)) {
            return true;
        }

        if ($bulkSummary->getUserType() === $this->userContext->getUserType()
            && $bulkSummary->getUserId() === $this->userContext->getUserId()) {
            return true;
        }

        return false;
    }

    /**
     * Get Allowed user types for current user
     *
     * @return array
     */
    public function getGlobalAllowedUserTypes()
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

    /**
     * Check if it allowed to see own bulk operations.
     *
     * @return bool
     */
    public function isOwnActionsAllowed()
    {
        return $this->authorization->isAllowed(self::BULK_LOGGING_ACL);
    }
}
