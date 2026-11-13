<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\AsynchronousOperations\Test\Unit\Model\Operation;

use Magento\AsynchronousOperations\Model\Operation\Details;
use Magento\Framework\Bulk\BulkStatusInterface;
use Magento\Framework\Bulk\OperationInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DetailsTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $bulkStatusMock;

    /**
     * @var Details
     */
    private $model;

    protected function setUp(): void
    {
        $this->bulkStatusMock = $this->createMock(BulkStatusInterface::class);
        $this->model = new Details($this->bulkStatusMock);
    }

    public function testGetDetails()
    {
        $uuid = 'some_uuid_string';
        $completed = 100;
        $failedRetriable = 23;
        $failedNotRetriable = 45;
        $open = 303;
        $rejected = 0;

        $expectedResult = [
            'operations_total' => $completed + $failedRetriable + $failedNotRetriable + $open,
            'operations_successful' => $completed,
            'operations_failed' => $failedRetriable + $failedNotRetriable,
            'failed_retriable' => $failedRetriable,
            'failed_not_retriable' => $failedNotRetriable,
            'rejected' => $rejected,
            'open' => $open,
        ];

        $this->bulkStatusMock->method('getOperationsCountByBulkIdAndStatus')
            ->willReturnMap([
                [$uuid, OperationInterface::STATUS_TYPE_COMPLETE, $completed],
                [$uuid, OperationInterface::STATUS_TYPE_RETRIABLY_FAILED, $failedRetriable],
                [$uuid, OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED, $failedNotRetriable],
                [$uuid, OperationInterface::STATUS_TYPE_OPEN, $open],
                [$uuid, OperationInterface::STATUS_TYPE_REJECTED, $rejected],
            ]);

        $result = $this->model->getDetails($uuid);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * getDetails() is called with null when the bulk details modal is rendered without a uuid request param.
     * Null must not be used as an array key (deprecated in PHP 8.5).
     */
    public function testGetDetailsWithNullUuid(): void
    {
        $this->bulkStatusMock->expects($this->exactly(5))
            ->method('getOperationsCountByBulkIdAndStatus')
            ->with(null, $this->anything())
            ->willReturn(0);

        $expectedResult = [
            'operations_total' => 0,
            'operations_successful' => 0,
            'operations_failed' => 0,
            'failed_retriable' => 0,
            'failed_not_retriable' => 0,
            'rejected' => 0,
            'open' => 0,
        ];

        $this->assertEquals($expectedResult, $this->model->getDetails(null));
        // second call is served from the cache, no further lookups
        $this->assertEquals($expectedResult, $this->model->getDetails(null));
    }
}
