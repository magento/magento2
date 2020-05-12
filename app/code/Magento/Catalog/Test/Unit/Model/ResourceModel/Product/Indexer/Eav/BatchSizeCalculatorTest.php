<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product\Indexer\Eav;

use Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\BatchSizeCalculator;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Indexer\BatchSizeManagement;
use PHPUnit\Framework\TestCase;

class BatchSizeCalculatorTest extends TestCase
{
    public function testEstimateBatchSize()
    {
        $indexerId = 'default';
        $batchManagerMock = $this->createMock(BatchSizeManagement::class);
        $batchSizes = [
            $indexerId => 2000,
        ];
        $batchManagers = [
            $indexerId => $batchManagerMock,
        ];
        $model = new BatchSizeCalculator(
            $batchSizes,
            $batchManagers
        );
        $connectionMock = $this->getMockForAbstractClass(AdapterInterface::class);

        $batchManagerMock->expects($this->once())
            ->method('ensureBatchSize')
            ->with($connectionMock, $batchSizes[$indexerId]);
        $this->assertEquals($batchSizes[$indexerId], $model->estimateBatchSize($connectionMock, $indexerId));
    }

    public function testEstimateBatchSizeThrowsExceptionIfIndexerIdIsNotRecognized()
    {
        $this->expectException('Magento\Framework\Exception\NoSuchEntityException');
        $model = new BatchSizeCalculator(
            [],
            []
        );
        $connectionMock = $this->getMockForAbstractClass(AdapterInterface::class);

        $model->estimateBatchSize($connectionMock, 'wrong_indexer_id');
    }
}
