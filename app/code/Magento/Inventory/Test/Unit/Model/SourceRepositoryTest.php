<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Test\Unit\Model;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\Data\SourceInterfaceFactory;
use Magento\InventoryApi\Api\Data\SourceSearchResultsInterfaceFactory;
use Magento\InventoryApi\Api\Data\SourceSearchResultsInterface;
use Magento\Inventory\Model\Source;
use Magento\Inventory\Model\ResourceModel\Source as SourceResource;
use Magento\Inventory\Model\ResourceModel\Source\CollectionFactory as SourceCollectionFactory;
use Magento\Inventory\Model\ResourceModel\Source\Collection as SourceCollection;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SourceRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SourceResource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceSource;

    /**
     * @var SourceInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sourceFactory;

    /**
     * @var CollectionProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionProcessor;

    /**
     * @var SourceCollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sourceCollectionFactory;

    /**
     * @var SourceSearchResultsInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sourceSearchResultsFactory;

    /**
     * @var SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * @var Source|\PHPUnit_Framework_MockObject_MockObject
     */
    private $source;

    /**
     * @var \Magento\Inventory\Model\SourceRepository
     */
    private $model;

    protected function setUp()
    {
        $this->resourceSource = $this->getMockBuilder(SourceResource::class)->disableOriginalConstructor()->getMock();
        $this->searchCriteriaBuilder = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sourceFactory = $this->getMockBuilder(SourceInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->collectionProcessor = $this->getMockBuilder(CollectionProcessorInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['process'])
            ->getMock();
        $this->sourceCollectionFactory = $this->getMockBuilder(SourceCollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->sourceSearchResultsFactory = $this->getMockBuilder(SourceSearchResultsInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->loggerMock = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->source = $this->getMockBuilder(Source::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\Inventory\Model\SourceRepository::class,
            [
                'resourceSource' => $this->resourceSource,
                'sourceFactory' => $this->sourceFactory,
                'collectionProcessor' => $this->collectionProcessor,
                'sourceCollectionFactory' => $this->sourceCollectionFactory,
                'sourceSearchResultsFactory' => $this->sourceSearchResultsFactory,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                'logger' => $this->loggerMock,
            ]
        );
    }

    public function testSave()
    {
        $sourceId = 42;

        $this->source
            ->expects($this->once())
            ->method('getSourceId')
            ->willReturn($sourceId);
        $this->resourceSource
            ->expects($this->once())
            ->method('save')
            ->with($this->source);

        self::assertEquals($sourceId, $this->model->save($this->source));
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testSaveErrorExpectsException()
    {
        $message = 'some message';

        $this->resourceSource
            ->expects($this->once())
            ->method('save')
            ->willThrowException(new \Exception($message));

        $this->loggerMock
            ->expects($this->once())
            ->method('error')
            ->with($message);

        $this->model->save($this->source);
    }

    public function testGet()
    {
        $sourceId = 345;

        $this->source
            ->expects($this->once())
            ->method('getSourceId')
            ->willReturn($sourceId);
        $this->sourceFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->source);
        $this->resourceSource
            ->expects($this->once())
            ->method('load')
            ->with($this->source, $sourceId, SourceInterface::SOURCE_ID);

        self::assertSame($this->source, $this->model->get($sourceId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testGetErrorExpectsException()
    {
        $sourceId = 345;

        $this->source
            ->expects($this->once())
            ->method('getSourceId')
            ->willReturn(null);
        $this->sourceFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->source);
        $this->resourceSource->expects($this->once())
            ->method('load')
            ->with(
                $this->source,
                $sourceId,
                SourceInterface::SOURCE_ID
            );

        $this->model->get($sourceId);
    }

    public function testGetListWithSearchCriteria()
    {
        $items = [
            $this->getMockBuilder(Source::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(Source::class)->disableOriginalConstructor()->getMock()
        ];
        $totalCount = 2;
        $searchCriteria = $this->getMockBuilder(SearchCriteriaInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $sourceCollection = $this->getMockBuilder(SourceCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $sourceCollection
            ->expects($this->once())
            ->method('getItems')
            ->willReturn($items);
        $sourceCollection
            ->expects($this->once())
            ->method('getSize')
            ->willReturn($totalCount);
        $this->sourceCollectionFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($sourceCollection);

        $searchResults = $this->getMockBuilder(SourceSearchResultsInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $searchResults
            ->expects($this->once())
            ->method('setItems')
            ->with($items);
        $searchResults
            ->expects($this->once())
            ->method('setTotalCount')
            ->with($totalCount);
        $searchResults
            ->expects($this->once())
            ->method('setSearchCriteria')
            ->with($searchCriteria);
        $this->sourceSearchResultsFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($searchResults);

        $this->collectionProcessor
            ->expects($this->once())
            ->method('process')
            ->with($searchCriteria, $sourceCollection);

        self::assertSame($searchResults, $this->model->getList($searchCriteria));
    }

    public function testGetListWithoutSearchCriteria()
    {
        $items = [
            $this->getMockBuilder(Source::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(Source::class)->disableOriginalConstructor()->getMock()
        ];
        $totalCount = 2;

        $searchCriteria = $this->getMockBuilder(SearchCriteriaInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilder
            ->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteria);

        $sourceCollection = $this->getMockBuilder(SourceCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $sourceCollection
            ->expects($this->once())
            ->method('getItems')
            ->willReturn($items);
        $sourceCollection
            ->expects($this->once())
            ->method('getSize')
            ->willReturn($totalCount);
        $this->sourceCollectionFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($sourceCollection);

        $searchResults = $this->getMockBuilder(SourceSearchResultsInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $searchResults
            ->expects($this->once())
            ->method('setItems')
            ->with($items);
        $searchResults
            ->expects($this->once())
            ->method('setTotalCount')
            ->with($totalCount);
        $searchResults
            ->expects($this->once())
            ->method('setSearchCriteria')
            ->with($searchCriteria);
        $this->sourceSearchResultsFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($searchResults);

        $this->collectionProcessor
            ->expects($this->never())
            ->method('process');

        self::assertSame($searchResults, $this->model->getList());
    }
}
