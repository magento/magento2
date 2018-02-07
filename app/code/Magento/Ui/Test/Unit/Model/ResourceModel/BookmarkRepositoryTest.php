<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Ui\Test\Unit\Model\ResourceModel;

use Magento\Framework\Api\SortOrder;
use Magento\Ui\Model\ResourceModel\BookmarkRepository;

/**
 * Class BookmarkRepositoryTest
 */
class BookmarkRepositoryTest extends \PHPUnit_Framework_TestCase
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
     * Set up
     */
    protected function setUp()
    {
        $this->bookmarkMock = $this->getMockBuilder('Magento\Ui\Model\Bookmark')
            ->disableOriginalConstructor()
            ->getMock();
        $bookmarkFactoryMock = $this->getMockBuilder('Magento\Ui\Api\Data\BookmarkInterfaceFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        /** @var $bookmarkFactoryMock \Magento\Ui\Api\Data\BookmarkInterfaceFactory */
        $bookmarkFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->bookmarkMock);
        $this->bookmarkResourceMock = $this->getMockBuilder('Magento\Ui\Model\ResourceModel\Bookmark')
            ->disableOriginalConstructor()
            ->setMethods(['load', 'save', 'delete'])
            ->getMock();

        $this->searchResultsMock = $this->getMockBuilder('Magento\Ui\Api\Data\BookmarkSearchResultsInterface')
            ->getMockForAbstractClass();
        /** @var $searchResultsFactoryMock \Magento\Ui\Api\Data\BookmarkSearchResultsInterfaceFactory */
        $searchResultsFactoryMock = $this->getMockBuilder(
            'Magento\Ui\Api\Data\BookmarkSearchResultsInterfaceFactory'
        )->disableOriginalConstructor()->setMethods(['create'])->getMock();
        $searchResultsFactoryMock->expects($this->any())->method('create')->willReturn($this->searchResultsMock);

        $this->bookmarkRepository = new BookmarkRepository(
            $bookmarkFactoryMock,
            $this->bookmarkResourceMock,
            $searchResultsFactoryMock
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
        $this->setExpectedException('Magento\Framework\Exception\CouldNotSaveException', __($exceptionMessage));
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
        $this->setExpectedException(
            'Magento\Framework\Exception\NoSuchEntityException',
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
        $this->setExpectedException('Magento\Framework\Exception\CouldNotDeleteException', __($exceptionMessage));
        $this->assertTrue($this->bookmarkRepository->delete($this->bookmarkMock));
    }

    public function testGetList()
    {
        $bookmarkId = 1;
        $fieldNameAsc = 'first_field';
        $fieldValueAsc = 'first_value';
        $fieldNameDesc = 'second_field';
        $fieldValueDesc = 'second_value';
        $this->bookmarkMock->expects($this->any())
            ->method('getId')
            ->willReturn($bookmarkId);
        $this->bookmarkResourceMock->expects($this->once())
            ->method('load')
            ->with($this->bookmarkMock, $bookmarkId)
            ->willReturn($this->bookmarkMock);
        $filterGroup = $this->getMockBuilder('Magento\Framework\Api\Search\FilterGroup')
            ->disableOriginalConstructor()
            ->getMock();
        $sortOrderAsc = $this->getMockBuilder('Magento\Framework\Api\SortOrder')
            ->disableOriginalConstructor()
            ->getMock();
        $sortOrderAsc->expects($this->once())
            ->method('getField')
            ->willReturn($fieldNameAsc);
        $sortOrderAsc->expects($this->once())
            ->method('getDirection')
            ->willReturn(SortOrder::SORT_ASC);
        $sortOrderDesc = $this->getMockBuilder('Magento\Framework\Api\SortOrder')
            ->disableOriginalConstructor()
            ->getMock();
        $sortOrderDesc->expects($this->once())
            ->method('getField')
            ->willReturn($fieldNameDesc);
        $sortOrderDesc->expects($this->once())
            ->method('getDirection')
            ->willReturn(SortOrder::SORT_DESC);
        $fieldAsc = $this->getMockBuilder('Magento\Framework\Api\Filter')
            ->disableOriginalConstructor()
            ->getMock();
        $fieldAsc->expects($this->once())
            ->method('getField')
            ->willReturn($fieldNameAsc);
        $fieldAsc->expects($this->once())
            ->method('getValue')
            ->willReturn($fieldValueAsc);
        $fieldAsc->expects($this->any())
            ->method('getConditionType')
            ->willReturn(false);
        $fieldDesc = $this->getMockBuilder('Magento\Framework\Api\Filter')
            ->disableOriginalConstructor()
            ->getMock();
        $fieldDesc->expects($this->once())
            ->method('getField')
            ->willReturn($fieldNameDesc);
        $fieldDesc->expects($this->once())
            ->method('getValue')
            ->willReturn($fieldValueDesc);
        $fieldDesc->expects($this->any())
            ->method('getConditionType')
            ->willReturn('eq');
        $filterGroup->expects($this->once())
            ->method('getFilters')
            ->willReturn([$fieldAsc, $fieldDesc]);
        $collection = $this->getMockBuilder('Magento\Ui\Model\ResourceModel\Bookmark\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $collection->expects($this->once())
            ->method('getItems')
            ->willReturn([$this->bookmarkMock]);
        $collection->expects($this->any())
            ->method('addOrder')
            ->willReturnMap([[$fieldNameAsc, SortOrder::SORT_ASC], [$fieldNameDesc, SortOrder::SORT_DESC]]);
        $collection->expects($this->any())
            ->method('addFieldToFilter')
            ->willReturnMap([[$fieldNameAsc, [$fieldValueAsc => 'eq']], [$fieldNameDesc, [$fieldValueDesc => 'eq']]]);
        $this->bookmarkMock->expects($this->once())
            ->method('getCollection')
            ->willReturn($collection);
        $searchCriteria = $this->getMockBuilder('Magento\Framework\Api\SearchCriteriaInterface')
            ->getMockForAbstractClass();
        $searchCriteria->expects($this->once())
            ->method('getFilterGroups')
            ->willReturn([$filterGroup]);
        $searchCriteria->expects($this->once())
            ->method('getSortOrders')
            ->willReturn([$sortOrderAsc, $sortOrderDesc]);
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
