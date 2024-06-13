<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Indexer\Test\Unit;

use Magento\Framework\Indexer\IndexTableRowSizeEstimator;
use PHPUnit\Framework\TestCase;

class IndexTableRowSizeEstimatorTest extends TestCase
{
    /**
     * Test for estimateRowSize method
     */
    public function testEstimateRowSize()
    {
        $rowMemorySize = 100;
        $model = new IndexTableRowSizeEstimator($rowMemorySize);
        $this->assertEquals($model->estimateRowSize(), $rowMemorySize);
    }
}
