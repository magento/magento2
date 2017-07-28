<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Model;

use Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory;
use Magento\Framework\EntityManager\EntityManager;

/**
 * Class OperationManagement
 * @since 2.2.0
 */
class OperationManagement implements \Magento\Framework\Bulk\OperationManagementInterface
{
    /**
     * @var EntityManager
     * @since 2.2.0
     */
    private $entityManager;

    /**
     * @var OperationInterfaceFactory
     * @since 2.2.0
     */
    private $operationFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     * @since 2.2.0
     */
    private $logger;

    /**
     * OperationManagement constructor.
     *
     * @param EntityManager $entityManager
     * @param OperationInterfaceFactory $operationFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @since 2.2.0
     */
    public function __construct(
        EntityManager $entityManager,
        OperationInterfaceFactory $operationFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->operationFactory= $operationFactory;
        $this->logger = $logger;
    }
    
    /**
     * @inheritDoc
     * @since 2.2.0
     */
    public function changeOperationStatus($operationId, $status, $errorCode = null, $message = null, $data = null)
    {
        try {
            $operationEntity = $this->operationFactory->create();
            $this->entityManager->load($operationEntity, $operationId);
            $operationEntity->setErrorCode($errorCode);
            $operationEntity->setStatus($status);
            $operationEntity->setResultMessage($message);
            $operationEntity->setSerializedData($data);
            $this->entityManager->save($operationEntity);
        } catch (\Exception $exception) {
            $this->logger->critical($exception->getMessage());
            return false;
        }
        return true;
    }
}
