<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\Test\Unit\SearchCriteria;

use Magento\Framework\Api\SearchCriteria\CollectionProcessor;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Data\Collection\AbstractDb;

class CollectionProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Return model
     *
     * @param CollectionProcessorInterface[] $processors
     * @return CollectionProcessor
     */
    private function getModel(array $processors)
    {
        return new CollectionProcessor($processors);
    }

    public function testProcess()
    {
        /** @var CollectionProcessorInterface|\PHPUnit\Framework\MockObject\MockObject $customFilterMock */
        $processorOneMock = $this->getMockBuilder(CollectionProcessorInterface::class)
            ->getMock();

        /** @var CollectionProcessorInterface|\PHPUnit\Framework\MockObject\MockObject $processorTwoMock */
        $processorTwoMock = $this->getMockBuilder(CollectionProcessorInterface::class)
            ->getMock();

        $processors = [$processorOneMock, $processorTwoMock];

        $model = $this->getModel($processors);

        /** @var SearchCriteriaInterface|\PHPUnit\Framework\MockObject\MockObject $searchCriteriaMock */
        $searchCriteriaMock = $this->getMockBuilder(SearchCriteriaInterface::class)
            ->getMock();

        /** @var AbstractDb|\PHPUnit\Framework\MockObject\MockObject $searchCriteriarMock */
        $collectionMock = $this->getMockBuilder(AbstractDb::class)
            ->disableOriginalConstructor()
            ->getMock();

        $processorOneMock->expects($this->once())
            ->method('process')
            ->with($searchCriteriaMock, $collectionMock);

        $processorTwoMock->expects($this->once())
            ->method('process')
            ->with($searchCriteriaMock, $collectionMock);

        $model->process($searchCriteriaMock, $collectionMock);
    }

    /**
     */
    public function testProcessWithException()
    {
        $this->expectException(\InvalidArgumentException::class);

        /** @var CollectionProcessorInterface|\PHPUnit\Framework\MockObject\MockObject $customFilterMock */
        $processorOneMock = $this->getMockBuilder(CollectionProcessorInterface::class)
            ->setMethods(['process'])
            ->getMockForAbstractClass();

        /** @var \stdClass|\PHPUnit\Framework\MockObject\MockObject $processorTwoMock */
        $processorTwoMock = $this->getMockBuilder(\stdClass::class)
            ->setMethods(['process'])
            ->getMock();

        $processors = [$processorOneMock, $processorTwoMock];

        $model = $this->getModel($processors);

        /** @var SearchCriteriaInterface|\PHPUnit\Framework\MockObject\MockObject $searchCriteriaMock */
        $searchCriteriaMock = $this->getMockBuilder(SearchCriteriaInterface::class)
            ->getMock();

        /** @var AbstractDb|\PHPUnit\Framework\MockObject\MockObject $searchCriteriarMock */
        $collectionMock = $this->getMockBuilder(AbstractDb::class)
            ->disableOriginalConstructor()
            ->getMock();

        $processorOneMock->expects($this->once())
            ->method('process')
            ->with($searchCriteriaMock, $collectionMock);

        $processorTwoMock->expects($this->never())
            ->method('process');

        $model->process($searchCriteriaMock, $collectionMock);
    }
}
