<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Analytics\Test\Unit\ReportXml\DB\Assembler;

use Magento\Analytics\ReportXml\DB\Assembler\JoinAssembler;
use Magento\Analytics\ReportXml\DB\ColumnsResolver;
use Magento\Analytics\ReportXml\DB\ConditionResolver;
use Magento\Analytics\ReportXml\DB\NameResolver;
use Magento\Analytics\ReportXml\DB\SelectBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * A unit test for testing of the 'join' assembler.
 */
class JoinAssemblerTest extends TestCase
{
    /**
     * @var JoinAssembler
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
     * @var ConditionResolver|MockObject
     */
    private $conditionResolverMock;

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
            ->method('getFilters')
            ->willReturn([]);
        $this->selectBuilderMock
            ->method('getColumns')
            ->willReturn([]);
        $this->selectBuilderMock
            ->method('getJoins')
            ->willReturn([]);

        $this->columnsResolverMock = $this->createMock(ColumnsResolver::class);

        $this->conditionResolverMock = $this->createMock(ConditionResolver::class);

        $this->resourceConnection = $this->createMock(ResourceConnection::class);

        $this->objectManagerHelper =
            new ObjectManager($this);

        $this->subject = $this->objectManagerHelper->getObject(
            JoinAssembler::class,
            [
                'conditionResolver' => $this->conditionResolverMock,
                'nameResolver' => $this->nameResolverMock,
                'columnsResolver' => $this->columnsResolverMock,
                'resourceConnection' => $this->resourceConnection
            ]
        );
    }

    /**
     * @return void
     */
    public function testAssembleEmpty(): void
    {
        $queryConfigMock = [
            'source' => [
                'name' => 'sales_order',
                'alias' => 'sales'
            ]
        ];

        $this->selectBuilderMock->expects($this->never())
            ->method('setColumns');
        $this->selectBuilderMock->expects($this->never())
            ->method('setFilters');
        $this->selectBuilderMock->expects($this->never())
            ->method('setJoins');

        $this->assertEquals(
            $this->selectBuilderMock,
            $this->subject->assemble($this->selectBuilderMock, $queryConfigMock)
        );
    }

    /**
     * @param array $queryConfigMock
     * @param array $joinsMock
     * @param array $tablesMapping
     *
     * @return void
     * @dataProvider assembleNotEmptyDataProvider
     */
    public function testAssembleNotEmpty(array $queryConfigMock, array $joinsMock, array $tablesMapping): void
    {
        $filtersMock = [];

        $this->nameResolverMock
            ->method('getAlias')
            ->willReturnCallback(function ($arg1) use ($queryConfigMock) {
                if ($arg1 == $queryConfigMock['source']) {
                    return $queryConfigMock['source']['alias'];
                } elseif ($arg1 == $queryConfigMock['source']['link-source'][0]) {
                    return $queryConfigMock['source']['link-source'][0]['alias'];
                }
            });
        $this->nameResolverMock->expects($this->once())
            ->method('getName')
            ->with($queryConfigMock['source']['link-source'][0])
            ->willReturn($queryConfigMock['source']['link-source'][0]['name']);

        $this->resourceConnection
            ->method('getTableName')
            ->willReturnOnConsecutiveCalls(...array_values($tablesMapping));

        $withArgs = $willReturnArgs = [];
        $withArgs[] = [
            $this->selectBuilderMock,
            $queryConfigMock['source']['link-source'][0]['using'],
            $queryConfigMock['source']['link-source'][0]['alias'],
            $queryConfigMock['source']['alias']
        ];
        $willReturnArgs[] = '(billing.parent_id = `sales`.`entity_id`)';

        if (isset($queryConfigMock['source']['link-source'][0]['filter'])) {
            $filtersMock = ['(sales.entity_id IS NULL)'];

            $withArgs[] = [
                $this->selectBuilderMock,
                $queryConfigMock['source']['link-source'][0]['filter'],
                $queryConfigMock['source']['link-source'][0]['alias'],
                $queryConfigMock['source']['alias']
            ];
            $willReturnArgs[] = $filtersMock[0];

            $this->columnsResolverMock->expects($this->once())
                ->method('getColumns')
                ->with($this->selectBuilderMock, $queryConfigMock['source']['link-source'][0])
                ->willReturn(
                    [
                        'entity_id' => 'sales.entity_id',
                        'billing_address_id' => 'billing.entity_id'
                    ]
                );

            $this->selectBuilderMock->expects($this->once())
                ->method('setColumns')
                ->with(
                    [
                        'entity_id' => 'sales.entity_id',
                        'billing_address_id' => 'billing.entity_id'
                    ]
                );
        }
        $this->conditionResolverMock
            ->method('getFilter')
            ->willReturnCallback(function ($withArgs) use ($willReturnArgs) {
                static $callCount = 0;
                $returnValue = $willReturnArgs[$callCount] ?? null;
                $callCount++;
                return $returnValue;
            });

        $this->selectBuilderMock->expects($this->once())
            ->method('setFilters')
            ->with($filtersMock);
        $this->selectBuilderMock->expects($this->once())
            ->method('setJoins')
            ->with($joinsMock);

        $this->assertEquals(
            $this->selectBuilderMock,
            $this->subject->assemble($this->selectBuilderMock, $queryConfigMock)
        );
    }

    /**
     * @return array
     */
    public static function assembleNotEmptyDataProvider(): array
    {
        return [
            [
                [
                    'source' => [
                        'name' => 'sales_order',
                        'alias' => 'sales',
                        'link-source' => [
                            [
                                'name' => 'sales_order_address',
                                'alias' => 'billing',
                                'link-type' => 'left',
                                'attribute' => [
                                    [
                                        'alias' => 'billing_address_id',
                                        'name' => 'entity_id'
                                    ]
                                ],
                                'using' => [
                                    [
                                        'glue' => 'and',
                                        'condition' => [
                                            [
                                                'attribute' => 'parent_id',
                                                'operator' => 'eq',
                                                'type' => 'identifier',
                                                '_value' => 'entity_id'
                                            ]
                                        ]
                                    ]
                                ],
                                'filter' => [
                                    [
                                        'glue' => 'and',
                                        'condition' => [
                                            [
                                                'attribute' => 'entity_id',
                                                'operator' => 'null'
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    'billing' => [
                        'link-type' => 'left',
                        'table' => [
                            'billing' => 'pref_sales_order_address'
                        ],
                        'condition' => '(billing.parent_id = `sales`.`entity_id`)'
                    ]
                ],
                ['sales_order_address' => 'pref_sales_order_address']
            ]
        ];
    }
}
