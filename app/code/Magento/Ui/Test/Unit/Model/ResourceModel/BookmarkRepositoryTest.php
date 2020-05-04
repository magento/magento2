<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ui\Test\Unit\Model\ResourceModel;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Ui\Api\Data\BookmarkInterface;
use Magento\Ui\Api\Data\BookmarkInterfaceFactory;
use Magento\Ui\Api\Data\BookmarkSearchResultsInterface;
use Magento\Ui\Api\Data\BookmarkSearchResultsInterfaceFactory;
use Magento\Ui\Model\ResourceModel\Bookmark;
use Magento\Ui\Model\ResourceModel\Bookmark\Collection;
use Magento\Ui\Model\ResourceModel\BookmarkRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BookmarkRepositoryTest extends TestCase
{
    /**
     * @var BookmarkRepository|MockObject
     */
    protected $bookmarkRepository;

    /**
     * @var BookmarkInterface|MockObject
     */
    protected $bookmarkMock;

    /**
     * @var Bookmark|MockObject
     */
    protected $bookmarkResourceMock;

    /**
     * @var BookmarkSearchResultsInterface|MockObject
     */
    protected $searchResultsMock;

    /**
     * @var MockObject
     */
    private $collectionProcessor;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->bookmarkMock = $this->getMockBuilder(\Magento\Ui\Model\Bookmark::class)
            ->disableOriginalConstructor()
            ->getMock();
        $bookmarkFactoryMock = $this->getMockBuilder(BookmarkInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        /** @var BookmarkInterfaceFactory $bookmarkFactoryMock */
        $bookmarkFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->bookmarkMock);
        $this->bookmarkResourceMock = $this->getMockBuilder(Bookmark::class)
            ->disableOriginalConstructor()
            ->setMethods(['load', 'save', 'delete'])
            ->getMock();

        $this->searchResultsMock = $this->getMockBuilder(BookmarkSearchResultsInterface::class)
            ->getMockForAbstractClass();
        /** @var $searchResultsFactoryMock \Magento\Ui\Api\Data\BookmarkSearchResultsInterfaceFactory */
        $searchResultsFactoryMock = $this->getMockBuilder(
            BookmarkSearchResultsInterfaceFactory::class
        )->disableOriginalConstructor()
            ->setMethods(['create'])->getMock();
        $searchResultsFactoryMock->expects($this->any())->method('create')->willReturn($this->searchResultsMock);
        $this->collectionProcessor = $this->createMock(
            CollectionProcessorInterface::class
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
        $this->expectException(CouldNotSaveException::class);
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
        $this->expectException(NoSuchEntityException::class);
        $exceptionMessage = (string)__(
            'The bookmark with "%1" ID doesn\'t exist. Verify your information and try again.',
            $notExistsBookmarkId
        );
        $this->expectExceptionMessage($exceptionMessage);
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
        $this->expectException(CouldNotDeleteException::class);
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
        $collection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collection->expects($this->once())
            ->method('getItems')
            ->willReturn([$this->bookmarkMock]);
        $this->bookmarkMock->expects($this->once())
            ->method('getCollection')
            ->willReturn($collection);
        $searchCriteria = $this->getMockBuilder(SearchCriteriaInterface::class)
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
