<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\DB\Test\Unit\Platform;

use Magento\Framework\DB\Select;

/**
 * Class QuoteTest
 */
class QuoteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\DB\Platform\Quote
     */
    protected $model;

    /**
     * @var \Zend_Db_Expr|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $zendDbExprMock;

    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $selectMock;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject('\Magento\Framework\DB\Platform\Quote');
        $this->zendDbExprMock = $this->getMock('Zend_Db_Expr', ['__toString'], [], '', false);
        $this->selectMock = $this->getMock('\Magento\Framework\DB\Select', ['assemble'], [], '', false);
    }

    public function testQuoteIdentifierWithZendDbExpr()
    {
        $quoted = 'string';
        $this->zendDbExprMock->expects($this->once())
            ->method('__toString')
            ->willReturn($quoted);
        $this->assertEquals($quoted, $this->model->quoteIdentifier($this->zendDbExprMock));
    }

    public function testQuoteIdentifierWithSelect()
    {
        $quoted = 'string';
        $expectedResult = '(' . $quoted . ')';
        $this->selectMock->expects($this->once())
            ->method('assemble')
            ->willReturn($quoted);
        $this->assertEquals($expectedResult, $this->model->quoteIdentifier($this->selectMock));
    }

    public function testQuoteIdentifierWithArrayExpr()
    {
        $identifier = [$this->zendDbExprMock, $this->zendDbExprMock];
        $expectedResult = 'string1.string2';
        $this->zendDbExprMock->expects($this->exactly(2))
            ->method('__toString')
            ->will($this->onConsecutiveCalls('string1', 'string2'));
        $this->assertEquals($expectedResult, $this->model->quoteIdentifier($identifier));
    }

    /**
     * @param string|array $identifier
     * @param string $expectedResult
     * @dataProvider getStringArrayToQuoteDataProvider
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function testQuoteIdentifier($identifier, $alias, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->model->quoteIdentifier($identifier));
    }

    /**
     * @param string $string
     * @param string|null $alias
     * @param string $expectedResult
     * @dataProvider getExpressionToQuoteDataProvider
     */
    public function testQuoteColumnAsWithZendDbExpr($string, $alias, $expectedResult)
    {
        $this->zendDbExprMock->expects($this->once())
            ->method('__toString')
            ->willReturn($string);
        $this->assertEquals($expectedResult, $this->model->quoteColumnAs($this->zendDbExprMock, $alias));
    }

    /**
     * @param string $string
     * @param string|null $alias
     * @param string $expectedResult
     * @dataProvider getSelectToQuoteDataProvider
     */
    public function testQuoteColumnAsWithSelect($string, $alias, $expectedResult)
    {
        $this->selectMock->expects($this->once())
            ->method('assemble')
            ->willReturn($string);
        $this->assertEquals($expectedResult, $this->model->quoteColumnAs($this->selectMock, $alias));
    }

    /**
     * @param string|array $identifier
     * @param string $expectedResult
     * @dataProvider getStringArrayToQuoteWithAliasDataProvider
     */
    public function testQuoteColumn($identifier, $alias, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->model->quoteColumnAs($identifier, $alias));
    }

    /**
     * @param string $string
     * @param string|null $alias
     * @param string $expectedResult
     * @dataProvider getExpressionToQuoteDataProvider
     */
    public function testQuoteTableAsWithZendDbExpr($string, $alias, $expectedResult)
    {
        $this->zendDbExprMock->expects($this->once())
            ->method('__toString')
            ->willReturn($string);
        $this->assertEquals($expectedResult, $this->model->quoteTableAs($this->zendDbExprMock, $alias));
    }

    /**
     * @param string $string
     * @param string|null $alias
     * @param string $expectedResult
     * @dataProvider getSelectToQuoteDataProvider
     */
    public function testQuoteTableAsWithSelect($string, $alias, $expectedResult)
    {
        $this->selectMock->expects($this->once())
            ->method('assemble')
            ->willReturn($string);
        $this->assertEquals($expectedResult, $this->model->quoteTableAs($this->selectMock, $alias));
    }

    /**
     * @param string|array $identifier
     * @param string $expectedResult
     * @dataProvider getStringArrayToQuoteWithAliasDataProvider
     */
    public function testQuoteTableAs($identifier, $alias, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->model->quoteTableAs($identifier, $alias));
    }

    /**
     * @return array
     */
    public function getExpressionToQuoteDataProvider()
    {
        return [
            ['string', null, 'string'],
            ['string', 'alias', 'string ' . Select::SQL_AS . ' `alias`'],
            ['string', '`alias`', 'string ' . Select::SQL_AS . ' ```alias```'],
            ['string', '!@#$%^&*()_+"\'`', 'string ' . Select::SQL_AS . ' `!@#$%^&*()_+"\'```'],
        ];
    }

    /**
     * @return array
     */
    public function getSelectToQuoteDataProvider()
    {
        return [
            ['string', null, '(string)'],
            ['string', 'alias', '(string) ' . Select::SQL_AS . ' `alias`'],
            ['string', '`alias`', '(string) ' . Select::SQL_AS . ' ```alias```'],
            ['string', '!@# $%^&*()_+"\'``', '(string) ' . Select::SQL_AS . ' `!@# $%^&*()_+"\'`````'],
        ];
    }

    /**
     * @return array
     */
    public function getStringArrayToQuoteDataProvider()
    {
        return [
            ['some string', null, '`some string`'],
            ['`some string`', null, '```some string```'],
            ['some.string', null, '`some`.`string`'],
            ['`some.string`', null, '```some`.`string```'],
            [['`some`', '`string`'], null, '```some```.```string```']
        ];
    }

    /**
     * @return array
     */
    public function getStringArrayToQuoteWithAliasDataProvider()
    {
        $variations = $this->getStringArrayToQuoteDataProvider();
        return array_merge($variations, [
            ['string', 'alias', '`string` ' . Select::SQL_AS . ' `alias`'],
            ['alias.string', 'alias', '`alias`.`string` ' . Select::SQL_AS . ' `alias`'],
            ['table.column', 'column', '`table`.`column`'],
            [['`table`', '`column`'], 'alias', '```table```.```column``` ' . Select::SQL_AS . ' `alias`']
        ]);
    }
}
