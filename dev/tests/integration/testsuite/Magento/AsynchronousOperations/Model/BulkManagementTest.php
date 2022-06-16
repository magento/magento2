<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Model;

use Magento\AsynchronousOperations\Api\Data\BulkSummaryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\ObjectManagerInterface;
use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\Framework\MessageQueue\BulkPublisherInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\EntityManager;
use Magento\AsynchronousOperations\Model\ResourceModel\Operation\CollectionFactory;
use Magento\AsynchronousOperations\Api\Data\BulkSummaryInterfaceFactory;
use Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BulkManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject_MockObject
     */
    private $publisherMock;

    /**
     * @var BulkManagement
     */
    private $model;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->publisherMock = $this->getMockForAbstractClass(BulkPublisherInterface::class);

        $this->model = $this->objectManager->create(
            BulkManagement::class,
            [
                'publisher' => $this->publisherMock
            ]
        );
    }

    public function testScheduleBulk()
    {
        // general bulk information
        $bulkUuid = '5a12c1bd-a8b5-41d4-8c00-3f5bcaa6d3c7';
        $bulkDescription = 'Bulk General Information';
        $topicName = 'example.topic.name';
        $userId = 1;

        // generate bulk operations that must be saved
        $operationCount = 100;
        $operations = [];
        $operationFactory = $this->objectManager->get(OperationInterfaceFactory::class);
        for ($index = 0; $index < $operationCount; $index++) {
            /** @var OperationInterface $operation */
            $operation = $operationFactory->create();
            $operation->setBulkUuid($bulkUuid);
            $operation->setTopicName($topicName);
            $operation->setSerializedData(json_encode(['entity_id' => $index]));
            $operations[] = $operation;
        }

        $this->publisherMock->expects($this->once())
            ->method('publish')
            ->with($topicName, $operations);

        // schedule bulk
        $this->assertTrue($this->model->scheduleBulk($bulkUuid, $operations, $bulkDescription, $userId));
        $storedData = $this->getStoredOperationData();
        // No operations should be saved to database during bulk creation
        $this->assertCount(0, $storedData);
    }

    /**
     * @magentoDataFixture Magento/AsynchronousOperations/_files/bulk.php
     */
    public function testRetryBulk()
    {
        $bulkUuid = 'bulk-uuid-5';
        $topicName = 'topic-4';
        $errorCodes = [1111, 2222];
        $operations = $this->objectManager->get(CollectionFactory::class)
            ->create()
            ->addFieldToFilter('bulk_uuid', ['eq' => $bulkUuid])
            ->getItems();
        $this->publisherMock->expects($this->once())
            ->method('publish')
            ->with($topicName, array_values($operations));
        $this->assertEquals(2, $this->model->retryBulk($bulkUuid, $errorCodes));

        $operations = $this->objectManager->get(CollectionFactory::class)
            ->create()
            ->addFieldToFilter('bulk_uuid', ['eq' => $bulkUuid])
            ->getItems();
        // Failed operations should be removed from database during bulk retry
        $this->assertCount(0, $operations);
    }

    /**
     * @magentoDataFixture Magento/AsynchronousOperations/_files/bulk.php
     */
    public function testDeleteBulk()
    {
        $this->model->deleteBulk('bulk-uuid-1');

        /** @var EntityManager $entityManager */
        $entityManager = $this->objectManager->get(EntityManager::class);
        $bulkSummaryFactory = $this->objectManager->get(BulkSummaryInterfaceFactory::class);
        /** @var BulkSummaryInterface $bulkSummary */
        $bulkSummary = $entityManager->load($bulkSummaryFactory->create(), 'bulk-uuid-1');
        $this->assertNull($bulkSummary->getBulkId());
    }

    /**
     * Retrieve stored operation data
     *
     * @return array
     * @throws \Exception
     */
    private function getStoredOperationData()
    {
        /** @var MetadataPool $metadataPool */
        $metadataPool = $this->objectManager->get(MetadataPool::class);
        $operationMetadata = $metadataPool->getMetadata(OperationInterface::class);
        /** @var ResourceConnection $resourceConnection */
        $resourceConnection = $this->objectManager->get(ResourceConnection::class);
        $connection = $resourceConnection->getConnectionByName($operationMetadata->getEntityConnectionName());

        return $connection->fetchAll($connection->select()->from($operationMetadata->getEntityTable()));
    }
}
