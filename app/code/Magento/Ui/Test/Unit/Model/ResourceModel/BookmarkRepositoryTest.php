<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Ui\Test\Unit\Model\ResourceModel;

use Magento\Ui\Model\ResourceModel\BookmarkRepository;

/**
 * Class BookmarkRepositoryTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BookmarkRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var BookmarkRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bookmarkRepository;

    /**
     * @var \Magento\Ui\Api\Data\BookmarkInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bookmarkMock;

    /**
     * @var \Magento\Ui\Model\ResourceModel\Bookmark|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bookmarkResourceMock;

    /**
     * @var \Magento\Ui\Api\Data\BookmarkSearchResultsInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchResultsMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionProcessor;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->bookmarkMock = $this->getMockBuilder(\Magento\Ui\Model\Bookmark::class)
            ->disableOriginalConstructor()
            ->getMock();
        $bookmarkFactoryMock = $this->getMockBuilder(\Magento\Ui\Api\Data\BookmarkInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        /** @var $bookmarkFactoryMock \Magento\Ui\Api\Data\BookmarkInterfaceFactory */
        $bookmarkFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->bookmarkMock);
        $this->bookmarkResourceMock = $this->getMockBuilder(\Magento\Ui\Model\ResourceModel\Bookmark::class)
            ->disableOriginalConstructor()
            ->setMethods(['load', 'save', 'delete'])
            ->getMock();

        $this->searchResultsMock = $this->getMockBuilder(\Magento\Ui\Api\Data\BookmarkSearchResultsInterface::class)
            ->getMockForAbstractClass();
        /** @var $searchResultsFactoryMock \Magento\Ui\Api\Data\BookmarkSearchResultsInterfaceFactory */
        $searchResultsFactoryMock = $this->getMockBuilder(
            \Magento\Ui\Api\Data\BookmarkSearchResultsInterfaceFactory::class
        )->disableOriginalConstructor()->setMethods(['create'])->getMock();
        $searchResultsFactoryMock->expects($this->any())->method('create')->willReturn($this->searchResultsMock);
        $this->collectionProcessor = $this->createMock(
            \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface::class
        );
        $this->bookmarkRepository = new BookmarkRepository(
            $bookmarkFactoryMock,
            $this->bookmarkResourceMock,
            $searchResultsFactoryMock,
            $this->collectionProcessor
        );
    }

    public function testSave()
    {
        $this->bookmarkResourceMock->expects($this->once())
            ->method('save')
            ->with($this->bookmarkMock);
        $this->assertEquals($this->bookmarkMock, $this->bookmarkRepository->save($this->bookmarkMock));
    }

    public function testSaveWithException()
    {
        $exceptionMessage = 'Some Message';
        $this->bookmarkResourceMock->expects($this->once())
            ->method('save')
            ->with($this->bookmarkMock)
            ->willThrowException(new \Exception($exceptionMessage));
        $this->expectException(\Magento\Framework\Exception\CouldNotSaveException::class);
        $this->expectExceptionMessage($exceptionMessage);
        $this->bookmarkRepository->save($this->bookmarkMock);
    }

    public function testGetById()
    {
        $bookmarkId = 1;
        $this->bookmarkMock->expects($this->once())
            ->method('getId')
            ->willReturn($bookmarkId);
        $this->bookmarkResourceMock->expects($this->once())
            ->method('load')
            ->with($this->bookmarkMock, $bookmarkId)
            ->willReturn($this->bookmarkMock);
        $this->assertEquals($this->bookmarkMock, $this->bookmarkRepository->getById($bookmarkId));
    }

    public function testGetByIdWithException()
    {
        $notExistsBookmarkId = 2;
        $this->bookmarkMock->expects($this->once())
            ->method('getId')
            ->willReturn(null);
        $this->bookmarkResourceMock->expects($this->once())
            ->method('load')
            ->with($this->bookmarkMock, $notExistsBookmarkId)
            ->willReturn($this->bookmarkMock);
        $this->expectException(
            \Magento\Framework\Exception\NoSuchEntityException::class,
            __('Bookmark with id "%1" does not exist.', $notExistsBookmarkId)
        );
        $this->bookmarkRepository->getById($notExistsBookmarkId);
    }

    public function testDelete()
    {
        $this->bookmarkResourceMock->expects($this->once())
            ->method('delete')
            ->with($this->bookmarkMock);
        $this->assertTrue($this->bookmarkRepository->delete($this->bookmarkMock));
    }

    public function testDeleteWithException()
    {
        $exceptionMessage = 'Some Message';
        $this->bookmarkResourceMock->expects($this->once())
            ->method('delete')
            ->with($this->bookmarkMock)
            ->willThrowException(new \Exception($exceptionMessage));
        $this->expectException(\Magento\Framework\Exception\CouldNotDeleteException::class);
        $this->expectExceptionMessage($exceptionMessage);
        $this->assertTrue($this->bookmarkRepository->delete($this->bookmarkMock));
    }

    public function testGetList()
    {
        $bookmarkId = 1;

        $this->bookmarkMock->expects($this->any())
            ->method('getId')
            ->willReturn($bookmarkId);
        $this->bookmarkResourceMock->expects($this->once())
            ->method('load')
            ->with($this->bookmarkMock, $bookmarkId)
            ->willReturn($this->bookmarkMock);
        $collection = $this->getMockBuilder(\Magento\Ui\Model\ResourceModel\Bookmark\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collection->expects($this->once())
            ->method('getItems')
            ->willReturn([$this->bookmarkMock]);
        $this->bookmarkMock->expects($this->once())
            ->method('getCollection')
            ->willReturn($collection);
        $searchCriteria = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteriaInterface::class)
            ->getMockForAbstractClass();
        $this->assertEquals($this->searchResultsMock, $this->bookmarkRepository->getList($searchCriteria));
    }

    public function testDeleteById()
    {
        $bookmarkId = 1;
        $this->bookmarkMock->expects($this->once())
            ->method('getId')
            ->willReturn($bookmarkId);
        $this->bookmarkResourceMock->expects($this->once())
            ->method('load')
            ->with($this->bookmarkMock, $bookmarkId)
            ->willReturn($this->bookmarkMock);
        $this->bookmarkResourceMock->expects($this->once())
            ->method('delete')
            ->with($this->bookmarkMock);
        $this->assertTrue($this->bookmarkRepository->deleteById($bookmarkId));
    }
}
