<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Test\Unit\DB\Query;

use Magento\Framework\DB\Query\BatchRangeIterator;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Adapter\AdapterInterface;

class BatchRangeIteratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BatchRangeIterator
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $selectMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $wrapperSelectMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
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
    protected function setUp()
    {
        $this->batchSize = 10;
        $this->currentBatch = 0;
        $this->correlationName = 'correlationName';
        $this->rangeField = 'rangeField';
        $this->rangeFieldAlias = 'rangeFieldAlias';

        $this->selectMock = $this->getMock(Select::class, [], [], '', false, false);
        $this->wrapperSelectMock = $this->getMock(Select::class, [], [], '', false, false);
        $this->connectionMock = $this->getMock(AdapterInterface::class);
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
        $filed = $this->correlationName . '.' . $this->rangeField;

        $this->selectMock->expects($this->once())->method('limit')->with($this->currentBatch, $this->batchSize);
        $this->selectMock->expects($this->once())->method('order')->with($filed . ' ASC');
        $this->assertEquals($this->selectMock, $this->model->current());
        $this->assertEquals(0, $this->model->key());
    }

    /**
     * Test the separation of batches
     */
    public function testIterations()
    {
        $this->connectionMock->expects($this->once())
            ->method('fetchRow')
            ->willReturn(['cnt' => 105]);

        $this->model->rewind();

        $this->assertEquals($this->selectMock, $this->model->current());
        $this->assertEquals(0, $this->model->key());
        $this->assertEquals($this->selectMock, $this->model->next());
        $this->assertTrue($this->model->valid());
        $this->assertEquals(1, $this->model->key());
    }
}
