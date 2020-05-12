<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Test\Unit\DB\Query;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Query\BatchIterator;
use Magento\Framework\DB\Select;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BatchIteratorTest extends TestCase
{
    /**
     * @var BatchIterator
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
     * Setup test dependencies.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->batchSize = 10;
        $this->correlationName = 'correlationName';
        $this->rangeField = 'rangeField';
        $this->rangeFieldAlias = 'rangeFieldAlias';

        $this->selectMock = $this->createMock(Select::class);
        $this->wrapperSelectMock = $this->createMock(Select::class);
        $this->connectionMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $this->connectionMock->expects($this->any())->method('select')->willReturn($this->wrapperSelectMock);
        $this->selectMock->expects($this->once())->method('getConnection')->willReturn($this->connectionMock);
        $this->connectionMock->expects($this->any())->method('quoteIdentifier')->willReturnArgument(0);

        $this->model = new BatchIterator(
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
     * 2. $iterator->current();
     * 3. $iterator->key();
     * @return void
     */
    public function testCurrent()
    {
        $filed = $this->correlationName . '.' . $this->rangeField;

        $this->selectMock->expects($this->once())->method('where')->with($filed . ' > ?', 0);
        $this->selectMock->expects($this->once())->method('limit')->with($this->batchSize);
        $this->selectMock->expects($this->once())->method('order')->with($filed . ' ASC');
        $this->wrapperSelectMock->expects($this->once())
            ->method('from')
            ->with(
                $this->selectMock,
                [
                    new \Zend_Db_Expr('MAX(' . $this->rangeFieldAlias . ') as max'),
                    new \Zend_Db_Expr('COUNT(*) as cnt'),
                ]
            );
        $this->connectionMock->expects($this->once())
            ->method('fetchRow')
            ->with($this->wrapperSelectMock)
            ->willReturn(['max' => 10, 'cnt' => 10]);
        $this->assertEquals($this->selectMock, $this->model->current());
        $this->assertEquals($this->selectMock, $this->model->current());
        $this->assertEquals(0, $this->model->key());
    }

    /**
     * SQL: select * from users
     * Batch size: 10
     * IDS: [1 - 25]
     * Items count: 25
     * Expected batches: [1-10, 11-20, 20-25]
     *
     * Test steps:
     * 1. $iterator->rewind();
     * 2. $iterator->valid();
     * 3. $iterator->current();
     * 4. $iterator->key();
     *
     * 1. $iterator->next()
     * 2. $iterator->valid();
     * 3. $iterator->current();
     * 4. $iterator->key();
     *
     * 1. $iterator->next()
     * 2. $iterator->valid();
     * 3. $iterator->current();
     * 4. $iterator->key();
     *
     *
     * 1. $iterator->next()
     * 2. $iterator->valid();
     * @return void
     */
    public function testIterations()
    {
        $startCallIndex = 3;
        $stepCall = 4;

        $this->connectionMock->expects($this->at($startCallIndex))
            ->method('fetchRow')
            ->willReturn(['max' => 10, 'cnt' => 10]);

        $this->connectionMock->expects($this->at($startCallIndex += $stepCall))
            ->method('fetchRow')
            ->willReturn(['max' => 20, 'cnt' => 10]);

        $this->connectionMock->expects($this->at($startCallIndex += $stepCall))
            ->method('fetchRow')
            ->willReturn(['max' => 25, 'cnt' => 5]);

        $this->connectionMock->expects($this->at($startCallIndex += $stepCall))
            ->method('fetchRow')
            ->willReturn(['max' => null, 'cnt' => 0]);

        /**
         * Test 3 iterations
         * [1-10, 11-20, 20-25]
         */
        $iteration = 0;
        $result = [];
        foreach ($this->model as $key => $select) {
            $result[] = $select;
            $this->assertEquals($iteration, $key);
            $iteration++;
        }
        $this->assertCount(3, $result);
    }

    /**
     * Test steps:
     * 1. $iterator->next();
     * 2. $iterator->key()
     * 3. $iterator->next();
     * 4. $iterator->current()
     * 5. $iterator->key()
     * @return void
     */
    public function testNext()
    {
        $filed = $this->correlationName . '.' . $this->rangeField;
        $this->selectMock->expects($this->at(0))->method('where')->with($filed . ' > ?', 0);
        $this->selectMock->expects($this->exactly(3))->method('limit')->with($this->batchSize);
        $this->selectMock->expects($this->exactly(3))->method('order')->with($filed . ' ASC');
        $this->selectMock->expects($this->at(3))->method('where')->with($filed . ' > ?', 25);

        $this->wrapperSelectMock->expects($this->exactly(3))->method('from')->with(
            $this->selectMock,
            [
                new \Zend_Db_Expr('MAX(' . $this->rangeFieldAlias . ') as max'),
                new \Zend_Db_Expr('COUNT(*) as cnt')
            ]
        );
        $this->connectionMock->expects($this->exactly(3))
            ->method('fetchRow')
            ->with($this->wrapperSelectMock)
            ->willReturn(['max' => 25, 'cnt' => 10]);

        $this->assertEquals($this->selectMock, $this->model->next());
        $this->assertEquals(1, $this->model->key());

        $this->assertEquals($this->selectMock, $this->model->next());
        $this->assertEquals($this->selectMock, $this->model->current());
        $this->assertEquals(2, $this->model->key());
    }
}
