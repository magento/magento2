<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Test\Unit\DB\Query;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Query\BatchRangeIterator;
use Magento\Framework\DB\Select;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BatchRangeIteratorTest extends TestCase
{
    /**
     * @var BatchRangeIterator
     */
    private $model;

    /**
     * @var MockObject
     */
    private $selectMock;

    /**
     * @var MockObject
     */
    private $wrapperSelectMock;

    /**
     * @var MockObject
     */
    private $connectionMock;

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @var string
     */
    private $correlationName;

    /**
     * @var string
     */
    private $rangeField;

    /**
     * @var string
     */
    private $rangeFieldAlias;

    /**
     * @var int
     */
    private $currentBatch = 0;

    /**
     * Setup test dependencies.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->batchSize = 10;
        $this->currentBatch = 0;
        $this->correlationName = 'correlationName';
        $this->rangeField = 'rangeField';
        $this->rangeFieldAlias = 'rangeFieldAlias';

        $this->selectMock = $this->createMock(Select::class);
        $this->wrapperSelectMock = $this->createMock(Select::class);
        $this->connectionMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $this->connectionMock->expects($this->any())->method('select')->willReturn($this->wrapperSelectMock);
        $this->selectMock->expects($this->once())->method('getConnection')->willReturn($this->connectionMock);
        $this->connectionMock->expects($this->any())->method('quoteIdentifier')->willReturnArgument(0);

        $this->model = new BatchRangeIterator(
            $this->selectMock,
            $this->batchSize,
            $this->correlationName,
            $this->rangeField,
            $this->rangeFieldAlias
        );
    }

    /**
     * Test steps:
     * 1. $iterator->current();
     * 2. $iterator->key();
     * @return void
     */
    public function testCurrent()
    {
        $this->selectMock->expects($this->once())->method('limit')->with($this->batchSize, $this->currentBatch);
        $this->selectMock->expects($this->once())->method('order')->with('correlationName.rangeField' . ' ASC');
        $this->wrapperSelectMock->expects($this->once())
            ->method('from')
            ->with(
                $this->selectMock,
                [new \Zend_Db_Expr('COUNT(*) as cnt')]
            );
        $this->connectionMock->expects($this->once())
            ->method('fetchRow')
            ->with($this->wrapperSelectMock)
            ->willReturn(['cnt' => 10]);
        $this->assertEquals($this->selectMock, $this->model->current());
        $this->assertEquals(0, $this->model->key());
    }

    /**
     * Test the separation of batches
     */
    public function testIterations()
    {
        $iterations = 0;

        $this->connectionMock->expects($this->once())
            ->method('fetchRow')
            ->willReturn(['cnt' => 105]);

        foreach ($this->model as $key) {
            $this->assertEquals($this->selectMock, $key);
            $iterations++;
        }

        $this->assertEquals(11, $iterations);
    }
}
