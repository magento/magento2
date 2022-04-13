<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAdobeIms\Test\Unit\Model;

use Magento\AdminAdobeIms\Model\ResourceModel\ImsWebapi as ImsWebapiResource;
use Magento\AdminAdobeIms\Model\ResourceModel\ImsWebapi\CollectionFactory;
use Magento\AdminAdobeIms\Model\ResourceModel\ImsWebapi\Collection;
use Magento\AdminAdobeIms\Model\ImsWebapi;
use Magento\AdminAdobeIms\Model\ImsWebapiRepository;
use Magento\AdminAdobeIms\Api\Data\ImsWebapiInterfaceFactory;
use Magento\AdminAdobeIms\Api\Data\ImsWebapiSearchResultsInterfaceFactory;
use Magento\AdminAdobeIms\Api\Data\ImsWebapiSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Ims Webapi repository test. Test all repository functions.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ImsWebapiRepositoryTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ImsWebapiRepository $model
     */
    private $model;

    /**
     * @var ImsWebapiResource|MockObject $resource
     */
    private $resource;

    /**
     * @var ImsWebapiInterfaceFactory|MockObject $entityFactory
     */
    private $entityFactory;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var CollectionFactory|MockObject
     */
    private $entityCollectionFactory;

    /**
     * @var CollectionProcessorInterface|MockObject
     */
    private $collectionProcessor;

    /**
     * @var ImsWebapiSearchResultsInterfaceFactory|MockObject
     */
    private $searchResultsFactory;

    /**
     * @var SearchCriteriaBuilder|MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * Prepare test objects.
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->resource = $this->createMock(ImsWebapiResource::class);
        $this->entityFactory = $this->createMock(ImsWebapiInterfaceFactory::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->entityCollectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->collectionProcessor = $this->createMock(CollectionProcessorInterface::class);
        $this->searchResultsFactory = $this->createPartialMock(
            ImsWebapiSearchResultsInterfaceFactory::class,
            ['create']
        );
        $this->searchCriteriaBuilder = $this->createPartialMock(
            SearchCriteriaBuilder::class,
            ['create', 'addFilter']
        );

        $this->model = new ImsWebapiRepository(
            $this->resource,
            $this->entityFactory,
            $this->loggerMock,
            $this->entityCollectionFactory,
            $this->collectionProcessor,
            $this->searchResultsFactory,
            $this->searchCriteriaBuilder
        );
    }

    /**
     * Test saving
     *
     * @return void
     * @throws CouldNotSaveException
     */
    public function testSave(): void
    {
        $imsWebapi = $this->objectManager->getObject(ImsWebapi::class);
        $this->resource->expects($this->once())
            ->method('save')
            ->with($imsWebapi);
        $this->model->save($imsWebapi);
    }

    /**
     * Test save with exception.
     *
     * @return void
     */
    public function testSaveWithException(): void
    {
        $this->expectException(CouldNotSaveException::class);
        $this->expectExceptionMessage('Could not save ims token.');

        $imsWebapi = $this->createMock(ImsWebapi::class);
        $this->resource->expects($this->once())
            ->method('save')
            ->with($imsWebapi)
            ->willThrowException(
                new CouldNotSaveException(__('Could not save ims token.'))
            );
        $this->loggerMock->expects($this->once())->method('critical');
        $this->model->save($imsWebapi);
    }

    /**
     * Test get  id.
     */
    public function testGet(): void
    {
        $entity = $this->objectManager->getObject(ImsWebapi::class)->setId(1);
        $this->entityFactory->method('create')
            ->willReturn($entity);
        $this->assertEquals($this->model->get(1)->getId(), 1);
    }

    /**
     * Test get ims web API id with exception.
     *
     * @return void
     */
    public function testGetWithException(): void
    {
        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage('The ims token wasn\'t found.');

        $entity = $this->objectManager->getObject(ImsWebapi::class);
        $this->entityFactory->method('create')
            ->willReturn($entity);
        $this->resource->expects($this->once())
            ->method('load')
            ->willThrowException(
                new NoSuchEntityException(__('The ims token wasn\'t found.'))
            );
        $this->model->get(1);
    }

    /**
     * Initializing collection of ims webapi
     *
     * @return array
     */
    protected function initCollection(): array
    {
        $collectionSize = 1;
        $searchCriteriaMock = $this->getMockBuilder(SearchCriteriaInterface::class)
            ->setMethods(['getPageSize'])
            ->getMockForAbstractClass();

        $searchCriteriaMock->expects($this->any())
            ->method('getPageSize')
            ->willReturn($collectionSize);

        $this->searchCriteriaBuilder->expects($this->any())
            ->method('create')
            ->willReturn($searchCriteriaMock);
        $this->searchCriteriaBuilder->expects($this->any())
            ->method('addFilter')
            ->willReturnSelf();

        $collection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $imsWebapiMock = $this->createMock(ImsWebapi::class);

        $collection->expects($this->once())
            ->method('getItems')
            ->willReturn([$imsWebapiMock]);

        $this->entityCollectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($collection);

        $collection->expects($this->once())
            ->method('getSize')
            ->willReturn($collectionSize);

        $this->collectionProcessor->expects($this->once())
            ->method('process')
            ->with($searchCriteriaMock, $collection)
            ->willReturnSelf();
        $searchResultsMock = $this->createSearchResultsMock($searchCriteriaMock, $imsWebapiMock, $collectionSize);

        $searchResultsMock->expects($this->any())
            ->method('getItems')
            ->willReturn([$imsWebapiMock]);

        $this->searchResultsFactory->expects($this->once())
            ->method('create')
            ->willReturn($searchResultsMock);

        return [
            'imsWebapiMock' => [$imsWebapiMock],
            'searchCriteriaMock' => $searchCriteriaMock,
            'searchResultsMock' => $searchResultsMock
        ];
    }

    /**
     * Test get by ims webapi id.
     *
     * @return void
     * @throws NoSuchEntityException
     */
    public function testGetByAdminUserId(): void
    {
        $collectionInfo = $this->initCollection();
        $this->assertEquals($collectionInfo['imsWebapiMock'], $this->model->getByAdminUserId(1));
    }

    /**
     * Test get list
     *
     * @return void
     * @throws NoSuchEntityException
     */
    public function testGetList(): void
    {
        $collectionInfo = $this->initCollection();

        $this->assertEquals(
            $collectionInfo['searchResultsMock'],
            $this->model->getList($collectionInfo['searchCriteriaMock'])
        );
    }

    /**
     * Creating mock for the search results object
     *
     * @param MockObject $searchCriteriaMock
     * @param MockObject $imsWebapiMock
     * @param int $collectionSize
     * @return MockObject
     */
    protected function createSearchResultsMock($searchCriteriaMock, $imsWebapiMock, $collectionSize = 1): MockObject
    {
        /** @var MockObject $searchResultsMock */
        $searchResultsMock = $this->getMockBuilder(ImsWebapiSearchResultsInterface::class)
            ->getMockForAbstractClass();

        $searchResultsMock->expects($this->once())
            ->method('setSearchCriteria')
            ->with($searchCriteriaMock);
        $searchResultsMock->expects($this->any())
            ->method('setItems')
            ->with([$imsWebapiMock]);
        $searchResultsMock->expects($this->any())
            ->method('setTotalCount')
            ->with($collectionSize);

        return $searchResultsMock;
    }

    /**
     * Test successful deletion of ims web API
     *
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function testDeleteByAdminUserId(): void
    {
        $adminUserId = 1;

        $collectionInfo = $this->initCollection();

        $this->resource->expects($this->exactly(1))
            ->method('delete')
            ->with($collectionInfo['imsWebapiMock'][0])
            ->willReturnSelf();

        $this->assertTrue($this->model->deleteByAdminUserId($adminUserId));
    }

    /**
     * Test non-successful deletion of ims webapi
     *
     * @return void
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function testDeleteWithException(): void
    {
        $adminUserId = 1;
        $message = 'Could not delete ims tokens for admin user id %d.';
        $this->expectException(CouldNotDeleteException::class);
        $this->expectExceptionMessage(sprintf($message, $adminUserId));
        $collectionInfo = $this->initCollection();

        $this->resource->expects($this->exactly(1))
            ->method('delete')
            ->with($collectionInfo['imsWebapiMock'][0])
            ->willThrowException(
                new CouldNotDeleteException(__(
                    $message,
                    $adminUserId
                ))
            );

        $this->model->deleteByAdminUserId($adminUserId);
    }
}
