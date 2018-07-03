<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Test\Unit\Model\Operation;

use Magento\Framework\Bulk\OperationInterface;

class DetailsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $bulkStatusMock;

    /**
     * @var \Magento\AsynchronousOperations\Model\Operation\Details
     */
    private $model;

    protected function setUp()
    {
        $this->bulkStatusMock = $this->getMockBuilder(\Magento\Framework\Bulk\BulkStatusInterface::class)
            ->getMock();
        $this->model = new \Magento\AsynchronousOperations\Model\Operation\Details($this->bulkStatusMock);
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
}
