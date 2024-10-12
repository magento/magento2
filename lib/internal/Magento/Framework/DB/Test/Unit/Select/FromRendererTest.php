<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\DB\Test\Unit\Select;

use Magento\Framework\DB\Platform\Quote;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Select\FromRenderer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FromRendererTest extends TestCase
{
    /**
     * @var FromRenderer
     */
    protected $model;

    /**
     * @var Quote|MockObject
     */
    protected $quoteMock;

    /**
     * @var Select|MockObject
     */
    protected $selectMock;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->quoteMock =
            $this->createPartialMock(Quote::class, ['quoteTableAs', 'quoteIdentifier']);
        $this->selectMock = $this->createPartialMock(Select::class, ['getPart']);
        $this->model = $objectManager->getObject(
            FromRenderer::class,
            ['quote' => $this->quoteMock]
        );
    }

    public function testRenderNoPart()
    {
        $sql = 'SELECT';
        $this->selectMock->expects($this->once())
            ->method('getPart')
            ->with(Select::FROM)
            ->willReturn([]);
        $this->assertEquals($sql, $this->model->render($this->selectMock, $sql));
    }

    /**
     * @param array $from
     * @param string $sql
     * @param string $expectedResult
     * @dataProvider renderDataProvider
     */
    public function testRender($from, $sql, $expectedResult)
    {
        $this->quoteMock->expects($this->any())
            ->method('quoteIdentifier')
            ->willReturnArgument(0);
        $this->quoteMock->expects($this->any())
            ->method('quoteTableAs')
            ->willReturnCallback(
                function ($tableName, $correlationName) {
                    return $tableName . ' AS ' . $correlationName;
                }
            );
        $this->selectMock->expects($this->once())
            ->method('getPart')
            ->with(Select::FROM)
            ->willReturn($from);
        $this->assertEquals($expectedResult, $this->model->render($this->selectMock, $sql));
    }

    /**
     * Data provider for testRender
     * @return array
     */
    public static function renderDataProvider()
    {
        return [
            [
                [['joinType' => Select::FROM, 'schema' => null, 'tableName' => 't1', 'joinCondition' => null]],
                'SELECT *',
                'SELECT * FROM t1 AS 0'
            ],
            [
                [
                    'a' => ['joinType' => Select::FROM, 'schema' => null, 'tableName' => 't1', 'joinCondition' => null],
                    'b' => ['joinType' => Select::FROM, 'schema' => null, 'tableName' => 't2', 'joinCondition' => null]
                ],
                'SELECT a.*',
                'SELECT a.* FROM t1 AS a' . "\n" . ' INNER JOIN t2 AS b'
            ],
            [
                [
                    'a' => ['joinType' => Select::FROM, 'schema' => null, 'tableName' => 't1', 'joinCondition' => null],
                    'b' => [
                        'joinType' => Select::LEFT_JOIN,
                        'schema' => 'db',
                        'tableName' => 't2',
                        'joinCondition' => 't1.f1 = t2.f2'
                    ]
                ],
                'SELECT b.f2',
                'SELECT b.f2 FROM t1 AS a' . "\n" . ' LEFT JOIN db.t2 AS b ON t1.f1 = t2.f2'
            ]
        ];
    }
}
