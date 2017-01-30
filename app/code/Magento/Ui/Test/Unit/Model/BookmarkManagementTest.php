<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Ui\Test\Unit\Model;

use Magento\Framework\Api\Filter;
use Magento\Ui\Model\BookmarkManagement;

/**
 * Class BookmarkManagementTest
 */
class BookmarkManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BookmarkManagement
     */
    protected $bookmarkManagement;

    /**
     * @var \Magento\Ui\Api\BookmarkRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bookmarkRepository;

    /**
     * @var \Magento\Framework\Api\FilterBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filterBuilder;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $userContext;

    public function setUp()
    {
        $this->bookmarkRepository = $this->getMockBuilder('Magento\Ui\Api\BookmarkRepositoryInterface')
            ->getMockForAbstractClass();
        $this->filterBuilder = $this->getMockBuilder('Magento\Framework\Api\FilterBuilder')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->searchCriteriaBuilder =$this->getMockBuilder('Magento\Framework\Api\SearchCriteriaBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $this->userContext = $this->getMockBuilder('Magento\Authorization\Model\UserContextInterface')
            ->getMockForAbstractClass();
        $this->bookmarkManagement = new BookmarkManagement(
            $this->bookmarkRepository,
            $this->filterBuilder,
            $this->searchCriteriaBuilder,
            $this->userContext
        );
    }

    public function testLoadByNamespace()
    {
        $userId = 1;
        $namespace = 'some_namespace';
        $this->userContext->expects($this->once())
            ->method('getUserId')
            ->willReturn($userId);
        $fieldUserId = new Filter(
            [
                Filter::KEY_FIELD => 'user_id',
                Filter::KEY_VALUE => $userId,
                Filter::KEY_CONDITION_TYPE => 'eq'
            ]
        );
        $fieldNamespace = new Filter(
            [
                Filter::KEY_FIELD => 'namespace',
                Filter::KEY_VALUE => $namespace,
                Filter::KEY_CONDITION_TYPE => 'eq'
            ]
        );
        $searchCriteria = $this->getMockBuilder('Magento\Framework\Api\SearchCriteriaInterface')
            ->getMockForAbstractClass();
        $this->filterBuilder->expects($this->at(0))
            ->method('create')
            ->willReturn($fieldUserId);
        $this->filterBuilder->expects($this->at(1))
            ->method('create')
            ->willReturn($fieldNamespace);
        $this->searchCriteriaBuilder->expects($this->once())
            ->method('addFilters')
            ->with([$fieldUserId, $fieldNamespace]);
        $this->searchCriteriaBuilder->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteria);
        $searchResult = $this->getMockBuilder('Magento\Ui\Api\Data\BookmarkSearchResultsInterface')
            ->getMockForAbstractClass();
        $this->bookmarkRepository->expects($this->once())
            ->method('getList')
            ->with($searchCriteria)
            ->willReturn($searchResult);
        $this->assertEquals($searchResult, $this->bookmarkManagement->loadByNamespace($namespace));
    }

    public function testGetByIdentifierNamespace()
    {
        $userId = 1;
        $namespace = 'some_namespace';
        $identifier ='current';
        $this->userContext->expects($this->once())
            ->method('getUserId')
            ->willReturn($userId);
        $fieldUserId = new Filter(
            [
                Filter::KEY_FIELD => 'user_id',
                Filter::KEY_VALUE => $userId,
                Filter::KEY_CONDITION_TYPE => 'eq'
            ]
        );
        $fieldIdentifier = new Filter(
            [
                Filter::KEY_FIELD => 'identifier',
                Filter::KEY_VALUE => $identifier,
                Filter::KEY_CONDITION_TYPE => 'eq'
            ]
        );
        $fieldNamespace = new Filter(
            [
                Filter::KEY_FIELD => 'namespace',
                Filter::KEY_VALUE => $namespace,
                Filter::KEY_CONDITION_TYPE => 'eq'
            ]
        );
        $bookmarkId = 1;
        $bookmark = $this->getMockBuilder('Magento\Ui\Api\Data\BookmarkInterface')->getMockForAbstractClass();
        $bookmark->expects($this->once())->method('getId')->willReturn($bookmarkId);
        $searchCriteria = $this->getMockBuilder('Magento\Framework\Api\SearchCriteriaInterface')
            ->getMockForAbstractClass();
        $this->filterBuilder->expects($this->at(0))
            ->method('create')
            ->willReturn($fieldUserId);
        $this->filterBuilder->expects($this->at(1))
            ->method('create')
            ->willReturn($fieldIdentifier);
        $this->filterBuilder->expects($this->at(2))
            ->method('create')
            ->willReturn($fieldNamespace);
        $this->searchCriteriaBuilder->expects($this->once())
            ->method('addFilters')
            ->with([$fieldUserId, $fieldIdentifier, $fieldNamespace]);
        $this->searchCriteriaBuilder->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteria);
        $searchResult = $this->getMockBuilder('Magento\Ui\Api\Data\BookmarkSearchResultsInterface')
            ->getMockForAbstractClass();
        $searchResult->expects($this->once())->method('getTotalCount')->willReturn(1);
        $searchResult->expects($this->once())->method('getItems')->willReturn([$bookmark]);
        $this->bookmarkRepository->expects($this->once())
            ->method('getList')
            ->with($searchCriteria)
            ->willReturn($searchResult);
        $this->bookmarkRepository->expects($this->once())
            ->method('getById')
            ->with($bookmarkId)
            ->willReturn($bookmark);
        $this->assertEquals(
            $bookmark,
            $this->bookmarkManagement->getByIdentifierNamespace($identifier, $namespace)
        );
    }
}
