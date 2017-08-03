<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Model;

/**
 * Class AccessValidator
 * @since 2.2.0
 */
class AccessValidator
{
    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     * @since 2.2.0
     */
    private $userContext;

    /**
     * @var \Magento\Framework\EntityManager\EntityManager
     * @since 2.2.0
     */
    private $entityManager;

    /**
     * @var \Magento\AsynchronousOperations\Api\Data\BulkSummaryInterfaceFactory
     * @since 2.2.0
     */
    private $bulkSummaryFactory;

    /**
     * AccessValidator constructor.
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     * @param \Magento\Framework\EntityManager\EntityManager $entityManager
     * @param \Magento\AsynchronousOperations\Api\Data\BulkSummaryInterfaceFactory $bulkSummaryFactory
     * @since 2.2.0
     */
    public function __construct(
        \Magento\Authorization\Model\UserContextInterface $userContext,
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
     * @since 2.2.0
     */
    public function isAllowed($bulkUuid)
    {
        /** @var \Magento\AsynchronousOperations\Api\Data\BulkSummaryInterface $bulkSummary */
        $bulkSummary = $this->entityManager->load(
            $this->bulkSummaryFactory->create(),
            $bulkUuid
        );
        return $bulkSummary->getUserId() === $this->userContext->getUserId();
    }
}
