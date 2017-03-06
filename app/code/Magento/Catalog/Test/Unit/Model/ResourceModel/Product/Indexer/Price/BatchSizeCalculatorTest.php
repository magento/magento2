<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product\Indexer\Price;

class BatchSizeCalculatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\BatchSizeCalculator
     */
    private $model;

    /**
     * @var \Magento\Framework\Indexer\BatchSizeCalculatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $calculatorMock;

    /**
     * @var int
     */
    private $memoryTablesMinRows;

    protected function setUp()
    {
        $this->calculatorMock = $this->getMock(\Magento\Framework\Indexer\BatchSizeCalculatorInterface::class);
        $this->memoryTablesMinRows = 200;
        $this->model = new \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\BatchSizeCalculator(
            ['default' => $this->memoryTablesMinRows],
            ['default' => $this->calculatorMock]
        );
    }

    public function testEstimateBatchSize()
    {
        $connectionMock = $this->getMock(\Magento\Framework\DB\Adapter\AdapterInterface::class);
        $typeId = 'default';
        $batchSize = 100500;

        $this->calculatorMock->expects($this->once())
            ->method('estimateBatchSize')
            ->with($connectionMock, $this->memoryTablesMinRows)
            ->willReturn($batchSize);

        $this->model->estimateBatchSize($connectionMock, $typeId);
    }
}
