<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Developer\Test\Unit\Console\Command;

use Magento\Developer\Console\Command\TablesWhitelistGenerateCommand;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Setup\JsonPersistor;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\Setup\Declaration\Schema\Declaration\ReaderComposite;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Unit test for whitelist generation command.
 *
 * @package Magento\Developer\Test\Unit\Console\Command
 */
class TablesWhitelistGenerateCommandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var TablesWhitelistGenerateCommand
     */
    private $model;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var ComponentRegistrar|\PHPUnit_Framework_MockObject_MockObject
     */
    private $componentRegistrarMock;

    /**
     * @var ReaderComposite|\PHPUnit_Framework_MockObject_MockObject
     */
    private $readerCompositeMock;

    /**
     * @var JsonPersistor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $jsonPersistorMock;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->componentRegistrarMock = $this->getMockBuilder(ComponentRegistrar::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->readerCompositeMock = $this->getMockBuilder(ReaderComposite::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->jsonPersistorMock = $this->getMockBuilder(JsonPersistor::class)
            ->getMock();
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            TablesWhitelistGenerateCommand::class,
            [
                'componentRegistrar' => $this->componentRegistrarMock,
                'readerComposite' => $this->readerCompositeMock,
                'jsonPersistor' => $this->jsonPersistorMock
            ]
        );
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function whitelistTableProvider()
    {
        return [
            [
                'moduleName' => 'SomeModule',
                'whitelist' => [
                    'primary' => [
                        'table' =>
                            [
                                'patch_list' =>
                                    [
                                        'column' =>
                                            [
                                                'patch_id' =>
                                                    [
                                                        'type' => 'int',
                                                        'name' => 'patch_id',
                                                        'identity' => 'true',
                                                        'comment' => 'Patch Auto Increment',
                                                    ],
                                                'patch_name' =>
                                                    [
                                                        'type' => 'varchar',
                                                        'name' => 'patch_name',
                                                        'length' => '1024',
                                                        'nullable' => 'false',
                                                        'comment' => 'Patch Class Name',
                                                    ],
                                            ],
                                        'constraint' =>
                                            [
                                                'PRIMARY' =>
                                                    [
                                                        'column' =>
                                                            [
                                                                'patch_id' => 'patch_id',
                                                            ],
                                                        'type' => 'primary',
                                                        'name' => 'PRIMARY',
                                                    ],
                                            ],
                                        'name' => 'patch_list',
                                        'resource' => 'default',
                                        'comment' => 'List of data/schema patches',
                                    ],
                            ],
                    ],
                    'SomeModule' => [
                        'table' => [
                            'first_table' => [
                                'disabled' => false,
                                'name' => 'first_table',
                                'resource' => 'default',
                                'engine' => 'innodb',
                                'column' => [
                                    'first_column' => [
                                        'name' => 'first_column',
                                        'xsi:type' => 'integer',
                                        'nullable' => 1,
                                        'unsigned' => '0',
                                    ],
                                    'second_column' => [
                                        'name' => 'second_column',
                                        'xsi:type' => 'date',
                                        'nullable' => 0,
                                    ]
                                ],
                                'index' => [
                                    'TEST_INDEX' => [
                                        'name' => 'TEST_INDEX',
                                        'indexType' => 'btree',
                                        'columns' => [
                                            'first_column'
                                        ]
                                    ]
                                ],
                                'constraint' => [
                                    'foreign' => [
                                        'some_foreign_constraint' => [
                                            'referenceTable' => 'table',
                                            'referenceColumn' => 'column',
                                            'table' => 'first_table',
                                            'column' => 'first_column'
                                        ]
                                    ],
                                    'primary' => [
                                        'PRIMARY' => [
                                            'xsi:type' => 'primary',
                                            'name' => 'PRIMARY',
                                            'columns' => [
                                                'second_column'
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                ],
                'expected' => [
                    'SomeModule' => [
                        'first_table' => [
                            'column' => [
                                'first_column' => true,
                                'second_column' => true,
                            ],
                            'index' => [
                                'TEST_INDEX' => true,
                            ],
                            'constraint' => [
                                'foreign' => true,
                                'primary' => true,
                            ]
                        ]
                    ]
                ]
            ],
            [
                'moduleName' => false,
                'whitelist' => [
                    'primary' => [
                        'table' =>
                            [
                                'patch_list' =>
                                    [
                                        'column' =>
                                            [
                                                'patch_id' =>
                                                    [
                                                        'type' => 'int',
                                                        'name' => 'patch_id',
                                                        'identity' => 'true',
                                                        'comment' => 'Patch Auto Increment',
                                                    ],
                                                'patch_name' =>
                                                    [
                                                        'type' => 'varchar',
                                                        'name' => 'patch_name',
                                                        'length' => '1024',
                                                        'nullable' => 'false',
                                                        'comment' => 'Patch Class Name',
                                                    ],
                                            ],
                                        'constraint' =>
                                            [
                                                'PRIMARY' =>
                                                    [
                                                        'column' =>
                                                            [
                                                                'patch_id' => 'patch_id',
                                                            ],
                                                        'type' => 'primary',
                                                        'name' => 'PRIMARY',
                                                    ],
                                            ],
                                        'name' => 'patch_list',
                                        'resource' => 'default',
                                        'comment' => 'List of data/schema patches',
                                    ],
                            ],
                    ],
                    'SomeModule' => [
                        'table' => [
                            'first_table' => [
                                'disabled' => false,
                                'name' => 'first_table',
                                'resource' => 'default',
                                'engine' => 'innodb',
                                'column' => [
                                    'first_column' => [
                                        'name' => 'first_column',
                                        'xsi:type' => 'integer',
                                        'nullable' => 1,
                                        'unsigned' => '0',
                                    ],
                                    'second_column' => [
                                        'name' => 'second_column',
                                        'xsi:type' => 'date',
                                        'nullable' => 0,
                                    ]
                                ],
                                'index' => [
                                    'TEST_INDEX' => [
                                        'name' => 'TEST_INDEX',
                                        'indexType' => 'btree',
                                        'columns' => [
                                            'first_column'
                                        ]
                                    ]
                                ],
                                'constraint' => [
                                    'foreign' => [
                                        'some_foreign_constraint' => [
                                            'referenceTable' => 'table',
                                            'referenceColumn' => 'column',
                                            'table' => 'first_table',
                                            'column' => 'first_column'
                                        ]
                                    ],
                                    'primary' => [
                                        'PRIMARY' => [
                                            'xsi:type' => 'primary',
                                            'name' => 'PRIMARY',
                                            'columns' => [
                                                'second_column'
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'Module2' => [
                        'table' => [
                            'second_table' => [
                                'disabled' => false,
                                'name' => 'second_table',
                                'resource' => 'default',
                                'engine' => 'innodb',
                                'column' => [
                                    'first_column' => [
                                        'name' => 'first_column',
                                        'xsi:type' => 'integer',
                                        'nullable' => 1,
                                        'unsigned' => '0',
                                    ],
                                    'second_column' => [
                                        'name' => 'second_column',
                                        'xsi:type' => 'date',
                                        'nullable' => 0,
                                    ]
                                ],
                                'index' => [
                                    'TEST_INDEX' => [
                                        'name' => 'TEST_INDEX',
                                        'indexType' => 'btree',
                                        'columns' => [
                                            'first_column'
                                        ]
                                    ]
                                ],
                                'constraint' => [
                                    'foreign' => [
                                        'some_foreign_constraint' => [
                                            'referenceTable' => 'table',
                                            'referenceColumn' => 'column',
                                            'table' => 'second_table',
                                            'column' => 'first_column'
                                        ]
                                    ],
                                    'primary' => [
                                        'PRIMARY' => [
                                            'xsi:type' => 'primary',
                                            'name' => 'PRIMARY',
                                            'columns' => [
                                                'second_column'
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                'expected' => [
                    'SomeModule' => [
                        'first_table' => [
                            'column' => [
                                'first_column' => true,
                                'second_column' => true,
                            ],
                            'index' => [
                                'TEST_INDEX' => true,
                            ],
                            'constraint' => [
                                'foreign' => true,
                                'primary' => true,
                            ]
                        ]
                    ],
                    'Module2' => [
                        'second_table' => [
                            'column' => [
                                'first_column' => true,
                                'second_column' => true,
                            ],
                            'index' => [
                                'TEST_INDEX' => true,
                            ],
                            'constraint' => [
                                'foreign' => true,
                                'primary' => true,
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @dataProvider whitelistTableProvider
     * @param string $moduleName
     * @param array $whiteListTables
     * @param array $expected
     */
    public function testCommand($moduleName, array $whiteListTables, array $expected)
    {
        $commandTester = new CommandTester($this->model);
        $options = !$moduleName ? [] : ['--module-name' => $moduleName];

        if (!$moduleName) {
            $this->componentRegistrarMock->expects(self::once())
                ->method('getPaths')
                ->willReturn(['SomeModule' => 1, 'Module2' => 2]);
            $this->readerCompositeMock->expects(self::exactly(3))
                ->method('read')
                ->withConsecutive(['SomeModule'], ['primary'], ['Module2'])
                ->willReturnOnConsecutiveCalls(
                    $whiteListTables['SomeModule'],
                    $whiteListTables['primary'],
                    $whiteListTables['Module2']
                );
            $this->jsonPersistorMock->expects(self::exactly(2))
                ->method('persist')
                ->withConsecutive(
                    [
                        $expected['SomeModule'],
                        '/etc/db_schema_whitelist.json'
                    ],
                    [
                        $expected['Module2'],
                        '/etc/db_schema_whitelist.json'
                    ]
                );
        } else {
            $this->readerCompositeMock->expects(self::exactly(2))
                ->method('read')
                ->withConsecutive([$moduleName], ['primary'])
                ->willReturnOnConsecutiveCalls($whiteListTables['SomeModule'], $whiteListTables['primary']);
            $this->jsonPersistorMock->expects(self::once())
                ->method('persist')
                ->with(
                    $expected['SomeModule'],
                    '/etc/db_schema_whitelist.json'
                );
        }
        $commandTester->execute($options);
    }
}
