<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\Test\Unit\SearchCriteria\CollectionProcessor;

use Magento\Framework\Api\SearchCriteria\CollectionProcessor\PaginationProcessor;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Data\Collection\AbstractDb;

class PaginationProcessorTest extends \PHPUnit\Framework\TestCase
{
    public function testProcess()
    {
        $model = new PaginationProcessor;

        /** @var SearchCriteriaInterface|\PHPUnit\Framework\MockObject\MockObject $searchCriteriaMock */
        $searchCriteriaMock = $this->getMockBuilder(SearchCriteriaInterface::class)
            ->getMock();
        $searchCriteriaMock->expects($this->once())
            ->method('getCurrentPage')
            ->willReturn(22);
        $searchCriteriaMock->expects($this->once())
            ->method('getPageSize')
            ->willReturn(33);

        /** @var AbstractDb|\PHPUnit\Framework\MockObject\MockObject $searchCriteriarMock */
        $collectionMock = $this->getMockBuilder(AbstractDb::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collectionMock->expects($this->once())
            ->method('setCurPage')
            ->with(22)
            ->willReturnSelf();
        $collectionMock->expects($this->once())
            ->method('setPageSize')
            ->with(33)
            ->willReturnSelf();

        $model->process($searchCriteriaMock, $collectionMock);
    }
}
