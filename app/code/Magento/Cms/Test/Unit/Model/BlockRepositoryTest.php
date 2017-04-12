<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Model;

use Magento\Cms\Model\BlockRepository;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;

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
     * @var CollectionProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionProcessor;

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

        $this->collectionProcessor = $this->getMockBuilder(CollectionProcessorInterface::class)
            ->getMockForAbstractClass();

        $this->repository = new BlockRepository(
            $this->blockResource,
            $blockFactory,
            $blockDataFactory,
            $collectionFactory,
            $blockSearchResultFactory,
            $this->dataHelper,
            $this->dataObjectProcessor,
            $this->storeManager,
            $this->collectionProcessor
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
        $total = 10;

        /** @var \Magento\Framework\Api\SearchCriteriaInterface $criteria */
        $criteria = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteriaInterface::class)->getMock();

        $this->collection->addItem($this->block);
        $this->collection->expects($this->once())
            ->method('getSize')
            ->willReturn($total);

        $this->collectionProcessor->expects($this->once())
            ->method('process')
            ->with($criteria, $this->collection)
            ->willReturnSelf();

        $this->blockSearchResult->expects($this->once())
            ->method('setSearchCriteria')
            ->with($criteria)
            ->willReturnSelf();
        $this->blockSearchResult->expects($this->once())
            ->method('setTotalCount')
            ->with($total)
            ->willReturnSelf();
        $this->blockSearchResult->expects($this->once())
            ->method('setItems')
            ->with(['someData'])
            ->willReturnSelf();

        $this->block->expects($this->once())
            ->method('getData')
            ->willReturn(['data']);

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
