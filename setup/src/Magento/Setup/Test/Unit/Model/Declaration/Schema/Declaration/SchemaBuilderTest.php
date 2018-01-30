<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Declaration\Schema\Declaration;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\BooleanUtils;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Setup\Model\Declaration\Schema\Dto\Columns\Integer;
use Magento\Setup\Model\Declaration\Schema\Dto\Columns\Timestamp;
use Magento\Setup\Model\Declaration\Schema\Dto\Constraints\Internal;
use Magento\Setup\Model\Declaration\Schema\Dto\Constraints\Reference;
use Magento\Setup\Model\Declaration\Schema\Dto\Index;
use Magento\Setup\Model\Declaration\Schema\Dto\Schema;
use Magento\Setup\Model\Declaration\Schema\Dto\Table;

class SchemaBuilderTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Setup\Model\Declaration\Schema\Declaration\SchemaBuilder */
    protected $model;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Setup\Model\Declaration\Schema\Dto\ElementFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $elementFactoryMock;

    /** @var \Magento\Framework\Stdlib\BooleanUtils|\PHPUnit_Framework_MockObject_MockObject */
    protected $booleanUtilsMock;

    /** @var \Magento\Setup\Model\Declaration\Schema\Sharding|\PHPUnit_Framework_MockObject_MockObject */
    protected $shardingMock;

    /** @var \Magento\Setup\Model\Declaration\Schema\Declaration\ValidationComposite|\PHPUnit_Framework_MockObject_MockObject */
    protected $validationCompositeMock;

    /** @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject */
    protected $resourceConnectionMock;

    protected function setUp()
    {
        $this->elementFactoryMock = $this->getMockBuilder(\Magento\Setup\Model\Declaration\Schema\Dto\ElementFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->booleanUtilsMock = $this->getMockBuilder(\Magento\Framework\Stdlib\BooleanUtils::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->shardingMock = $this->getMockBuilder(\Magento\Setup\Model\Declaration\Schema\Sharding::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->validationCompositeMock = $this->getMockBuilder(\Magento\Setup\Model\Declaration\Schema\Declaration\ValidationComposite::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceConnectionMock = $this->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            \Magento\Setup\Model\Declaration\Schema\Declaration\SchemaBuilder::class,
            [
                'elementFactory' => $this->elementFactoryMock,
                'booleanUtils' => new BooleanUtils(),
                'sharding' => $this->shardingMock,
                'validationComposite' => $this->validationCompositeMock,
                'resourceConnection' => $this->resourceConnectionMock
            ]
        );
    }

    /**
     * @return array
     */
    public function tablesProvider()
    {
        return [
            [
                [
                    'first_table' => [
                        'name' => 'first_table',
                        'engine' => 'innodb',
                        'resource' => 'default',
                        'column' => [
                            'first_column' => [
                                'name' => 'first_column',
                                'type' => 'int',
                                'padding' => 10,
                                'identity' => true,
                                'nullable' => false
                            ],
                            'foreign_column' => [
                                'name' => 'foreign_column',
                                'type' => 'int',
                                'padding' => 10,
                                'nullable' => false
                            ],
                            'some_disabled_column' => [
                                'name' => 'some_disabled_column',
                                'disabled' => 'true',
                                'type' => 'int',
                                'padding' => 10,
                                'nullable' => false
                            ],
                            'second_column' => [
                                'name' => 'second_column',
                                'type' => 'timestamp',
                                'default' => 'CURRENT_TIMESTAMP',
                                'on_update' => true
                            ],
                        ],
                        'constraint' => [
                            'some_foreign_key' => [
                                'name' => 'some_foreign_key',
                                'type' => 'foreign',
                                'column' => 'foreign_column',
                                'table' => 'first_table',
                                'referenceTable' => 'second_table',
                                'referenceColumn' => 'ref_column'
                            ],
                            'PRIMARY' => [
                                'name' => 'PRIMARY',
                                'type' => 'primary',
                                'column' => [
                                    'first_column'
                                ]
                            ]
                        ]
                    ],
                    'second_table' => [
                        'name' => 'second_table',
                        'engine' => 'innodb',
                        'resource' => 'default',
                        'column' => [
                            'ref_column' => [
                                'name' => 'ref_column',
                                'type' => 'int',
                                'padding' => 10,
                                'nullable' => false
                            ],
                        ],
                        'index' => [
                            'FIRST_INDEX' => [
                                'name' => 'FIRST_INDEX',
                                'column' => [
                                    'ref_column'
                                ]
                            ]
                        ],
                    ]
                ]
            ]
        ];
    }

    /**
     * Create table
     *
     * @param string $name
     * @return Table
     */
    private function createTable($name)
    {
        return new Table(
            $name,
            $name,
            'table',
            'default',
            'resource'
        );
    }

    /**
     * @param string $name
     * @param Table $table
     * @return \Magento\Setup\Model\Declaration\Schema\Dto\Columns\Integer
     */
    private function createIntegerAIColumn($name, Table $table)
    {
        return new Integer(
            $name,
            'int',
            $table,
            10,
            true,
            false,
            true
        );
    }

    /**
     * @param string $name
     * @param Table $table
     * @return \Magento\Setup\Model\Declaration\Schema\Dto\Columns\Integer
     */
    private function createIntegerColumn($name, Table $table)
    {
        return new Integer(
            $name,
            'int',
            $table,
            10
        );
    }

    /**
     * @param Table $table
     * @param array $columns
     * @return Internal
     */
    private function createPrimaryConstraint(Table $table, array $columns)
    {
        return new Internal(
            'PRIMARY',
            'primary',
            $table,
            $columns
        );
    }

    /**
     * @param string $indexName
     * @param Table $table
     * @param array $columns
     * @return Index
     */
    private function createIndex($indexName, Table $table, array $columns)
    {
        return new Index(
            $indexName,
            'index',
            $table,
            $columns,
            'btree'
        );
    }


    /**
     * @param string $name
     * @param Table $table
     * @return \Magento\Setup\Model\Declaration\Schema\Dto\Columns\Timestamp
     */
    private function createTimestampColumn($name, Table $table)
    {
        return new Timestamp(
            $name,
            'timestamp',
            $table,
            'CURRENT_TIMESTAMP',
            false,
            true
        );
    }

    /**
     * @dataProvider tablesProvider
     * @param array $tablesData
     */
    public function testBuild(array $tablesData)
    {
        $table = $this->createTable('first_table');
        $refTable = $this->createTable('second_table');
        $refColumn = $this->createIntegerColumn('ref_column', $refTable);
        $index = $this->createIndex('FIRST_INDEX', $table, [$refColumn]);
        $refTable->addColumns([$refColumn]);
        $refTable->addIndexes([$index]);
        $firstColumn = $this->createIntegerAIColumn('first_column', $table);
        $foreignColumn = $this->createIntegerColumn('foreign_column', $table);
        $timestampColumn = $this->createTimestampColumn('second_column', $table);
        $primaryKey = $this->createPrimaryConstraint($table, [$firstColumn]);
        $foreignKey = new Reference(
            'some_foreign_key',
            'foreign',
            $table,
            $foreignColumn,
            $refTable,
            $refColumn,
            'CASCADE'
        );
        $table->addColumns([$firstColumn, $foreignColumn, $timestampColumn]);
        $table->addConstraints([$foreignKey, $primaryKey]);
        $this->elementFactoryMock->expects(self::exactly(9))
            ->method('create')
            ->withConsecutive(
                [
                    'table',
                    [
                        'name' =>'first_table',
                        'resource' => 'default',
                        'engine' => 'innodb',
                        'comment' => null
                    ]
                ],
                [
                    'int',
                    [
                        'name' => 'first_column',
                        'type' => 'int',
                        'table' => $table,
                        'padding' => 10,
                        'identity' => true,
                        'nullable' => false,
                        'resource' => 'default'
                    ]
                ],
                [
                    'int',
                    [
                        'name' => 'foreign_column',
                        'type' => 'int',
                        'table' => $table,
                        'padding' => 10,
                        'nullable' => false,
                        'resource' => 'default'
                    ]
                ],
                [
                    'timestamp',
                    [
                        'name' => 'second_column',
                        'type' => 'timestamp',
                        'table' => $table,
                        'default' => 'CURRENT_TIMESTAMP',
                        'on_update' => true,
                        'resource' => 'default'
                    ]
                ],
                [
                    'table',
                    [
                        'name' =>'second_table',
                        'resource' => 'default',
                        'engine' => 'innodb',
                        'comment' => null
                    ]
                ],
                [
                    'int',
                    [
                        'name' => 'ref_column',
                        'type' => 'int',
                        'table' => $refTable,
                        'padding' => 10,
                        'nullable' => false,
                        'resource' => 'default'
                    ]
                ],
                [
                    'index',
                    [
                        'name' => 'FIRST_INDEX',
                        'table' => $refTable,
                        'column' => ['ref_column'],
                        'columns' => [$refColumn],
                        'resource' => 'default'
                    ]
                ],
                [
                    'foreign',
                    [
                        'name' => 'some_foreign_key',
                        'type' => 'foreign',
                        'column' => $foreignColumn,
                        'table' => $table,
                        'referenceTable' => $refTable,
                        'referenceColumn' => $refColumn,
                        'resource' => 'default'
                    ]
                ],
                [
                    'primary',
                    [
                        'name' => 'PRIMARY',
                        'type' => 'primary',
                        'columns' => [$firstColumn],
                        'table' => $table,
                        'column' => ['first_column'],
                        'resource' => 'default'
                    ]
                ]
            )
            ->willReturnOnConsecutiveCalls(
                $table,
                $firstColumn,
                $foreignColumn,
                $timestampColumn,
                $refTable,
                $refColumn,
                $index,
                $foreignKey,
                $primaryKey
            );
        $resourceConnectionMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var Schema $schema */
        $schema = $this->objectManagerHelper->getObject(
            Schema::class,
            ['resourceConnection' => $resourceConnectionMock]
        );
        $this->resourceConnectionMock->expects(self::once())
            ->method('getTableName')
            ->willReturn('second_table');
        $resourceConnectionMock->expects(self::exactly(6))
            ->method('getTableName')
            ->withConsecutive(
                ['first_table'],
                ['first_table'],
                ['second_table'],
                ['second_table'],
                ['first_table'],
                ['second_table']
            )
            ->willReturnOnConsecutiveCalls(
                'first_table',
                'first_table',
                'second_table',
                'second_table',
                'first_table',
                'second_table'
            );
        $this->model->addTablesData($tablesData);
        $this->model->build($schema);
    }
}
