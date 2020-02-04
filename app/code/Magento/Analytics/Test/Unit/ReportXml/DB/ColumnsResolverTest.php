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
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class ColumnsResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SelectBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $selectBuilderMock;

    /**
     * @var ColumnsResolver
     */
    private $columnsResolver;

    /**
     * @var ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connectionMock;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->selectBuilderMock = $this->getMockBuilder(SelectBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resourceConnectionMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->connectionMock = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManagerHelper($this);
        $this->columnsResolver = $objectManager->getObject(
            ColumnsResolver::class,
            [
                'nameResolver' => new NameResolver(),
                'resourceConnection' => $this->resourceConnectionMock
            ]
        );
    }

    public function testGetColumnsWithoutAttributes()
    {
        $this->assertEquals($this->columnsResolver->getColumns($this->selectBuilderMock, []), []);
    }

    /**
     * @dataProvider getColumnsDataProvider
     */
    public function testGetColumnsWithFunction($expectedColumns, $expectedGroup, $entityConfig)
    {
        $this->resourceConnectionMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionMock);
        $this->connectionMock->expects($this->any())
            ->method('quoteIdentifier')
            ->with('cpe.name')
            ->willReturn('`cpe`.`name`');
        $this->selectBuilderMock->expects($this->once())
            ->method('getColumns')
            ->willReturn([]);
        $this->selectBuilderMock->expects($this->once())
            ->method('getGroup')
            ->willReturn([]);
        $this->selectBuilderMock->expects($this->once())
            ->method('setGroup')
            ->with($expectedGroup);
        $this->assertEquals(
            $expectedColumns,
            $this->columnsResolver->getColumns(
                $this->selectBuilderMock,
                $entityConfig
            )
        );
    }

    /**
     * @return array
     */
    public function getColumnsDataProvider()
    {
        return [
            'COUNT( DISTINCT `cpe`.`name`) AS name' => [
                    'expectedColumns' => [
                        'name' => new ColumnValueExpression('COUNT( DISTINCT `cpe`.`name`)')
                    ],
                    'expectedGroup' => [
                        'name' => new ColumnValueExpression('COUNT( DISTINCT `cpe`.`name`)')
                    ],
                    'entityConfig' =>
                        [
                            'name' => 'catalog_product_entity',
                            'alias' => 'cpe',
                            'attribute' => [
                                [
                                    'name' => 'name',
                                    'function' => 'COUNT',
                                    'distinct' => true,
                                    'group' => true
                                ]
                            ],
                        ],
                    ],
            'AVG(`cpe`.`name`) AS avg_name' => [
                'expectedColumns' => [
                    'avg_name' => new ColumnValueExpression('AVG(`cpe`.`name`)')
                ],
                'expectedGroup' => [],
                'entityConfig' =>
                    [
                        'name' => 'catalog_product_entity',
                        'alias' => 'cpe',
                        'attribute' => [
                            [
                                'name' => 'name',
                                'alias' => 'avg_name',
                                'function' => 'AVG',
                            ]
                        ],
                    ],
            ]
        ];
    }
}
