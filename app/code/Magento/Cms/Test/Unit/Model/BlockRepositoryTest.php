<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Model;

use Magento\Cms\Model\BlockRepository;
use Magento\Framework\Api\SortOrder;

/**
 * Test for Magento\Cms\Model\BlockRepository
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BlockRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BlockRepository
     */
    protected $repository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Cms\Model\ResourceModel\Block
     */
    protected $blockResource;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Cms\Model\Block
     */
    protected $block;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Cms\Api\Data\BlockInterface
     */
    protected $blockData;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Cms\Api\Data\BlockSearchResultsInterface
     */
    protected $blockSearchResult;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Api\DataObjectHelper
     */
    protected $dataHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Reflection\DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Cms\Model\ResourceModel\Block\Collection
     */
    protected $collection;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * Initialize repository
     */
    protected function setUp()
    {
        $this->blockResource = $this->getMockBuilder(\Magento\Cms\Model\ResourceModel\Block::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataObjectProcessor = $this->getMockBuilder(\Magento\Framework\Reflection\DataObjectProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $blockFactory = $this->getMockBuilder(\Magento\Cms\Model\BlockFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $blockDataFactory = $this->getMockBuilder(\Magento\Cms\Api\Data\BlockInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $blockSearchResultFactory = $this->getMockBuilder(
            \Magento\Cms\Api\Data\BlockSearchResultsInterfaceFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $collectionFactory = $this->getMockBuilder(\Magento\Cms\Model\ResourceModel\Block\CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $store = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $store->expects($this->any())->method('getId')->willReturn(0);
        $this->storeManager->expects($this->any())->method('getStore')->willReturn($store);

        $this->block = $this->getMockBuilder(\Magento\Cms\Model\Block::class)->disableOriginalConstructor()->getMock();
        $this->blockData = $this->getMockBuilder(\Magento\Cms\Api\Data\BlockInterface::class)
            ->getMock();
        $this->blockSearchResult = $this->getMockBuilder(\Magento\Cms\Api\Data\BlockSearchResultsInterface::class)
            ->getMock();
        $this->collection = $this->getMockBuilder(\Magento\Cms\Model\ResourceModel\Block\Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['addFieldToFilter', 'getSize', 'setCurPage', 'setPageSize', 'load', 'addOrder'])
            ->getMock();

        $blockFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->block);
        $blockDataFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->blockData);
        $blockSearchResultFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->blockSearchResult);
        $collectionFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->collection);
        /**
         * @var \Magento\Cms\Model\BlockFactory $blockFactory
         * @var \Magento\Cms\Api\Data\BlockInterfaceFactory $blockDataFactory
         * @var \Magento\Cms\Api\Data\BlockSearchResultsInterfaceFactory $blockSearchResultFactory
         * @var \Magento\Cms\Model\ResourceModel\Block\CollectionFactory $collectionFactory
         */

        $this->dataHelper = $this->getMockBuilder(\Magento\Framework\Api\DataObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->repository = new BlockRepository(
            $this->blockResource,
            $blockFactory,
            $blockDataFactory,
            $collectionFactory,
            $blockSearchResultFactory,
            $this->dataHelper,
            $this->dataObjectProcessor,
            $this->storeManager
        );
    }

    /**
     * @test
     */
    public function testSave()
    {
        $this->blockResource->expects($this->once())
            ->method('save')
            ->with($this->block)
            ->willReturnSelf();
        $this->assertEquals($this->block, $this->repository->save($this->block));
    }

    /**
     * @test
     */
    public function testDeleteById()
    {
        $blockId = '123';

        $this->block->expects($this->once())
            ->method('getId')
            ->willReturn(true);
        $this->blockResource->expects($this->once())
            ->method('load')
            ->with($this->block, $blockId)
            ->willReturn($this->block);
        $this->blockResource->expects($this->once())
            ->method('delete')
            ->with($this->block)
            ->willReturnSelf();

        $this->assertTrue($this->repository->deleteById($blockId));
    }

    /**
     * @test
     *
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testSaveException()
    {
        $this->blockResource->expects($this->once())
            ->method('save')
            ->with($this->block)
            ->willThrowException(new \Exception());
        $this->repository->save($this->block);
    }

    /**
     * @test
     *
     * @expectedException \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function testDeleteException()
    {
        $this->blockResource->expects($this->once())
            ->method('delete')
            ->with($this->block)
            ->willThrowException(new \Exception());
        $this->repository->delete($this->block);
    }

    /**
     * @test
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testGetByIdException()
    {
        $blockId = '123';

        $this->block->expects($this->once())
            ->method('getId')
            ->willReturn(false);
        $this->blockResource->expects($this->once())
            ->method('load')
            ->with($this->block, $blockId)
            ->willReturn($this->block);
        $this->repository->getById($blockId);
    }

    /**
     * @test
     */
    public function testGetList()
    {
        $field = 'name';
        $value = 'magento';
        $condition = 'eq';
        $total = 10;
        $currentPage = 3;
        $pageSize = 2;
        $sortField = 'id';

        $criteria = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteriaInterface::class)->getMock();
        $filterGroup = $this->getMockBuilder(\Magento\Framework\Api\Search\FilterGroup::class)->getMock();
        $filter = $this->getMockBuilder(\Magento\Framework\Api\Filter::class)->getMock();
        $storeFilter = $this->getMockBuilder(\Magento\Framework\Api\Filter::class)->getMock();
        $sortOrder = $this->getMockBuilder(\Magento\Framework\Api\SortOrder::class)->getMock();

        $criteria->expects($this->once())->method('getFilterGroups')->willReturn([$filterGroup]);
        $criteria->expects($this->once())->method('getSortOrders')->willReturn([$sortOrder]);
        $criteria->expects($this->once())->method('getCurrentPage')->willReturn($currentPage);
        $criteria->expects($this->once())->method('getPageSize')->willReturn($pageSize);
        $filterGroup->expects($this->once())->method('getFilters')->willReturn([$storeFilter, $filter]);
        $filter->expects($this->once())->method('getConditionType')->willReturn($condition);
        $filter->expects($this->any())->method('getField')->willReturn($field);
        $filter->expects($this->once())->method('getValue')->willReturn($value);
        $storeFilter->expects($this->any())->method('getField')->willReturn('store_id');
        $storeFilter->expects($this->once())->method('getValue')->willReturn(1);
        $sortOrder->expects($this->once())->method('getField')->willReturn($sortField);
        $sortOrder->expects($this->once())->method('getDirection')->willReturn(SortOrder::SORT_DESC);

        /** @var \Magento\Framework\Api\SearchCriteriaInterface $criteria */

        $this->collection->addItem($this->block);
        $this->blockSearchResult->expects($this->once())
            ->method('setSearchCriteria')
            ->with($criteria)
            ->willReturnSelf();
        $this->collection->expects($this->once())
            ->method('addFieldToFilter')
            ->with([$field], [[$condition => $value]])
            ->willReturnSelf();
        $this->blockSearchResult->expects($this->once())->method('setTotalCount')->with($total)->willReturnSelf();
        $this->collection->expects($this->once())->method('getSize')->willReturn($total);
        $this->collection->expects($this->once())->method('setCurPage')->with($currentPage)->willReturnSelf();
        $this->collection->expects($this->once())->method('setPageSize')->with($pageSize)->willReturnSelf();
        $this->collection->expects($this->once())->method('addOrder')->with($sortField, 'DESC')->willReturnSelf();
        $this->block->expects($this->once())->method('getData')->willReturn(['data']);
        $this->blockSearchResult->expects($this->once())->method('setItems')->with(['someData'])->willReturnSelf();
        $this->dataHelper->expects($this->once())
            ->method('populateWithArray')
            ->with($this->blockData, ['data'], \Magento\Cms\Api\Data\BlockInterface::class);
        $this->dataObjectProcessor->expects($this->once())
            ->method('buildOutputDataArray')
            ->with($this->blockData, \Magento\Cms\Api\Data\BlockInterface::class)
            ->willReturn('someData');

        $this->assertEquals($this->blockSearchResult, $this->repository->getList($criteria));
    }
}
