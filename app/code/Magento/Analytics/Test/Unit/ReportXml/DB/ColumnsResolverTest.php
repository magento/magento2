<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\ReportXml\DB;

use Magento\Analytics\ReportXml\DB\ColumnsResolver;
use Magento\Analytics\ReportXml\DB\NameResolver;
use Magento\Analytics\ReportXml\DB\SelectBuilder;
use Magento\Framework\DB\Sql\ColumnValueExpression;

/**
 * Class ColumnsResolverTest
 */
class ColumnsResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var NameResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $nameResolverMock;

    /**
     * @var SelectBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $selectBuilderMock;

    /**
     * @var ColumnsResolver
     */
    private $columnsResolver;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->nameResolverMock = $this->getMockBuilder(NameResolver::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->selectBuilderMock = $this->getMockBuilder(SelectBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->columnsResolver = new ColumnsResolver($this->nameResolverMock);
    }

    public function testGetColumnsWithoutAttributes()
    {
        $this->assertEquals($this->columnsResolver->getColumns($this->selectBuilderMock, []), []);
    }

    /**
     * @dataProvider getColumnsDataProvider
     */
    public function testGetColumns($expression, $attributeData)
    {
        $columnAlias = 'fn';
        $expr = new ColumnValueExpression($expression);
        $expectedResult = [$columnAlias => $expr];
        $columns = [$columnAlias => 'name'];
        $entityConfig['attribute'] = ['attribute1' => $attributeData];
        $this->selectBuilderMock->expects($this->once())
            ->method('getColumns')
            ->willReturn($columns);
        $this->nameResolverMock->expects($this->at(0))
            ->method('getAlias')
            ->with($attributeData)
            ->willReturn($columnAlias);
        $this->nameResolverMock->expects($this->at(1))
            ->method('getAlias')
            ->with($entityConfig)
            ->willReturn($columnAlias);
        $this->nameResolverMock->expects($this->once())
            ->method('getName')
            ->with($attributeData)
            ->willReturn('name');
        $group = ['g'];
        $this->selectBuilderMock->expects($this->once())
            ->method('getGroup')
            ->willReturn($group);
        $this->selectBuilderMock->expects($this->once())
            ->method('setGroup')
            ->with(array_merge($group, $expectedResult));
        $this->assertEquals(
            $this->columnsResolver->getColumns(
                $this->selectBuilderMock,
                $entityConfig
            ),
            $expectedResult
        );
    }

    /**
     * @return array
     */
    public function getColumnsDataProvider()
    {
        return [
            'TestWithFunction' => [
                    'expression' => "SUM( DISTINCT fn.name)",
                    'attributeData' => ['adata1', 'function' => 'SUM', 'distinct' => true, 'group' => true],
                ],
            'TestWithoutFunction' => [
                'expression' => "fn.name",
                'attributeData' => ['adata1', 'distinct' => true, 'group' => true],
            ],
        ];
    }
}
