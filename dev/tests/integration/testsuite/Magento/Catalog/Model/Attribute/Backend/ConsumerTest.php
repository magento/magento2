<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Model\Attribute\Backend;

use Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\ObjectManagerInterface;
use Magento\AsynchronousOperations\Model\BulkManagement;
use Magento\AsynchronousOperations\Model\BulkStatus;
use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\Framework\MessageQueue\BulkPublisherInterface;

class ConsumerTest extends TestCase
{
    const BULK_UUID = '5a12c1bd-a8b5-41d4-8c00-3f5bcaa6d3c8';

    /**
     * @var \Magento\Catalog\Model\Attribute\Backend\Consumer
     */
    private $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $publisherMock;

    /**
     * @var BulkManagement
     */
    private $bulkManagement;

    /**
     * @var BulkStatus
     */
    private $bulkStatus;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->publisherMock = $this->getMockForAbstractClass(BulkPublisherInterface::class);

        $this->bulkManagement = $this->objectManager->create(
            BulkManagement::class,
            [
                'publisher' => $this->publisherMock
            ]
        );
        $this->bulkStatus = $this->objectManager->get(BulkStatus::class);
        $catalogProductMock = $this->createMock(\Magento\Catalog\Helper\Product::class);
        $productFlatIndexerProcessorMock = $this->createMock(
            \Magento\Catalog\Model\Indexer\Product\Flat\Processor::class
        );
        $productPriceIndexerProcessorMock = $this->createMock(
            \Magento\Catalog\Model\Indexer\Product\Price\Processor::class
        );
        $operationManagementMock = $this->createMock(
            \Magento\Framework\Bulk\OperationManagementInterface::class
        );
        $actionMock = $this->createMock(\Magento\Catalog\Model\Product\Action::class);
        $loggerMock = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->serializer = $this->objectManager->get(\Magento\Framework\Serialize\SerializerInterface::class);
        $entityManager = $this->objectManager->get(\Magento\Framework\EntityManager\EntityManager::class);
        $this->model = $this->objectManager->create(
            Consumer::class,
            [
                'catalogProduct' => $catalogProductMock,
                'productFlatIndexerProcessor' => $productFlatIndexerProcessorMock,
                'productPriceIndexerProcessor' => $productPriceIndexerProcessorMock,
                'operationManagement' => $operationManagementMock,
                'action' => $actionMock,
                'logger' => $loggerMock,
                'serializer' => $this->serializer,
                'entityManager' => $entityManager
            ]
        );

        parent::setUp();
    }

    /**
     * Testing saving bulk operation during processing operation by attribute backend consumer
     */
    public function testSaveOperationDuringProcess()
    {
        $operation = $this->prepareUpdateAttributesBulkAndOperation();
        try {
            $this->model->process($operation);
        } catch (\Exception $e) {
            $this->fail(sprintf('Operation save process failed.: %s', $e->getMessage()));
        }
        $operationStatus = $operation->getStatus();
        $this->assertEquals(
            1,
            $this->bulkStatus->getOperationsCountByBulkIdAndStatus(self::BULK_UUID, $operationStatus)
        );
    }

    /**
     * Schedules test bulk and returns operation
     * @return OperationInterface
     */
    private function prepareUpdateAttributesBulkAndOperation(): OperationInterface
    {
        // general bulk information
        $bulkUuid = self::BULK_UUID;
        $bulkDescription = 'Update attributes for 2 selected products';
        $topicName = 'product_action_attribute.update';
        $userId = 1;
        /** @var OperationInterfaceFactory $operationFactory */
        $operationFactory = $this->objectManager->get(OperationInterfaceFactory::class);
        $operation = $operationFactory->create();
        $operation->setBulkUuid($bulkUuid)
            ->setTopicName($topicName)
            ->setSerializedData($this->serializer->serialize(
                ['product_ids' => [1,3], 'attributes' => [], 'store_id' => '0']
            ));
        $this->bulkManagement->scheduleBulk($bulkUuid, [$operation], $bulkDescription, $userId);
        return $operation;
    }

    /**
     * Clear created bulk and operation
     */
    protected function tearDown(): void
    {
        $this->bulkManagement->deleteBulk(self::BULK_UUID);
        parent::tearDown();
    }
}
