<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Model;

use Magento\AsynchronousOperations\Api\Data\DetailedOperationStatusInterfaceFactory;
use Magento\Framework\EntityManager\EntityManager;

/**
 * Class OperationManagement
 */
class OperationManagement implements \Magento\Framework\Bulk\OperationManagementInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var DetailedOperationStatusInterfaceFactory
     */
    private $operationFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * OperationManagement constructor.
     *
     * @param EntityManager $entityManager
     * @param DetailedOperationStatusInterfaceFactory $operationFactory
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        EntityManager $entityManager,
        DetailedOperationStatusInterfaceFactory $operationFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->operationFactory = $operationFactory;
        $this->logger = $logger;
    }
    
    /**
     * @inheritDoc
     */
    public function changeOperationStatus(
        $operationId,
        $status,
        $errorCode = null,
        $message = null,
        $data = null,
        $resultData = null
    ) {
        try {
            $operationEntity = $this->operationFactory->create();
            $this->entityManager->load($operationEntity, $operationId);
            $operationEntity->setErrorCode($errorCode);
            $operationEntity->setStatus($status);
            $operationEntity->setResultMessage($message);
            $operationEntity->setSerializedData($data);
            $operationEntity->setResultSerializedData($resultData);
            $operationEntity->setResultSerializedData($resultData);
            $this->entityManager->save($operationEntity);
        } catch (\Exception $exception) {
            $this->logger->critical($exception->getMessage());
            return false;
        }
        return true;
    }
}
