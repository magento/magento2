<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Attribute\Backend\WebsiteSpecific;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\Catalog\Model\ResourceModel\Attribute\WebsiteAttributesSynchronizer;
use Magento\Framework\DB\Adapter\ConnectionException;
use Magento\Framework\DB\Adapter\DeadlockException;
use Magento\Framework\DB\Adapter\LockWaitException;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;

class ValueSynchronizer
{
    /**
     * @param EntityManager $entityManager
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     * @param WebsiteAttributesSynchronizer $websiteAttributesSynchronizer
     */
    public function __construct(
        private EntityManager $entityManager,
        private SerializerInterface $serializer,
        private LoggerInterface $logger,
        private WebsiteAttributesSynchronizer $websiteAttributesSynchronizer
    ) {
    }

    /**
     * Process website specific values synchronization.
     *
     * @param OperationInterface $operation
     * @return void
     */
    public function process(OperationInterface $operation): void
    {
        try {
            $serializedData = $operation->getSerializedData();
            $data = $this->serializer->unserialize($serializedData);
            $this->websiteAttributesSynchronizer->synchronizeStoreValues($data['store_id']);
            $operation->setStatus(OperationInterface::STATUS_TYPE_COMPLETE);
            $operation->setResultMessage(null);
        } catch (LockWaitException|DeadlockException|ConnectionException $e) {
            $operation->setStatus(OperationInterface::STATUS_TYPE_RETRIABLY_FAILED);
            $operation->setErrorCode($e->getCode());
            $operation->setResultMessage($e->getMessage());
        } catch (LocalizedException $e) {
            $operation->setStatus(OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED);
            $operation->setErrorCode($e->getCode());
            $operation->setResultMessage($e->getMessage());
        } catch (\Throwable $e) {
            $this->logger->critical($e);
            $operation->setStatus(OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED);
            $operation->setErrorCode($e->getCode());
            $operation->setResultMessage(
                __('Sorry, something went wrong during update synchronization. Please see log for details.')
            );
        }
        $this->entityManager->save($operation);
    }
}
