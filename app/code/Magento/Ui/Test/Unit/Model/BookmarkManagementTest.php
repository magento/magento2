<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Ui\Test\Unit\Model;

use Magento\Framework\Api\Filter;
use Magento\Ui\Model\BookmarkManagement;

/**
 * Class BookmarkManagementTest
 */
class BookmarkManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var BookmarkManagement
     */
    protected $bookmarkManagement;

    /**
     * @var \Magento\Ui\Api\BookmarkRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $bookmarkRepository;

    /**
     * @var \Magento\Framework\Api\FilterBuilder|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $filterBuilder;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $userContext;

    protected function setUp(): void
    {
        $this->bookmarkRepository = $this->getMockBuilder(\Magento\Ui\Api\BookmarkRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->filterBuilder = $this->getMockBuilder(\Magento\Framework\Api\FilterBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->searchCriteriaBuilder =$this->getMockBuilder(\Magento\Framework\Api\SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->userContext = $this->getMockBuilder(\Magento\Authorization\Model\UserContextInterface::class)
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
        $searchCriteria = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteriaInterface::class)
            ->getMockForAbstractClass();
        $this->filterBuilder->expects($this->at(0))
            ->method('create')
            ->willReturn($fieldUserId);
        $this->filterBuilder->expects($this->at(1))
            ->method('create')
            ->willReturn($fieldNamespace);
        $this->searchCriteriaBuilder->expects($this->exactly(2))
            ->method('addFilters')
            ->withConsecutive([[$fieldUserId]], [[$fieldNamespace]]);
        $this->searchCriteriaBuilder->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteria);
        $searchResult = $this->getMockBuilder(\Magento\Ui\Api\Data\BookmarkSearchResultsInterface::class)
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
        $bookmark = $this->getMockBuilder(\Magento\Ui\Api\Data\BookmarkInterface::class)->getMockForAbstractClass();
        $bookmark->expects($this->once())->method('getId')->willReturn($bookmarkId);
        $searchCriteria = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteriaInterface::class)
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
        $this->searchCriteriaBuilder->expects($this->exactly(3))
            ->method('addFilters')
            ->withConsecutive([[$fieldUserId]], [[$fieldIdentifier]], [[$fieldNamespace]]);
        $this->searchCriteriaBuilder->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteria);
        $searchResult = $this->getMockBuilder(\Magento\Ui\Api\Data\BookmarkSearchResultsInterface::class)
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
