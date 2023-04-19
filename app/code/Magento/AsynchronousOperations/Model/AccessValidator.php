<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Model;

use Magento\AsynchronousOperations\Api\Data\BulkSummaryInterface;
use Magento\Authorization\Model\UserContextInterface;

class AccessValidator
{
    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @var \Magento\Framework\EntityManager\EntityManager
     */
    private $entityManager;

    /**
     * @var \Magento\AsynchronousOperations\Api\Data\BulkSummaryInterfaceFactory
     */
    private $bulkSummaryFactory;

    /**
     * @param UserContextInterface $userContext
     * @param \Magento\Framework\EntityManager\EntityManager $entityManager
     * @param \Magento\AsynchronousOperations\Api\Data\BulkSummaryInterfaceFactory $bulkSummaryFactory
     */
    public function __construct(
        UserContextInterface $userContext,
        \Magento\Framework\EntityManager\EntityManager $entityManager,
        \Magento\AsynchronousOperations\Api\Data\BulkSummaryInterfaceFactory $bulkSummaryFactory
    ) {
        $this->userContext = $userContext;
        $this->entityManager = $entityManager;
        $this->bulkSummaryFactory = $bulkSummaryFactory;
    }

    /**
     * Check if content allowed for current user
     *
     * @param int $bulkUuid
     * @return bool
     */
    public function isAllowed($bulkUuid)
    {
        /** @var BulkSummaryInterface $bulkSummary */
        $bulkSummary = $this->entityManager->load(
            $this->bulkSummaryFactory->create(),
            $bulkUuid
        );
        if ((int) $bulkSummary->getUserType() === UserContextInterface::USER_TYPE_INTEGRATION) {
            return true;
        }

        return ((int) $bulkSummary->getUserId()) === ((int) $this->userContext->getUserId());
    }
}
