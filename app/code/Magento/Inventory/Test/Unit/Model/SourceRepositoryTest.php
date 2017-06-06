<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Test\Unit\Model;

use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\Data\SourceInterfaceFactory;
use Magento\InventoryApi\Api\Data\SourceSearchResultsInterfaceFactory;
use Magento\InventoryApi\Api\Data\SourceSearchResultsInterface;
use Magento\Inventory\Model\Source;
use Magento\Inventory\Model\ResourceModel\Source as SourceResource;
use Magento\Inventory\Model\ResourceModel\Source\CollectionFactory as SourceCollectionFactory;
use Magento\Inventory\Model\ResourceModel\Source\Collection as SourceCollection;
use Magento\Inventory\Model\SourceCarrierLink;
use Magento\Inventory\Model\ResourceModel\SourceCarrierLink as ResourceSourceCarrierLink;
use Magento\Inventory\Model\ResourceModel\SourceCarrierLink\CollectionFactory as CarrierLinkCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Inventory\Model\ResourceModel\SourceCarrierLink\Collection as CarrierLinkCollection;

/**
 * Class SourceRepositoryTest
 */
class SourceRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SourceResource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sourceResource;

    /**
     * @var ResourceSourceCarrierLink|\PHPUnit_Framework_MockObject_MockObject
     */
    private $carrierLinkResource;

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
    private $collectionFactory;

    /**
     * @var SourceSearchResultsInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sourceSearchResultsFactory;

    /**
     * @var CarrierLinkCollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $carrierLinkCollectionFactory;

    /**
     * @var SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * @var \Magento\Inventory\Model\SourceRepository
     */
    private $model;

    protected function setUp()
    {
        $this->sourceResource = $this->getMockBuilder(SourceResource::class)->disableOriginalConstructor()->getMock();
        $this->carrierLinkResource = $this->getMockBuilder(ResourceSourceCarrierLink::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->carrierLinkCollectionFactory = $this->getMockBuilder(CarrierLinkCollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
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
        $this->collectionFactory = $this->getMockBuilder(SourceCollectionFactory::class)
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

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\Inventory\Model\SourceRepository::class,
            [
                'resourceSource' => $this->sourceResource,
                'resourceSourceCarrierLink' => $this->carrierLinkResource,
                'sourceFactory' => $this->sourceFactory,
                'collectionProcessor' => $this->collectionProcessor,
                'collectionFactory' => $this->collectionFactory,
                'carrierLinkCollectionFactory' => $this->carrierLinkCollectionFactory,
                'sourceSearchResultsFactory' => $this->sourceSearchResultsFactory,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                'logger' => $this->loggerMock,
            ]
        );
    }

    public function testSaveSuccessful()
    {
        $sourceId = 42;
        /** @var \Magento\Inventory\Model\Source|\PHPUnit_Framework_MockObject_MockObject $sourceMock */
        $sourceMock = $this->getMockBuilder(\Magento\Inventory\Model\Source::class)
            ->disableOriginalConstructor()
            ->getMock();
        $carrierLinkMock = $this->getMockBuilder(SourceCarrierLink::class)
            ->disableOriginalConstructor()
            ->getMock();

        $sourceMock->expects($this->atLeastOnce())->method('getSourceId')->willReturn($sourceId);
        $sourceMock->expects($this->atLeastOnce())->method('getCarrierLinks')->willReturn([$carrierLinkMock]);

        $this->sourceResource->expects($this->once())->method('save')->with($sourceMock);
        $this->carrierLinkResource->expects($this->once())->method('save')->with($carrierLinkMock);

        $this->assertEquals($sourceId, $this->model->save($sourceMock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testSaveErrorExpectsException()
    {
        /** @var Source|\PHPUnit_Framework_MockObject_MockObject $sourceModel */
        $sourceModel = $this->getMockBuilder(Source::class)->disableOriginalConstructor()->getMock();
        $this->sourceResource->expects($this->atLeastOnce())->method('save');
        $this->sourceResource->expects($this->atLeastOnce())
            ->method('save')
            ->will($this->throwException(new \Exception('Some unit test Exception')));

        $this->model->save($sourceModel);
    }

    public function testGetSuccessful()
    {
        $sourceId = 345;

        /** @var Source|\PHPUnit_Framework_MockObject_MockObject $sourceMock */
        $sourceMock = $this->getMockBuilder(Source::class)->disableOriginalConstructor()->getMock();
        $searchCriteriaMock = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $carrierLinkCollectionMock = $this->getMockBuilder(
            \Magento\Inventory\Model\ResourceModel\SourceCarrierLink\Collection::class
        )->disableOriginalConstructor()->getMock();

        $this->carrierLinkCollectionFactory->expects($this->atLeastOnce())->method('create')
            ->willReturn($carrierLinkCollectionMock);
        $this->searchCriteriaBuilder->expects($this->atLeastOnce())->method('addFilter')->willReturnSelf();
        $this->searchCriteriaBuilder->expects($this->atLeastOnce())->method('create')->willReturn($searchCriteriaMock);

        $carrierLinkCollectionMock->expects($this->atLeastOnce())->method('getItems')->willReturn([]);
        $sourceMock->expects($this->atLeastOnce())->method('setCarrierLinks')->with([]);

        $sourceMock->expects($this->atLeastOnce())
            ->method('getSourceId')
            ->will($this->returnValue($sourceId));
        $this->sourceFactory->expects($this->once())->method('create')->willReturn($sourceMock);
        $this->sourceResource->expects($this->once())
            ->method('load')
            ->with(
                $sourceMock,
                $sourceId,
                SourceInterface::SOURCE_ID
            );


        $this->assertSame($sourceMock, $this->model->get($sourceId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testGetErrorExpectsException()
    {
        $sourceId = 345;

        /** @var Source|\PHPUnit_Framework_MockObject_MockObject $sourceModel */
        $sourceModel = $this->getMockBuilder(Source::class)->disableOriginalConstructor()->getMock();
        $searchCriteriaMock = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $carrierLinkCollectionMock = $this->getMockBuilder(
            \Magento\Inventory\Model\ResourceModel\SourceCarrierLink\Collection::class
        )->disableOriginalConstructor()->getMock();

        $this->carrierLinkCollectionFactory->expects($this->atLeastOnce())->method('create')
            ->willReturn($carrierLinkCollectionMock);
        $this->searchCriteriaBuilder->expects($this->atLeastOnce())->method('addFilter')->willReturnSelf();
        $this->searchCriteriaBuilder->expects($this->atLeastOnce())->method('create')->willReturn($searchCriteriaMock);

        $sourceModel->expects($this->atLeastOnce())->method('getSourceId')->willReturn(null);
        $this->sourceFactory->expects($this->once())->method('create')->willReturn($sourceModel);
        $this->sourceResource->expects($this->once())
            ->method('load')
            ->with(
                $sourceModel,
                $sourceId,
                SourceInterface::SOURCE_ID
            );

        $this->model->get($sourceId);
    }

    public function testGetList()
    {
        $sourceCollection = $this->getMockBuilder(SourceCollection::class)->disableOriginalConstructor()->getMock();
        $searchResults = $this->getMockBuilder(SourceSearchResultsInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $searchCriteria = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteriaInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $sources = [
            $this->getMockBuilder(Source::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(Source::class)->disableOriginalConstructor()->getMock()
        ];
        $carrierLinkCollectionMock = $this->getMockBuilder(CarrierLinkCollection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->collectionFactory->expects($this->once())->method('create')->willReturn($sourceCollection);
        $sourceCollection->expects($this->atLeastOnce())->method('getItems')->willReturn($sources);
        $this->sourceSearchResultsFactory->expects($this->once())->method('create')->willReturn($searchResults);
        $searchResults->expects($this->once())->method('setItems')->with($sources);

        $this->searchCriteriaBuilder->expects($this->atLeastOnce())->method('addFilter')->willReturnSelf();
        $this->searchCriteriaBuilder->expects($this->atLeastOnce())->method('create')->willReturn($searchCriteria);
        $this->carrierLinkCollectionFactory->expects($this->atLeastOnce())->method('create')
            ->willReturn($carrierLinkCollectionMock);

        $this->assertSame($searchResults, $this->model->getList($searchCriteria));
    }
}
