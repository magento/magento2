<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Test\Unit\Model;

use Magento\InventoryApi\Api\Data\SourceInterface;

/**
 * Class SourceRepositoryTest
 */
class SourceRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Inventory\Model\Resource\Source|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resource;

    /**
     * @var \Magento\Inventory\Model\SourceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sourceFactory;

    /**
     * @var \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionProcessor;

    /**
     * @var \Magento\Inventory\Model\Resource\Source\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionFactory;

    /**
     * @var \Magento\Inventory\Model\SourceSearchResultsFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sourceSearchResultsFactory;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * @var \Magento\Inventory\Model\SourceRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sourceRepository;

    protected function setUp()
    {
        $this->resource = $this->getMock(
            \Magento\Inventory\Model\Resource\Source::class,
            [],
            [],
            '',
            false
        );

        $this->sourceFactory = $this->getMock(
            \Magento\InventoryApi\Api\Data\SourceInterfaceFactory::class,
            ['create'],
            [],
            '',
            false
        );

        $this->collectionProcessor = $this->getMockBuilder(
            \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface::class
        )->getMockForAbstractClass();

        $this->collectionFactory = $this->getMock(
            \Magento\Inventory\Model\Resource\Source\CollectionFactory::class,
            ['create'],
            [],
            '',
            false
        );

        $this->sourceSearchResultsFactory = $this->getMock(
            \Magento\Inventory\Model\SourceSearchResultsFactory::class,
            ['create'],
            [],
            '',
            false
        );

        $this->loggerMock = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->sourceRepository = $objectManager->getObject(
            \Magento\Inventory\Model\SourceRepository::class,
            [
                'resource' => $this->resource,
                'sourceFactory' => $this->sourceFactory,
                'collectionProcessor' => $this->collectionProcessor,
                'collectionFactory' => $this->collectionFactory,
                'sourceSearchResultsFactory' => $this->sourceSearchResultsFactory,
                'logger' => $this->loggerMock,
            ]
        );
    }

    public function testSaveSuccessful()
    {
        /** @var \Magento\Inventory\Model\Source|\PHPUnit_Framework_MockObject_MockObject $sourceModel */
        $sourceModel = $this->getMock(
            \Magento\Inventory\Model\Source::class,
            [],
            [],
            '',
            false
        );

        $result = $this->sourceRepository->save($sourceModel);
        $this->assertNotNull($result);
    }

    public function testSaveErrorExpectsException()
    {
        /** @var \Magento\Inventory\Model\Source|\PHPUnit_Framework_MockObject_MockObject $sourceModel */
        $sourceModel = $this->getMock(
            \Magento\Inventory\Model\Source::class,
            [],
            [],
            '',
            false
        );

        $this->resource->expects($this->atLeastOnce())
            ->method('save');

        $this->setExpectedException(\Magento\Framework\Exception\CouldNotSaveException::class);

        $this->resource->expects($this->atLeastOnce())
            ->method('save')
            ->will($this->throwException(new \Exception('Some unit test Exception')));

        $this->sourceRepository->save($sourceModel);
    }

    public function testGetSuccessful()
    {
        $sourceId = 345;

        /** @var \Magento\Inventory\Model\Source|\PHPUnit_Framework_MockObject_MockObject $sourceModel */
        $sourceModel = $this->getMock(
            \Magento\Inventory\Model\Source::class,
            [],
            [],
            '',
            false
        );

        $sourceModel->expects($this->atLeastOnce())
            ->method('getSourceId')
            ->will($this->returnValue($sourceId));

        $this->sourceFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($sourceModel));

        $this->resource->expects($this->once())
            ->method('load')
            ->with(
                $sourceModel,
                SourceInterface::SOURCE_ID,
                $sourceId
            );

        $result = $this->sourceRepository->get($sourceId);

        $this->assertSame($sourceModel, $result);
    }

    public function testGetErrorExpectsException()
    {
        $sourceId = 345;

        /** @var \Magento\Inventory\Model\Source|\PHPUnit_Framework_MockObject_MockObject $sourceModel */
        $sourceModel = $this->getMock(
            \Magento\Inventory\Model\Source::class,
            [],
            [],
            '',
            false
        );

        $sourceModel->expects($this->atLeastOnce())
            ->method('getSourceId')
            ->will($this->returnValue(null));

        $this->sourceFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($sourceModel));

        $this->resource->expects($this->once())
            ->method('load')
            ->with(
                $sourceModel,
                SourceInterface::SOURCE_ID,
                $sourceId
            );

        $this->setExpectedException(\Magento\Framework\Exception\NoSuchEntityException::class);

        $this->sourceRepository->get($sourceId);
    }

    public function testGetList()
    {
        /** @var \Magento\Inventory\Model\Source|\PHPUnit_Framework_MockObject_MockObject $sourceModel1 */
        $sourceModel1 = $this->getMock(
            \Magento\Inventory\Model\Source::class,
            [],
            [],
            '',
            false
        );

        /** @var \Magento\Inventory\Model\Source|\PHPUnit_Framework_MockObject_MockObject $sourceModel2 */
        $sourceModel2 = $this->getMock(
            \Magento\Inventory\Model\Source::class,
            [],
            [],
            '',
            false
        );

        $searchCriteria = $this->getMock(
            \Magento\Framework\Api\SearchCriteriaInterface::class,
            [],
            [],
            '',
            false
        );

        $collection = $this->getMock(
            \Magento\Inventory\Model\Resource\Source\Collection::class,
            [],
            [],
            '',
            false
        );

        $this->collectionFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($collection));

        $sources = [
            $sourceModel1,
            $sourceModel2
        ];

        $collection->expects($this->atLeastOnce())
            ->method('getItems')
            ->will($this->returnValue($sources));

        $searchResults = $this->getMock(
            \Magento\Inventory\Model\SourceSearchResults::class,
            [],
            [],
            '',
            false
        );

        $this->sourceSearchResultsFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($searchResults));

        $searchResults->expects($this->once())
            ->method('setItems')
            ->with($sources);

        $result = $this->sourceRepository->getList($searchCriteria);

        $this->assertSame($searchResults, $result);
    }
}
