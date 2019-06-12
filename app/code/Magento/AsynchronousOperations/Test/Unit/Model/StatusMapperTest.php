<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Test\Unit\Model;

use Magento\Framework\Bulk\OperationInterface;
use Magento\Framework\Bulk\BulkSummaryInterface;

/**
 * Class StatusMapperTest
 */
class StatusMapperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\AsynchronousOperations\Model\StatusMapper
     */
    private $model;

    protected function setUp()
    {
        $this->model = new \Magento\AsynchronousOperations\Model\StatusMapper();
    }

    public function testOperationStatusToBulkSummaryStatus()
    {
        $this->assertEquals(
            $this->model->operationStatusToBulkSummaryStatus(OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED),
            BulkSummaryInterface::FINISHED_WITH_FAILURE
        );

        $this->assertEquals(
            $this->model->operationStatusToBulkSummaryStatus(OperationInterface::STATUS_TYPE_RETRIABLY_FAILED),
            BulkSummaryInterface::FINISHED_WITH_FAILURE
        );

        $this->assertEquals(
            $this->model->operationStatusToBulkSummaryStatus(OperationInterface::STATUS_TYPE_COMPLETE),
            BulkSummaryInterface::FINISHED_SUCCESSFULLY
        );

        $this->assertEquals(
            $this->model->operationStatusToBulkSummaryStatus(OperationInterface::STATUS_TYPE_OPEN),
            BulkSummaryInterface::IN_PROGRESS
        );

        $this->assertEquals(
            $this->model->operationStatusToBulkSummaryStatus(0),
            BulkSummaryInterface::NOT_STARTED
        );
    }

    public function testOperationStatusToBulkSummaryStatusWithUnknownStatus()
    {
        $this->assertNull($this->model->operationStatusToBulkSummaryStatus('unknown_status'));
    }

    public function testBulkSummaryStatusToOperationStatus()
    {
        $this->assertEquals(
            $this->model->bulkSummaryStatusToOperationStatus(BulkSummaryInterface::FINISHED_SUCCESSFULLY),
            OperationInterface::STATUS_TYPE_COMPLETE
        );

        $this->assertEquals(
            $this->model->bulkSummaryStatusToOperationStatus(BulkSummaryInterface::IN_PROGRESS),
            OperationInterface::STATUS_TYPE_OPEN
        );

        $this->assertEquals(
            $this->model->bulkSummaryStatusToOperationStatus(BulkSummaryInterface::FINISHED_WITH_FAILURE),
            [
                OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED,
                OperationInterface::STATUS_TYPE_RETRIABLY_FAILED,
                OperationInterface::STATUS_TYPE_REJECTED
            ]
        );

        $this->assertEquals(
            $this->model->bulkSummaryStatusToOperationStatus(BulkSummaryInterface::NOT_STARTED),
            0
        );
    }

    public function testBulkSummaryStatusToOperationStatusWithUnknownStatus()
    {
        $this->assertNull($this->model->bulkSummaryStatusToOperationStatus('unknown_status'));
    }
}
