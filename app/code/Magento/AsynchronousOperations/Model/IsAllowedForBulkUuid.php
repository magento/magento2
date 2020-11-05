<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AsynchronousOperations\Model;

use Magento\Authorization\Model\UserContextInterface;
use Magento\AsynchronousOperations\Api\Data\BulkSummaryInterfaceFactory;
use Magento\AsynchronousOperations\Api\Data\BulkSummaryInterface;
use Magento\Framework\EntityManager\EntityManager;

/**
 * Check if content allowed for current use depends from assigned user roles and bulkUuid
 */
class IsAllowedForBulkUuid
{
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
     * @var array
     */
    private $allowedUserTypes;

    /**
     * @param UserContextInterface $userContext
     * @param EntityManager $entityManager
     * @param BulkSummaryInterfaceFactory $bulkSummaryFactory
     * @param GetGlobalAllowedUserTypes $getGlobalAllowedUserTypes
     */
    public function __construct(
        UserContextInterface $userContext,
        EntityManager $entityManager,
        BulkSummaryInterfaceFactory $bulkSummaryFactory,
        GetGlobalAllowedUserTypes $getGlobalAllowedUserTypes
    ) {
        $this->userContext = $userContext;
        $this->entityManager = $entityManager;
        $this->bulkSummaryFactory = $bulkSummaryFactory;
        $this->allowedUserTypes = $getGlobalAllowedUserTypes->execute();
    }

    /**
     * Returns is content allowed
     *
     * @param string $bulkUuid
     * @return bool
     */
    public function execute(string $bulkUuid): bool
    {
        /** @var BulkSummaryInterface $bulkSummary */
        $bulkSummary = $this->entityManager->load($this->bulkSummaryFactory->create(), $bulkUuid);

        return in_array($bulkSummary->getUserType(), $this->allowedUserTypes) || $this->isAllowedForUser($bulkSummary);
    }

    /**
     * Returns is bulk allowed for user
     *
     * @param BulkSummaryInterface $bulkSummary
     * @return bool
     */
    private function isAllowedForUser(BulkSummaryInterface $bulkSummary): bool
    {
        return $bulkSummary->getUserType() === $this->userContext->getUserType()
            && $bulkSummary->getUserId() === $this->userContext->getUserId();
    }
}
