<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Model;

use Magento\Cms\Model\PageRepository;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;

/**
 * Test for Magento\Cms\Model\PageRepository
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PageRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PageRepository
     */
    protected $repository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Cms\Model\ResourceModel\Page
     */
    protected $pageResource;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Cms\Model\Page
     */
    protected $page;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Cms\Api\Data\PageInterface
     */
    protected $pageData;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Cms\Api\Data\PageSearchResultsInterface
     */
    protected $pageSearchResult;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Api\DataObjectHelper
     */
    protected $dataHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Reflection\DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Cms\Model\ResourceModel\Page\Collection
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
        $this->pageResource = $this->getMockBuilder(\Magento\Cms\Model\ResourceModel\Page::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataObjectProcessor = $this->getMockBuilder(\Magento\Framework\Reflection\DataObjectProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pageFactory = $this->getMockBuilder(\Magento\Cms\Model\PageFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $pageDataFactory = $this->getMockBuilder(\Magento\Cms\Api\Data\PageInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $pageSearchResultFactory = $this->getMockBuilder(\Magento\Cms\Api\Data\PageSearchResultsInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $collectionFactory = $this->getMockBuilder(\Magento\Cms\Model\ResourceModel\Page\CollectionFactory::class)
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

        $this->page = $this->getMockBuilder(\Magento\Cms\Model\Page::class)->disableOriginalConstructor()->getMock();
        $this->pageData = $this->getMockBuilder(\Magento\Cms\Api\Data\PageInterface::class)
            ->getMock();
        $this->pageSearchResult = $this->getMockBuilder(\Magento\Cms\Api\Data\PageSearchResultsInterface::class)
            ->getMock();
        $this->collection = $this->getMockBuilder(\Magento\Cms\Model\ResourceModel\Page\Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSize', 'setCurPage', 'setPageSize', 'load', 'addOrder'])
            ->getMock();

        $pageFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->page);
        $pageDataFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->pageData);
        $pageSearchResultFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->pageSearchResult);
        $collectionFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->collection);
        /**
         * @var \Magento\Cms\Model\PageFactory $pageFactory
         * @var \Magento\Cms\Api\Data\PageInterfaceFactory $pageDataFactory
         * @var \Magento\Cms\Api\Data\PageSearchResultsInterfaceFactory $pageSearchResultFactory
         * @var \Magento\Cms\Model\ResourceModel\Page\CollectionFactory $collectionFactory
         */

        $this->dataHelper = $this->getMockBuilder(\Magento\Framework\Api\DataObjectHelper::class)
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
            ->with($this->page)
            ->willReturnSelf();
        $this->assertEquals($this->page, $this->repository->save($this->page));
    }

    /**
     * @test
     */
    public function testDeleteById()
    {
        $pageId = '123';

        $this->page->expects($this->once())
            ->method('getId')
            ->willReturn(true);
        $this->page->expects($this->once())
            ->method('load')
            ->with($pageId)
            ->willReturnSelf();
        $this->pageResource->expects($this->once())
            ->method('delete')
            ->with($this->page)
            ->willReturnSelf();

        $this->assertTrue($this->repository->deleteById($pageId));
    }

    /**
     * @test
     *
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testSaveException()
    {
        $this->pageResource->expects($this->once())
            ->method('save')
            ->with($this->page)
            ->willThrowException(new \Exception());
        $this->repository->save($this->page);
    }

    /**
     * @test
     *
     * @expectedException \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function testDeleteException()
    {
        $this->pageResource->expects($this->once())
            ->method('delete')
            ->with($this->page)
            ->willThrowException(new \Exception());
        $this->repository->delete($this->page);
    }

    /**
     * @test
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testGetByIdException()
    {
        $pageId = '123';

        $this->page->expects($this->once())
            ->method('getId')
            ->willReturn(false);
        $this->page->expects($this->once())
            ->method('load')
            ->with($pageId)
            ->willReturnSelf();
        $this->repository->getById($pageId);
    }

    /**
     * @test
     */
    public function testGetList()
    {
        $total = 10;

        /** @var \Magento\Framework\Api\SearchCriteriaInterface $criteria */
        $criteria = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteriaInterface::class)->getMock();

        $this->collection->addItem($this->page);
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
            ->with(['someData'])
            ->willReturnSelf();

        $this->page->expects($this->once())
            ->method('getData')
            ->willReturn(['data']);

        $this->dataHelper->expects($this->once())
            ->method('populateWithArray')
            ->with($this->pageData, ['data'], \Magento\Cms\Api\Data\PageInterface::class);

        $this->dataObjectProcessor->expects($this->once())
            ->method('buildOutputDataArray')
            ->with($this->pageData, \Magento\Cms\Api\Data\PageInterface::class)
            ->willReturn('someData');

        $this->assertEquals($this->pageSearchResult, $this->repository->getList($criteria));
    }
}
