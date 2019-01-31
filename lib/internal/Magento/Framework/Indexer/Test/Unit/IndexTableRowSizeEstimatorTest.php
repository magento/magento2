<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Indexer\Test\Unit;

class IndexTableRowSizeEstimatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test for estimateRowSize method
     */
    public function testEstimateRowSize()
    {
        $rowMemorySize = 100;
        $model = new \Magento\Framework\Indexer\IndexTableRowSizeEstimator($rowMemorySize);
        $this->assertEquals($model->estimateRowSize(), $rowMemorySize);
    }
}
