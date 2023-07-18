<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AsynchronousOperations\Cron;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory;
use Magento\AsynchronousOperations\Api\SaveMultipleOperationsInterface;
use Magento\AsynchronousOperations\Model\ResourceModel\Operation\Collection;
use Magento\AsynchronousOperations\Model\ResourceModel\Operation\CollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Bulk\BulkManagementInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for MarkIncompleteOperationsAsFailed
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MarkIncompleteOperationsAsFailedTest extends TestCase
{
    /**
     * @var MarkIncompleteOperationsAsFailed
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $objectManager = Bootstrap::getObjectManager();
        $this->model = $objectManager->create(MarkIncompleteOperationsAsFailed::class);
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testExecute(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $jsonSerializer = $objectManager->get(Json::class);
        $resource = $objectManager->get(ResourceConnection::class);
        $topicName = 'topic_name_1';
        $buuid = uniqid('bulk-');
        $startedAt = $resource->getConnection()->formatDate(new \DateTime('-14 hours', new \DateTimeZone('UTC')));
        $operationsData = [
            [
                OperationInterface::ID => 0,
                OperationInterface::STATUS => OperationInterface::STATUS_TYPE_COMPLETE,
                'started_at' => $startedAt,
            ],
            [
                OperationInterface::ID => 1,
                OperationInterface::STATUS => OperationInterface::STATUS_TYPE_OPEN,
                'started_at' => $startedAt,
            ],
            [
                OperationInterface::ID => 2,
                OperationInterface::STATUS => OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED,
                'started_at' => $startedAt,
            ],
            [
                OperationInterface::ID => 3,
                OperationInterface::STATUS => OperationInterface::STATUS_TYPE_OPEN,
                'started_at' => null,
            ]
        ];
        $operationFactory = $objectManager->get(OperationInterfaceFactory::class);
        $operations = [];
        foreach ($operationsData as $data) {
            $data += [
                OperationInterface::BULK_ID => $buuid,
                OperationInterface::TOPIC_NAME => $topicName,
                OperationInterface::SERIALIZED_DATA => $jsonSerializer->serialize([]),
            ];
            $operations[] = $operationFactory->create(['data' => $data]);
        }
        $bulkManagement = $objectManager->get(BulkManagementInterface::class);
        $saveMultipleOperations = $objectManager->get(SaveMultipleOperationsInterface::class);
        $bulkManagement->scheduleBulk($buuid, [], 'test bulk');
        $saveMultipleOperations->execute($operations);

        $this->model->execute();

        $operationCollectionFactory = $objectManager->get(CollectionFactory::class);
        /** @var Collection $collection */
        $collection = $operationCollectionFactory->create();
        $collection->addFieldToFilter(
            OperationInterface::BULK_ID,
            ['eq' => $buuid]
        );
        $collection->addFieldToFilter(
            OperationInterface::STATUS,
            ['eq' => OperationInterface::STATUS_TYPE_RETRIABLY_FAILED]
        );
        $this->assertEquals(1, $collection->count());
        $operation = $collection->getFirstItem();
        $this->assertEquals(1, $operation->getId());
        $this->assertEquals(0, $operation->getErrorCode());
        $this->assertEquals('Unknown Error', $operation->getResultMessage());
    }
}
