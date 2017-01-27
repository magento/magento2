<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Test\Unit\DB\Query;

use Magento\Framework\DB\Query\Generator;
use Magento\Framework\DB\Query\BatchIteratorFactory;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Query\BatchIterator;

class GeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Generator
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $selectMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $factoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $iteratorMock;

    /**
     * Setup test dependencies.
     */
    protected function setUp()
    {
        $this->factoryMock = $this->getMock(BatchIteratorFactory::class, [], [], '', false, false);
        $this->selectMock = $this->getMock(Select::class, [], [], '', false, false);
        $this->iteratorMock = $this->getMock(BatchIterator::class, [], [], '', false, false);
        $this->model = new Generator($this->factoryMock);
    }

    /**
     * Test success generate.
     * @return void
     */
    public function testGenerate()
    {
        $map = [
            [
                Select::FROM,
                [
                    'cp' => ['joinType' => Select::FROM]
                ]
            ],
            [
                Select::COLUMNS,
                [
                    ['cp', 'entity_id', 'product_id']
                ]
            ]
        ];
        $this->selectMock->expects($this->exactly(2))->method('getPart')->willReturnMap($map);
        $this->factoryMock->expects($this->once())->method('create')->with(
            [
                'select' => $this->selectMock,
                'batchSize' => 100,
                'correlationName' => 'cp',
                'rangeField' => 'entity_id',
                'rangeFieldAlias' => 'product_id',
                'batchStrategy' => 'unique'
            ]
        )->willReturn($this->iteratorMock);
        $this->assertEquals($this->iteratorMock, $this->model->generate('entity_id', $this->selectMock, 100));
    }

    /**
     * Test batch generation with invalid select object.
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage  Select object must have correct "FROM" part
     * @return void
     */
    public function testGenerateWithoutFromPart()
    {
        $map = [
            [Select::FROM, []],
            [
                Select::COLUMNS,
                [
                    ['cp', 'entity_id', 'product_id']
                ]
            ]
        ];
        $this->selectMock->expects($this->any())->method('getPart')->willReturnMap($map);
        $this->factoryMock->expects($this->never())->method('create');
        $this->model->generate('entity_id', $this->selectMock, 100);
    }

    /**
     * Test generate batches with rangeField without alias.
     * @return void
     */
    public function testGenerateWithRangeFieldWithoutAlias()
    {
        $map = [
            [
                Select::FROM,
                [
                    'cp' => ['joinType' => Select::FROM]
                ]
            ],
            [
                Select::COLUMNS,
                [
                    ['cp', 'entity_id', null]
                ]
            ]
        ];
        $this->selectMock->expects($this->exactly(2))->method('getPart')->willReturnMap($map);
        $this->factoryMock->expects($this->once())->method('create')->with(
            [
                'select' => $this->selectMock,
                'batchSize' => 100,
                'correlationName' => 'cp',
                'rangeField' => 'entity_id',
                'rangeFieldAlias' => 'entity_id',
                'batchStrategy' => 'unique'
            ]
        )->willReturn($this->iteratorMock);
        $this->assertEquals($this->iteratorMock, $this->model->generate('entity_id', $this->selectMock, 100));
    }

    /**
     * Test generate batches with wild-card.
     *
     * @return void
     */
    public function testGenerateWithInvalidWithWildcard()
    {
        $map = [
            [
                Select::FROM,
                [
                    'cp' => ['joinType' => Select::FROM]
                ]
            ],
            [
                Select::COLUMNS,
                [
                    ['cp', '*', null]
                ]
            ]
        ];
        $this->selectMock->expects($this->exactly(2))->method('getPart')->willReturnMap($map);
        $this->factoryMock->expects($this->once())->method('create')->with(
            [
                'select' => $this->selectMock,
                'batchSize' => 100,
                'correlationName' => 'cp',
                'rangeField' => 'entity_id',
                'rangeFieldAlias' => 'entity_id',
                'batchStrategy' => 'unique'
            ]
        )->willReturn($this->iteratorMock);
        $this->assertEquals($this->iteratorMock, $this->model->generate('entity_id', $this->selectMock, 100));
    }
}
