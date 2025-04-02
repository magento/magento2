<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\DB\Test\Unit\Select;

use Magento\Framework\DB\Platform\Quote;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Select\ColumnsRenderer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ColumnsRendererTest extends TestCase
{
    /**
     * @var ColumnsRenderer
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
     * @var \Zend_Db_Expr
     */
    protected $sqlWildcard;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->quoteMock = $this->createPartialMock(Quote::class, ['quoteColumnAs']);
        $this->selectMock = $this->createPartialMock(Select::class, ['getPart']);
        $this->model = $objectManager->getObject(
            ColumnsRenderer::class,
            ['quote' => $this->quoteMock]
        );
        $this->sqlWildcard = new \Zend_Db_Expr(Select::SQL_WILDCARD);
    }

    public function testRenderNotColumns()
    {
        $this->selectMock->expects($this->once())
            ->method('getPart')
            ->with(Select::COLUMNS)
            ->willReturn([]);
        $this->assertNull($this->model->render($this->selectMock));
    }

    /**
     * @param array $columns
     * @param string $sql
     * @param string $expectedResult
     * @dataProvider renderDataProvider
     */
    public function testRender($columns, $sql, $expectedResult)
    {
        $mapValues = [
            ['column', null, '`column`'],
            [['table', 'column'], null, '`table`.`column`'],
            [['table', 'column'], 'alias', '`table`.`column` AS `alias`'],
        ];
        $this->quoteMock->expects($this->any())
            ->method('quoteColumnAs')
            ->willReturnMap($mapValues);
        $this->selectMock->expects($this->exactly(2))
            ->method('getPart')
            ->with(Select::COLUMNS)
            ->willReturn($columns);
        $this->assertEquals($expectedResult, $this->model->render($this->selectMock, $sql));
    }

    /**
     * @return array
     */
    public static function renderDataProvider()
    {
        return [
            [[['', 'column', null]], 'SELECT', 'SELECT `column`'],
            [[['table', 'column', null]], 'SELECT', 'SELECT `table`.`column`'],
            [[['table', 'column', 'alias']], 'SELECT', 'SELECT `table`.`column` AS `alias`'],
        ];
    }
}
