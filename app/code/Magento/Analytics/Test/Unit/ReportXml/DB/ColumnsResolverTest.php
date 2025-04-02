<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Analytics\Test\Unit\ReportXml\DB;

use Magento\Analytics\ReportXml\DB\ColumnsResolver;
use Magento\Analytics\ReportXml\DB\NameResolver;
use Magento\Analytics\ReportXml\DB\SelectBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Sql\ColumnValueExpression;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ColumnsResolverTest extends TestCase
{
    /**
     * @var SelectBuilder|MockObject
     */
    private $selectBuilderMock;

    /**
     * @var ColumnsResolver
     */
    private $columnsResolver;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->selectBuilderMock = $this->createMock(SelectBuilder::class);

        $this->resourceConnectionMock = $this->createMock(ResourceConnection::class);

        $this->connectionMock = $this->getMockForAbstractClass(AdapterInterface::class);

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
        $this->resourceConnectionMock
            ->method('getConnection')
            ->willReturn($this->connectionMock);
        $this->connectionMock
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
    public static function getColumnsDataProvider()
    {
        return [
            'COUNT( DISTINCT `cpe`.`name`) AS name' => [
                'expectedColumns' => [
                    'name' => new ColumnValueExpression('COUNT( DISTINCT `cpe`.`name`)')
                ],
                'expectedGroup' => [
                    'name' => new ColumnValueExpression('COUNT( DISTINCT `cpe`.`name`)')
                ],
                'entityConfig' => [
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
                'entityConfig' => [
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
