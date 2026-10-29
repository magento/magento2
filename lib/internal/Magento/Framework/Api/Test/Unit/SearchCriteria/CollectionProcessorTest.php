<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Api\Test\Unit\SearchCriteria;

use Magento\Framework\Api\SearchCriteria\CollectionProcessor;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

class CollectionProcessorTest extends TestCase
{
    use MockCreationTrait;
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
        /** @var CollectionProcessorInterface|MockObject $customFilterMock */
        $processorOneMock = $this->getMockBuilder(CollectionProcessorInterface::class)
            ->getMock();

        /** @var CollectionProcessorInterface|MockObject $processorTwoMock */
        $processorTwoMock = $this->getMockBuilder(CollectionProcessorInterface::class)
            ->getMock();

        $processors = [$processorOneMock, $processorTwoMock];

        $model = $this->getModel($processors);

        /** @var SearchCriteriaInterface|MockObject $searchCriteriaMock */
        $searchCriteriaMock = $this->getMockBuilder(SearchCriteriaInterface::class)
            ->getMock();

        /** @var AbstractDb|MockObject $searchCriteriarMock */
        $collectionMock = $this->getMockBuilder(AbstractDb::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getResource'])
            ->getMock();

        $processorOneMock->expects($this->once())
            ->method('process')
            ->with($searchCriteriaMock, $collectionMock);

        $processorTwoMock->expects($this->once())
            ->method('process')
            ->with($searchCriteriaMock, $collectionMock);

        $model->process($searchCriteriaMock, $collectionMock);
    }

    public function testProcessWithException()
    {
        $this->expectException('InvalidArgumentException');
        /** @var CollectionProcessorInterface|MockObject $customFilterMock */
        $processorOneMock = $this->createMock(CollectionProcessorInterface::class);

        /** @var \stdClass|MockObject $processorTwoMock */
        $processorTwoMock = $this->createPartialMockWithReflection(\stdClass::class, ['process']);

        $processors = [$processorOneMock, $processorTwoMock];

        $model = $this->getModel($processors);

        /** @var SearchCriteriaInterface|MockObject $searchCriteriaMock */
        $searchCriteriaMock = $this->getMockBuilder(SearchCriteriaInterface::class)
            ->getMock();

        /** @var AbstractDb|MockObject $searchCriteriarMock */
        $collectionMock = $this->getMockBuilder(AbstractDb::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getResource'])
            ->getMock();

        $processorOneMock->expects($this->once())
            ->method('process')
            ->with($searchCriteriaMock, $collectionMock);

        $processorTwoMock->expects($this->never())
            ->method('process');

        $model->process($searchCriteriaMock, $collectionMock);
    }
}
