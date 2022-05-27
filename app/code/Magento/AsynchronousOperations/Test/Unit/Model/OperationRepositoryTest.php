<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AsynchronousOperations\Test\Unit\Model;

use Magento\AsynchronousOperations\Api\Data\OperationExtensionInterfaceFactory;
use Magento\AsynchronousOperations\Model\BulkStatus;
use Magento\AsynchronousOperations\Model\OperationRepository;
use Magento\AsynchronousOperations\Model\ResourceModel\Operation\Collection as OperationCollection;
use Magento\AsynchronousOperations\Model\ResourceModel\Operation\CollectionFactory as OperationCollectionFactory;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\EntityManager\EntityManager;
use Magento\AsynchronousOperations\Api\Data\OperationSearchResultsInterfaceFactory as SearchResultFactory;
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
    private OperationRepository $model;

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
     * @param int|null $failureType
     * @param array $failureCodes
     *
     * @return void
     * @dataProvider getFailedOperationsByBulkIdDataProvider
     */
    public function testGetFailedOperationsByBulkId(?int $failureType, array $failureCodes): void
    {
        $bulkUuid = 'bulk-1';
        $operationCollection = $this->createMock(OperationCollection::class);
        $this->operationCollectionFactory->expects($this->once())->method('create')->willReturn($operationCollection);
        $operationCollection
            ->method('addFieldToFilter')
            ->withConsecutive(['bulk_uuid', $bulkUuid], ['status', $failureCodes])
            ->willReturnOnConsecutiveCalls($operationCollection, $operationCollection);
        $operationCollection->expects($this->once())->method('getItems')->willReturn([$this->operationMock]);
        $this->assertEquals([$this->operationMock], $this->model->getFailedOperationsByBulkId($bulkUuid, $failureType));
    }
}
