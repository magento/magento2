<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Model;

use Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;

/**
 * Class for managing Bulk Operations
 */
class OperationManagement implements \Magento\Framework\Bulk\OperationManagementInterface
{
    /**
     * @var ResourceConnection
     */
    private $connection;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var OperationInterfaceFactory
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
     * @param OperationInterfaceFactory $operationFactory
     * @param LoggerInterface $logger
     * @param ResourceConnection $connection
     */
    public function __construct(
        EntityManager $entityManager,
        OperationInterfaceFactory $operationFactory,
        LoggerInterface $logger,
        ResourceConnection $connection
    ) {
        $this->entityManager = $entityManager;
        $this->operationFactory = $operationFactory;
        $this->logger = $logger;
        $this->connection = $connection;
    }

    /**
     * @inheritDoc
     */
    public function changeOperationStatus(
        $bulkUuid,
        $operationKey,
        $status,
        $errorCode = null,
        $message = null,
        $data = null,
        $resultData = null
    ) {
        try {
            $connection = $this->connection->getConnection();
            $table = $this->connection->getTableName('magento_operation');
            $bind = [
                'error_code' => $errorCode,
                'status' => $status,
                'result_message' => $message,
                'serialized_data' => $data,
                'result_serialized_data' => $resultData
            ];
            $where = ['bulk_uuid = ?' => $bulkUuid, 'operation_key = ?' => $operationKey];
            $connection->update($table, $bind, $where);
        } catch (\Exception $exception) {
            $this->logger->critical($exception->getMessage());
            return false;
        }
        return true;
    }
}
