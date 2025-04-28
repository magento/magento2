<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Analytics\Test\Unit\ReportXml\DB\Assembler;

use Magento\Analytics\ReportXml\DB\Assembler\FromAssembler;
use Magento\Analytics\ReportXml\DB\ColumnsResolver;
use Magento\Analytics\ReportXml\DB\NameResolver;
use Magento\Analytics\ReportXml\DB\SelectBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * A unit test for testing of the 'from' assembler.
 */
class FromAssemblerTest extends TestCase
{
    /**
     * @var FromAssembler
     */
    private $subject;

    /**
     * @var NameResolver|MockObject
     */
    private $nameResolverMock;

    /**
     * @var SelectBuilder|MockObject
     */
    private $selectBuilderMock;

    /**
     * @var ObjectManager
     */
    private $objectManagerHelper;

    /**
     * @var ColumnsResolver|MockObject
     */
    private $columnsResolverMock;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceConnection;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->nameResolverMock = $this->createMock(NameResolver::class);

        $this->selectBuilderMock = $this->createMock(SelectBuilder::class);
        $this->selectBuilderMock
            ->method('getColumns')
            ->willReturn([]);

        $this->columnsResolverMock = $this->createMock(ColumnsResolver::class);

        $this->resourceConnection = $this->createMock(ResourceConnection::class);

        $this->objectManagerHelper =
            new ObjectManager($this);

        $this->subject = $this->objectManagerHelper->getObject(
            FromAssembler::class,
            [
                'nameResolver' => $this->nameResolverMock,
                'columnsResolver' => $this->columnsResolverMock,
                'resourceConnection' => $this->resourceConnection,
            ]
        );
    }

    /**
     * @dataProvider assembleDataProvider
     * @param array $queryConfig
     * @param string $tableName
     * @return void
     */
    public function testAssemble(array $queryConfig, $tableName)
    {
        $this->nameResolverMock
            ->method('getAlias')
            ->with($queryConfig['source'])
            ->willReturn($queryConfig['source']['alias']);

        $this->nameResolverMock->expects($this->once())
            ->method('getName')
            ->with($queryConfig['source'])
            ->willReturn($queryConfig['source']['name']);

        $this->resourceConnection
            ->expects($this->once())
            ->method('getTableName')
            ->with($queryConfig['source']['name'])
            ->willReturn($tableName);

        $this->selectBuilderMock->expects($this->once())
            ->method('setFrom')
            ->with([$queryConfig['source']['alias'] => $tableName]);

        $this->columnsResolverMock->expects($this->once())
            ->method('getColumns')
            ->with($this->selectBuilderMock, $queryConfig['source'])
            ->willReturn(['entity_id' => 'sales.entity_id']);

        $this->selectBuilderMock->expects($this->once())
            ->method('setColumns')
            ->with(['entity_id' => 'sales.entity_id']);

        $this->assertEquals(
            $this->selectBuilderMock,
            $this->subject->assemble($this->selectBuilderMock, $queryConfig)
        );
    }

    /**
     * @return array
     */
    public static function assembleDataProvider()
    {
        return [
            'Tables without prefixes' => [
                [
                    'source' => [
                        'name' => 'sales_order',
                        'alias' => 'sales',
                        'attribute' => [
                            [
                                'name' => 'entity_id'
                            ]
                        ],
                    ],
                ],
                'sales_order',
            ],
            'Tables with prefixes' => [
                [
                    'source' => [
                        'name' => 'sales_order',
                        'alias' => 'sales',
                        'attribute' => [
                            [
                                'name' => 'entity_id'
                            ]
                        ],
                    ],
                ],
                'pref_sales_order',
            ]
        ];
    }
}
