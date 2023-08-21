<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Model;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory;
use Magento\AsynchronousOperations\Model\BulkStatus;
use Magento\AsynchronousOperations\Model\OperationManagement;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\App\ResourceConnection;
use Magento\TestFramework\Helper\Bootstrap;

class OperationManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\AsynchronousOperations\Model\OperationManagement
     */
    private $model;

    /**
     * @var \Magento\AsynchronousOperations\Model\BulkStatus
     */
    private $bulkStatusManagement;

    /**
     * @var OperationInterfaceFactory
     */
    private $operationFactory;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var ResourceConnection
     */
    private $connection;

    protected function setUp(): void
    {
        $this->connection = Bootstrap::getObjectManager()->get(ResourceConnection::class);
        $this->model = Bootstrap::getObjectManager()->get(OperationManagement::class);
        $this->bulkStatusManagement = Bootstrap::getObjectManager()->get(BulkStatus::class);
        $this->operationFactory = Bootstrap::getObjectManager()->get(OperationInterfaceFactory::class);
        $this->entityManager = Bootstrap::getObjectManager()->get(EntityManager::class);
    }

    /**
     * @magentoDataFixture Magento/AsynchronousOperations/_files/bulk.php
     */
    public function testGetBulkStatus()
    {
        $operations =  $this->bulkStatusManagement->getFailedOperationsByBulkId('bulk-uuid-5', 3);
        if (empty($operations)) {
            $this->fail('Operation doesn\'t exist');
        }
        /** @var OperationInterface $operation */
        $operation = array_shift($operations);
        $operationId = $operation->getId();

        $this->assertTrue($this->model->changeOperationStatus(
            'bulk-uuid-5',
            $operationId,
            OperationInterface::STATUS_TYPE_OPEN
        ));

        $table = $this->connection->getTableName('magento_operation');
        $connection = $this->connection->getConnection();
        $select = $connection->select()
            ->from($table)
            ->where("bulk_uuid = ?", 'bulk-uuid-5')
            ->where("operation_key = ?", $operationId);
        $updatedOperation = $connection->fetchRow($select);

        $this->assertEquals(OperationInterface::STATUS_TYPE_OPEN, $updatedOperation['status']);
        $this->assertNull($updatedOperation['result_message']);
        $this->assertNull($updatedOperation['serialized_data']);
    }
}
