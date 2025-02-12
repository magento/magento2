<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ui\Test\Unit\Model;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Ui\Api\BookmarkRepositoryInterface;
use Magento\Ui\Api\Data\BookmarkInterface;
use Magento\Ui\Api\Data\BookmarkSearchResultsInterface;
use Magento\Ui\Model\BookmarkManagement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BookmarkManagementTest extends TestCase
{
    /**
     * @var BookmarkManagement
     */
    protected $bookmarkManagement;

    /**
     * @var BookmarkRepositoryInterface|MockObject
     */
    protected $bookmarkRepository;

    /**
     * @var FilterBuilder|MockObject
     */
    protected $filterBuilder;

    /**
     * @var SearchCriteriaBuilder|MockObject
     */
    protected $searchCriteriaBuilder;

    /**
     * @var UserContextInterface|MockObject
     */
    protected $userContext;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->bookmarkRepository = $this->getMockBuilder(BookmarkRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->filterBuilder = $this->getMockBuilder(FilterBuilder::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->searchCriteriaBuilder =$this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->userContext = $this->getMockBuilder(UserContextInterface::class)
            ->getMockForAbstractClass();
        $this->bookmarkManagement = new BookmarkManagement(
            $this->bookmarkRepository,
            $this->filterBuilder,
            $this->searchCriteriaBuilder,
            $this->userContext
        );
    }

    /**
     * @return void
     */
    public function testLoadByNamespace(): void
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
        $searchCriteria = $this->getMockBuilder(SearchCriteriaInterface::class)
            ->getMockForAbstractClass();
        $this->filterBuilder
            ->method('create')
            ->willReturnOnConsecutiveCalls($fieldUserId, $fieldNamespace);
        $this->searchCriteriaBuilder->expects($this->exactly(2))
            ->method('addFilters')
            ->willReturnCallback(function ($param1) use ($fieldUserId, $fieldNamespace) {
                if ($param1 == [$fieldUserId] || $param1 == [$fieldNamespace]) {
                    return null;
                }
            });

        $this->searchCriteriaBuilder->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteria);
        $searchResult = $this->getMockBuilder(BookmarkSearchResultsInterface::class)
            ->getMockForAbstractClass();
        $this->bookmarkRepository->expects($this->once())
            ->method('getList')
            ->with($searchCriteria)
            ->willReturn($searchResult);
        $this->assertEquals($searchResult, $this->bookmarkManagement->loadByNamespace($namespace));
    }

    /**
     * @return void
     */
    public function testGetByIdentifierNamespace(): void
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
        $bookmark = $this->getMockBuilder(BookmarkInterface::class)
            ->getMockForAbstractClass();
        $bookmark->expects($this->once())->method('getId')->willReturn($bookmarkId);
        $searchCriteria = $this->getMockBuilder(SearchCriteriaInterface::class)
            ->getMockForAbstractClass();
        $this->filterBuilder
            ->method('create')
            ->willReturnOnConsecutiveCalls($fieldUserId, $fieldIdentifier, $fieldNamespace);
        $this->searchCriteriaBuilder->expects($this->exactly(3))
            ->method('addFilters')
            ->willReturnCallback(function ($param1) use ($fieldUserId, $fieldNamespace) {
                if ($param1 == [$fieldUserId]
                    || $param1 == [$fieldNamespace]
                    || $param1 == [$fieldNamespace]) {
                    return null;
                }
            });
        $this->searchCriteriaBuilder->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteria);
        $searchResult = $this->getMockBuilder(BookmarkSearchResultsInterface::class)
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
