<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Test\Unit\DB\Query;

use Magento\Framework\DB\Query\BatchIterator;
use Magento\Framework\DB\Query\BatchIteratorFactory;
use Magento\Framework\DB\Query\BatchRangeIteratorFactory;
use Magento\Framework\DB\Query\Generator;
use Magento\Framework\DB\Select;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GeneratorTest extends TestCase
{
    /**
     * @var Generator
     */
    private $model;

    /**
     * @var MockObject
     */
    private $selectMock;

    /**
     * @var MockObject
     */
    private $factoryMock;

    /**
     * @var MockObject
     */
    private $iteratorMock;

    /**
     * @var MockObject
     */
    private $rangeFactoryMock;

    /**
     * Setup test dependencies.
     */
    protected function setUp(): void
    {
        $this->factoryMock = $this->createMock(BatchIteratorFactory::class);
        $this->rangeFactoryMock = $this->createPartialMock(BatchRangeIteratorFactory::class, ['create']);
        $this->selectMock = $this->createMock(Select::class);
        $this->iteratorMock = $this->createMock(BatchIterator::class);
        $this->model = new Generator($this->factoryMock, $this->rangeFactoryMock);
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
                'rangeFieldAlias' => 'product_id'
            ]
        )->willReturn($this->iteratorMock);
        $this->assertEquals($this->iteratorMock, $this->model->generate('entity_id', $this->selectMock, 100));
    }

    /**
     * Test batch generation with invalid select object.
     *
     * @return void
     */
    public function testGenerateWithoutFromPart()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('The select object must have the correct "FROM" part. Verify and try again.');
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
                'rangeFieldAlias' => 'entity_id'
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
                'rangeFieldAlias' => 'entity_id'
            ]
        )->willReturn($this->iteratorMock);
        $this->assertEquals($this->iteratorMock, $this->model->generate('entity_id', $this->selectMock, 100));
    }

    /**
     * Test success generate with non-unique strategy.
     * @return void
     */
    public function testGenerateWithNonUniqueStrategy()
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
                'rangeFieldAlias' => 'product_id'
            ]
        )->willReturn($this->iteratorMock);
        $this->assertEquals(
            $this->iteratorMock,
            $this->model->generate('entity_id', $this->selectMock, 100, 'non_unique')
        );
    }
}
