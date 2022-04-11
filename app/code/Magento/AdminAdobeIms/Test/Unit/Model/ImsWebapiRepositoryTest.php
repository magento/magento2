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
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;


/**
 * Ims Webapi repository test.
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

        $this->model = new ImsWebapiRepository(
            $this->resource,
            $this->entityFactory,
            $this->loggerMock,
            $this->entityCollectionFactory,
            $this->collectionProcessor,
            $this->searchResultsFactory
        );
    }

    /**
     * Test save.
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
     */
    public function testSaveWithException(): void
    {
        $this->expectException(CouldNotSaveException::class);
        $this->expectExceptionMessage('Could not save ims webapi.');

        $imsWebapi = $this->createMock(ImsWebapi::class);
        $this->resource->expects($this->once())
            ->method('save')
            ->with($imsWebapi)
            ->willThrowException(
                new CouldNotSaveException(__('Could not save ims webapi.'))
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
     */
    public function testGetWithException(): void
    {
        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage('The ims web API wasn\'t found.');

        $entity = $this->objectManager->getObject(ImsWebapi::class);
        $this->entityFactory->method('create')
            ->willReturn($entity);
        $this->resource->expects($this->once())
            ->method('load')
            ->willThrowException(
                new NoSuchEntityException(__('The web API wasn\'t found.'))
            );
        $this->model->get(1);
    }

    /**
     * Test get by ims webapi id.
     */
    public function testGetByUserId(): void
    {
        $entity = $this->objectManager->getObject(ImsWebapi::class)->setId(1);
        $this->entityFactory->method('create')
            ->willReturn($entity);
        $this->assertEquals($this->model->getByUserId(1)->getId(), 1);
    }

    /**
     * Test get list.
     */
    public function testGetList(): void
    {
        $collectionSize = 1;

        $imsWebapiMock = $this->createMock(ImsWebapi::class);

        $collection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $collection->expects($this->once())
            ->method('getSize')
            ->willReturn($collectionSize);

        $collection->expects($this->once())
            ->method('getItems')
            ->willReturn([$imsWebapiMock]);

        $this->entityCollectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($collection);

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteriaInterface::class)
            ->setMethods(['getPageSize'])
            ->getMockForAbstractClass();

        $searchCriteriaMock->expects($this->any())
            ->method('getPageSize')
            ->willReturn($collectionSize);

        $this->collectionProcessor->expects($this->once())
            ->method('process')
            ->with($searchCriteriaMock, $collection)
            ->willReturnSelf();
        $searchResultsMock = $this->createSearchResultsMock($searchCriteriaMock, $imsWebapiMock, $collectionSize);

        $this->searchResultsFactory->expects($this->once())
            ->method('create')
            ->willReturn($searchResultsMock);

        $this->assertEquals($searchResultsMock, $this->model->getList($searchCriteriaMock));
    }

    /**
     * @param MockObject $searchCriteriaMock
     * @param MockObject $imsWebapiMock
     * @param int $collectionSize
     * @return MockObject
     */
    protected function createSearchResultsMock($searchCriteriaMock, $imsWebapiMock, $collectionSize)
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
        $searchResultsMock->expects($this->once())
            ->method('setTotalCount')
            ->with($collectionSize);

        return $searchResultsMock;
    }

    /**
     * Test successful deletion of ims web API
     */
    public function testDeleteById()
    {
        $entityId = 1;

        $imsWebapiMock = $this->initImsWebapi($entityId);

        $this->resource->expects($this->exactly(1))
            ->method('delete')
            ->with($imsWebapiMock)
            ->willReturnSelf();

        $this->assertTrue($this->model->deleteById($entityId));
    }

    /**
     * Test non-successful deletion of ims webapi
     *
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function testDeleteWithException()
    {
        $entityId = 1;
        $message = 'Cannot delete ims webapi with id %1';
        $this->expectException('Magento\Framework\Exception\NoSuchEntityException');
        $this->expectExceptionMessage(sprintf($message, $entityId));
        $imsWebapi = $this->initImsWebapi($entityId);

        $this->resource->expects($this->once())
            ->method('delete')
            ->willThrowException(
                new NoSuchEntityException(__(
                    $message,
                    $entityId
                ))
            );
        $this->model->deleteById($entityId);
    }

    /**
     * @param int $entityId
     * @return ImsWebapi|MockObject
     */
    private function initImsWebapi($entityId)
    {
        $imsWebapiMock = $this->createMock(ImsWebapi::class);
        $this->entityFactory->method('create')
            ->willReturn($imsWebapiMock);

        $imsWebapiMock->expects($this->any())
            ->method('load')
            ->with($entityId)
            ->willReturn($imsWebapiMock);

        $imsWebapiMock->expects($this->any())
            ->method('getId')
            ->willReturn($entityId);
        return $imsWebapiMock;
    }
}
