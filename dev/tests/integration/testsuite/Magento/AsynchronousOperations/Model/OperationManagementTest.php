<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Model;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory;
use Magento\Framework\EntityManager\EntityManager;

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

    protected function setUp()
    {
        $this->model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\AsynchronousOperations\Model\OperationManagement::class
        );
        $this->bulkStatusManagement = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\AsynchronousOperations\Model\BulkStatus::class
        );

        $this->operationFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            OperationInterfaceFactory::class
        );
        $this->entityManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            EntityManager::class
        );
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

        $this->assertTrue($this->model->changeOperationStatus($operationId, OperationInterface::STATUS_TYPE_OPEN));

        /** @var OperationInterface $updatedOperation */
        $updatedOperation = $this->operationFactory->create();
        $this->entityManager->load($updatedOperation, $operationId);
        $this->assertEquals(OperationInterface::STATUS_TYPE_OPEN, $updatedOperation->getStatus());
        $this->assertEquals(null, $updatedOperation->getResultMessage());
        $this->assertEquals(null, $updatedOperation->getSerializedData());
    }
}
