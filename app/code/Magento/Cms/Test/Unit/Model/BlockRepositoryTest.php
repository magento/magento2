<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Model;

use Exception;
use Magento\Cms\Api\Data\BlockInterface;
use Magento\Cms\Api\Data\BlockInterfaceFactory;
use Magento\Cms\Api\Data\BlockSearchResultsInterface;
use Magento\Cms\Api\Data\BlockSearchResultsInterfaceFactory;
use Magento\Cms\Model\BlockExtensible;
use Magento\Cms\Model\BlockFactory;
use Magento\Cms\Model\BlockRepository;
use Magento\Cms\Model\ResourceModel\Block;
use Magento\Cms\Model\ResourceModel\Block\Collection;
use Magento\Cms\Model\ResourceModel\Block\CollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Magento\Cms\Model\BlockRepository
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BlockRepositoryTest extends TestCase
{
    /**
     * @var BlockRepository
     */
    protected $repository;

    /**
     * @var MockObject|Block
     */
    protected $blockResource;

    /**
     * @var MockObject|Block
     */
    protected $block;

    /**
     * @var MockObject|BlockExtensible
     */
    protected $blockExtensible;

    /**
     * @var MockObject|BlockInterface
     */
    protected $blockData;

    /**
     * @var MockObject|BlockSearchResultsInterface
     */
    protected $blockSearchResult;

    /**
     * @var MockObject|DataObjectHelper
     */
    protected $dataHelper;

    /**
     * @var MockObject|DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var MockObject|Collection
     */
    protected $collection;

    /**
     * @var MockObject|StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CollectionProcessorInterface|MockObject
     */
    private $collectionProcessor;

    /**
     * Initialize repository
     */
    protected function setUp()
    {
        $this->blockResource = $this->getMockBuilder(Block::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataObjectProcessor = $this->getMockBuilder(DataObjectProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $blockFactory = $this->getMockBuilder(BlockFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $blockDataFactory = $this->getMockBuilder(BlockInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $blockSearchResultFactory = $this->getMockBuilder(
            BlockSearchResultsInterfaceFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $collectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $store = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $store->expects($this->any())->method('getId')->willReturn(0);
        $this->storeManager->expects($this->any())->method('getStore')->willReturn($store);

        $this->block = $this->getMockBuilder(Block::class)->disableOriginalConstructor()->getMock();
        $this->blockExtensible = $this->getMockBuilder(BlockExtensible::class)->disableOriginalConstructor()->getMock();
        $this->blockData = $this->getMockBuilder(BlockInterface::class)
            ->getMock();
        $this->blockSearchResult = $this->getMockBuilder(BlockSearchResultsInterface::class)
            ->getMock();
        $this->collection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['addFieldToFilter', 'getSize', 'setCurPage', 'setPageSize', 'load', 'addOrder'])
            ->getMock();

        $blockFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->block);
        $blockDataFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->blockExtensible);
        $blockSearchResultFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->blockSearchResult);
        $collectionFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->collection);
        /**
         * @var BlockFactory $blockFactory
         * @var BlockInterfaceFactory $blockDataFactory
         * @var BlockSearchResultsInterfaceFactory $blockSearchResultFactory
         * @var CollectionFactory $collectionFactory
         */

        $this->dataHelper = $this->getMockBuilder(DataObjectHelper::class)
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
            ->with($this->blockExtensible)
            ->willReturnSelf();
        $this->assertEquals($this->blockExtensible, $this->repository->save($this->blockExtensible));
    }

    /**
     * @test
     */
    public function testDeleteById()
    {
        $blockId = '123';

        $this->blockExtensible->expects($this->once())
            ->method('getId')
            ->willReturn(true);
        $this->blockResource->expects($this->once())
            ->method('load')
            ->with($this->blockExtensible, $blockId)
            ->willReturn($this->blockExtensible);
        $this->blockResource->expects($this->once())
            ->method('delete')
            ->with($this->blockExtensible)
            ->willReturnSelf();

        $this->assertTrue($this->repository->deleteById($blockId));
    }

    /**
     * @test
     */
    public function testSaveException()
    {
        $this->blockResource->expects($this->once())
            ->method('save')
            ->with($this->blockExtensible)
            ->willThrowException(new Exception());
        $this->expectException(CouldNotSaveException::class);
        $this->repository->save($this->blockExtensible);
    }

    /**
     * @test
     */
    public function testDeleteException()
    {
        $this->blockResource->expects($this->once())
            ->method('delete')
            ->with($this->blockExtensible)
            ->willThrowException(new Exception());
        $this->expectException(CouldNotDeleteException::class);
        $this->repository->delete($this->blockExtensible);
    }

    /**
     * @test
     */
    public function testGetByIdException()
    {
        $blockId = '123';

        $this->blockExtensible->expects($this->once())
            ->method('getId')
            ->willReturn(null);
        $this->blockResource->expects($this->once())
            ->method('load')
            ->with($this->blockExtensible, $blockId)
            ->willReturn($this->blockExtensible);
        $this->expectException(NoSuchEntityException::class);
        $this->repository->getById($blockId);
    }

    /**
     * @test
     */
    public function testGetList()
    {
        $total = 10;

        /** @var SearchCriteriaInterface $criteria */
        $criteria = $this->getMockBuilder(SearchCriteriaInterface::class)->getMock();

        $this->collection->addItem($this->blockExtensible);
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
            ->with([$this->blockExtensible])
            ->willReturnSelf();
        $this->assertEquals($this->blockSearchResult, $this->repository->getList($criteria));
    }
}
