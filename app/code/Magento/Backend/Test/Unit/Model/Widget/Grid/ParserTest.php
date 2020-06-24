<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Model\Widget\Grid;

use Magento\Backend\Model\Widget\Grid\Parser;
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{
    /**
     * @var Parser
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_model = new Parser();
    }

    /**
     * @param string $expression
     * @param array $expected
     * @dataProvider parseExpressionDataProvider
     */
    public function testParseExpression($expression, $expected)
    {
        $this->assertEquals($expected, $this->_model->parseExpression($expression));
    }

    /**
     * @return array
     */
    public function parseExpressionDataProvider()
    {
        return [
            ['1-2', ['1', '2', '-']],
            ['1*2', ['1', '2', '*']],
            ['1/2', ['1', '2', '/']],
            ['1+2+3', ['1', '2', '+', '3', '+']],
            ['1*2*3+4', ['1', '2', '*', '3', '*', '4', '+']],
            ['1-2-3', ['1', '2', '-', '3', '-']],
            ['1*2*3', ['1', '2', '*', '3', '*']],
            ['1/2/3', ['1', '2', '/', '3', '/']],
            [
                '1 * 2 / 3 + 4 * 5 * 6 - 7 - 8',
                ['1', '2', '*', '3', '/', '4', '5', '*', '6', '*', '+', '7', '-', '8', '-']
            ]
        ];
    }

    /**
     * @param $operation
     * @param $expected
     * @dataProvider isOperationDataProvider
     */
    public function testIsOperation($operation, $expected)
    {
        $this->assertEquals($expected, $this->_model->isOperation($operation));
    }

    /**
     * @return array
     */
    public function isOperationDataProvider()
    {
        return [
            ['+', true],
            ['-', true],
            ['*', true],
            ['/', true],
            ['0', false],
            ['aa', false]
        ];
    }
}
