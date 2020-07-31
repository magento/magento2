<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Api\Test\Unit\SearchCriteria\CollectionProcessor;

use Magento\Framework\Api\SearchCriteria\CollectionProcessor\PaginationProcessor;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PaginationProcessorTest extends TestCase
{
    public function testProcess()
    {
        $model = new PaginationProcessor();

        /** @var SearchCriteriaInterface|MockObject $searchCriteriaMock */
        $searchCriteriaMock = $this->getMockBuilder(SearchCriteriaInterface::class)
            ->getMock();
        $searchCriteriaMock->expects($this->once())
            ->method('getCurrentPage')
            ->willReturn(22);
        $searchCriteriaMock->expects($this->once())
            ->method('getPageSize')
            ->willReturn(33);

        /** @var AbstractDb|MockObject $searchCriteriarMock */
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
