<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Model;

use Exception;
use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\Data\PageInterfaceFactory;
use Magento\Cms\Api\Data\PageSearchResultsInterface;
use Magento\Cms\Api\Data\PageSearchResultsInterfaceFactory;
use Magento\Cms\Model\PageExtensible;
use Magento\Cms\Model\PageFactory;
use Magento\Cms\Model\PageRepository;
use Magento\Cms\Model\ResourceModel\Page;
use Magento\Cms\Model\ResourceModel\Page\Collection;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Test for Magento\Cms\Model\PageRepository
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PageRepositoryTest extends TestCase
{
    /**
     * @var PageRepository
     */
    protected $repository;

    /**
     * @var MockObject|Page
     */
    protected $pageResource;

    /**
     * @var MockObject|\Magento\Cms\Model\Page
     */
    protected $page;

    /**
     * @var MockObject|PageExtensible
     */
    protected $pageExtensible;

    /**
     * @var MockObject|PageInterface
     */
    protected $pageData;

    /**
     * @var MockObject|PageSearchResultsInterface
     */
    protected $pageSearchResult;

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
        $this->pageResource = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataObjectProcessor = $this->getMockBuilder(DataObjectProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pageFactory = $this->getMockBuilder(PageFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $pageDataFactory = $this->getMockBuilder(PageInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $pageSearchResultFactory = $this->getMockBuilder(PageSearchResultsInterfaceFactory::class)
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

        $this->page = $this->getMockBuilder(\Magento\Cms\Model\Page::class)->disableOriginalConstructor()->getMock();
        $this->pageExtensible = $this->getMockBuilder(PageExtensible::class)->disableOriginalConstructor()->getMock();
        $this->pageData = $this->getMockBuilder(PageInterface::class)
            ->getMock();
        $this->pageSearchResult = $this->getMockBuilder(PageSearchResultsInterface::class)
            ->getMock();
        $this->collection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSize', 'setCurPage', 'setPageSize', 'load', 'addOrder'])
            ->getMock();

        $pageFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->page);
        $pageDataFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->pageExtensible);
        $pageSearchResultFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->pageSearchResult);
        $collectionFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->collection);
        /**
         * @var PageFactory $pageFactory
         * @var PageInterfaceFactory $pageDataFactory
         * @var PageSearchResultsInterfaceFactory $pageSearchResultFactory
         * @var CollectionFactory $collectionFactory
         */

        $this->dataHelper = $this->getMockBuilder(DataObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->collectionProcessor = $this->getMockBuilder(CollectionProcessorInterface::class)
            ->getMockForAbstractClass();

        $this->repository = new PageRepository(
            $this->pageResource,
            $pageFactory,
            $pageDataFactory,
            $collectionFactory,
            $pageSearchResultFactory,
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
        $this->pageResource->expects($this->once())
            ->method('save')
            ->with($this->pageExtensible)
            ->willReturnSelf();
        $this->assertEquals($this->pageExtensible, $this->repository->save($this->pageExtensible));
    }

    /**
     * @test
     */
    public function testDeleteById()
    {
        $pageId = '123';

        $this->pageExtensible->expects($this->once())
            ->method('getId')
            ->willReturn(true);
        $this->pageExtensible->expects($this->once())
            ->method('load')
            ->with($pageId)
            ->willReturnSelf();
        $this->pageResource->expects($this->once())
            ->method('delete')
            ->with($this->pageExtensible)
            ->willReturnSelf();

        $this->assertTrue($this->repository->deleteById($pageId));
    }

    /**
     * @test
     */
    public function testSaveException()
    {
        $this->pageResource->expects($this->once())
            ->method('save')
            ->with($this->pageExtensible)
            ->willThrowException(new Exception());
        $this->expectException(CouldNotSaveException::class);
        $this->repository->save($this->pageExtensible);
    }

    /**
     * @test
     */
    public function testDeleteException()
    {
        $this->pageResource->expects($this->once())
            ->method('delete')
            ->with($this->pageExtensible)
            ->willThrowException(new Exception());
        $this->expectException(CouldNotDeleteException::class);
        $this->repository->delete($this->pageExtensible);
    }

    /**
     * @test
     */
    public function testGetByIdException()
    {
        $pageId = '123';

        $this->pageExtensible->expects($this->once())
            ->method('getId')
            ->willReturn(false);
        $this->pageExtensible->expects($this->once())
            ->method('load')
            ->with($pageId)
            ->willReturnSelf();
        $this->expectException(NoSuchEntityException::class);
        $this->repository->getById($pageId);
    }

    /**
     * @test
     */
    public function testGetList()
    {
        $total = 10;

        /** @var SearchCriteriaInterface $criteria */
        $criteria = $this->getMockBuilder(SearchCriteriaInterface::class)->getMock();

        $this->collection->addItem($this->pageExtensible);
        $this->collection->expects($this->once())
            ->method('getSize')
            ->willReturn($total);

        $this->collectionProcessor->expects($this->once())
            ->method('process')
            ->with($criteria, $this->collection)
            ->willReturnSelf();

        $this->pageSearchResult->expects($this->once())
            ->method('setSearchCriteria')
            ->with($criteria)
            ->willReturnSelf();
        $this->pageSearchResult->expects($this->once())
            ->method('setTotalCount')
            ->with($total)
            ->willReturnSelf();
        $this->pageSearchResult->expects($this->once())
            ->method('setItems')
            ->with([$this->pageExtensible])
            ->willReturnSelf();
        $this->assertEquals($this->pageSearchResult, $this->repository->getList($criteria));
    }
}
