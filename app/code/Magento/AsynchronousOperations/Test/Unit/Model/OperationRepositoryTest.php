<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AsynchronousOperations\Test\Unit\Model;

use Magento\AsynchronousOperations\Api\Data\OperationExtensionInterfaceFactory;
use Magento\AsynchronousOperations\Model\OperationRepository;
use Magento\AsynchronousOperations\Model\ResourceModel\Operation\Collection as OperationCollection;
use Magento\AsynchronousOperations\Model\ResourceModel\Operation\CollectionFactory as OperationCollectionFactory;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\EntityManager\EntityManager;
use Magento\AsynchronousOperations\Api\Data\OperationSearchResultsInterfaceFactory as SearchResultFactory;
use Magento\AsynchronousOperations\Api\Data\OperationSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Magento\AsynchronousOperations\Model\OperationRepository class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OperationRepositoryTest extends TestCase
{
    /**
     * @var OperationRepository
     */
    private $model;

    /**
     * @var EntityManager|MockObject
     */
    private $entityManager;

    /**
     * @var OperationCollectionFactory|MockObject
     */
    private $operationCollectionFactory;

    /**
     * @var SearchResultFactory|MockObject
     */
    private $searchResultFactory;

    /**
     * @var JoinProcessorInterface|MockObject
     */
    private $joinProcessor;

    /**
     * @var OperationExtensionInterfaceFactory|MockObject
     */
    private $operationExtensionFactory;

    /**
     * @var CollectionProcessorInterface|MockObject
     */
    private $collectionProcessor;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var array of items
     */
    private $items = [
        "items"=> [
            [
                "extension_attributes" => [
                    "start_time" => "2022-05-06 05:48:04"
                ],
                "id" => 1,
                "bulk_uuid" => "89300764-2502-44c6-a377-70b9565c34b8",
                "topic_name" => "async.magento.customer.api.accountmanagementinterface.createaccount.post",
                "serialized_data" => "{}",
                "result_serialized_data" => null,
                "status"=> 4,
                "result_message" => null,
                "error_code" => null
            ],
            [
                "extension_attributes" => [
                    "start_time" => "2022-05-06 05:48:04"
                ],
                "id" => 2,
                "bulk_uuid" => "89300764-2502-44c6-a377-70b9565c34b8",
                "topic_name" => "async.magento.customer.api.accountmanagementinterface.createaccount.post",
                "serialized_data" => "{}",
                "result_serialized_data" => null,
                "status" => 4,
                "result_message" => null,
                "error_code" => null
            ],
            [
                "extension_attributes"=> [
                "start_time"=> "2022-05-06 05:48:04"
                ],
                "id" => 3,
                "bulk_uuid" => "89300764-2502-44c6-a377-70b9565c34b8",
                "topic_name" => "async.magento.customer.api.accountmanagementinterface.createaccount.post",
                "serialized_data" => "{}",
                "result_serialized_data" => null,
                "status" => 4,
                "result_message" => null,
                "error_code" => null
            ],
            [
                "extension_attributes" => [
                    "start_time" => "2022-05-06 05:48:04"
                ],
                "id" => 4,
                "bulk_uuid" => "89300764-2502-44c6-a377-70b9565c34b8",
                "topic_name" => "async.magento.customer.api.accountmanagementinterface.createaccount.post",
                "serialized_data" => "{}",
                "result_serialized_data" => null,
                "status" => 4,
                "result_message" => null,
                "error_code" => null
            ],
        ],
    ];

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManager::class);
        $this->operationCollectionFactory = $this->createPartialMock(OperationCollectionFactory::class, ['create']);
        $this->searchResultFactory = $this->createMock(SearchResultFactory::class);
        $this->joinProcessor = $this->createMock(JoinProcessorInterface::class);
        $this->operationExtensionFactory = $this->createMock(OperationExtensionInterfaceFactory::class);
        $this->collectionProcessor = $this->createMock(CollectionProcessorInterface::class);
        $this->logger = $this->createMock(\Psr\Log\LoggerInterface::class);

        $this->model = new OperationRepository(
            $this->entityManager,
            $this->operationCollectionFactory,
            $this->searchResultFactory,
            $this->joinProcessor,
            $this->operationExtensionFactory,
            $this->collectionProcessor,
            $this->logger
        );
    }

    /**
     * Check for Get List methods if its calling addFieldToSelect method
     *
     * @return void
     */
    public function testGetListSelect(): void
    {
        $searchResultInterface = $this->createMock(OperationSearchResultsInterface::class);
        $searchResultInterface->expects($this->once())->method('setSearchCriteria')->willReturnSelf();
        $searchResultInterface->expects($this->once())->method('setTotalCount')->willReturnSelf();
        $searchResultInterface->expects($this->once())->method('setItems')->willReturnSelf();

        $this->joinProcessor->expects($this->once())->method('process');
        $this->collectionProcessor->expects($this->once())->method('process');

        $searchCriteria = $this->createMock(SearchCriteriaInterface::class);
        $this->searchResultFactory->expects($this->once())->method('create')->willReturn($searchResultInterface);
        $operationCollection = $this->createMock(OperationCollection::class);

        $operationCollection->expects($this->once())->method('getItems')->willReturn($this->items);
        $operationCollection->expects($this->once())->method('getSize')->willReturn(count($this->items));
        $operationCollection->expects($this->exactly(3))->method('addFieldToSelect')->willReturnSelf();
        $this->operationCollectionFactory->expects($this->once())->method('create')->willReturn($operationCollection);
        $this->model->getList($searchCriteria);
    }
}
