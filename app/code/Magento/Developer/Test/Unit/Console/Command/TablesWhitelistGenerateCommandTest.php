<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Developer\Test\Unit\Console\Command;

use Magento\Framework\Setup\JsonPersistor;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Symfony\Component\Console\Tester\CommandTester;

class TablesWhitelistGenerateCommandTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Developer\Console\Command\TablesWhitelistGenerateCommand */
    protected $model;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Framework\Component\ComponentRegistrar|\PHPUnit_Framework_MockObject_MockObject */
    protected $componentRegistrarMock;

    /** @var \Magento\Setup\Model\Declaration\Schema\Declaration\ReaderComposite|\PHPUnit_Framework_MockObject_MockObject */
    protected $readerCompositeMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $jsonPersistor;

    protected function setUp()
    {
        $this->componentRegistrarMock = $this->getMockBuilder(\Magento\Framework\Component\ComponentRegistrar::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->readerCompositeMock = $this->getMockBuilder(\Magento\Setup\Model\Declaration\Schema\Declaration\ReaderComposite::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->jsonPersistor = $this->getMockBuilder(JsonPersistor::class)
            ->getMock();
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            \Magento\Developer\Console\Command\TablesWhitelistGenerateCommand::class,
            [
                'componentRegistrar' => $this->componentRegistrarMock,
                'readerComposite' => $this->readerCompositeMock,
                'jsonPersistor' => $this->jsonPersistor
            ]
        );
    }

    /**
     * @return array
     */
    public function whitelistTableProvider()
    {
        return [
            [
                'moduleName' => 'SomeModule',
                'whitelist' => [
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
                            'column' =>
                                [
                                    'first_column' => true,
                                    'second_column' => true,
                                ],
                            'index' =>
                                [
                                    'TEST_INDEX' => true,
                                ],
                            'constraint' =>
                                [
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
                            'column' =>
                                [
                                    'first_column' => true,
                                    'second_column' => true,
                                ],
                            'index' =>
                                [
                                    'TEST_INDEX' => true,
                                ],
                            'constraint' =>
                                [
                                    'foreign' => true,
                                    'primary' => true,
                                ]
                        ]
                    ],
                    'Module2' => [
                        'second_table' => [
                            'column' =>
                                [
                                    'first_column' => true,
                                    'second_column' => true,
                                ],
                            'index' =>
                                [
                                    'TEST_INDEX' => true,
                                ],
                            'constraint' =>
                                [
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
            $this->readerCompositeMock->expects(self::exactly(2))
                ->method('read')
                ->withConsecutive(['SomeModule'], ['Module2'])
                ->willReturnOnConsecutiveCalls($whiteListTables['SomeModule'], $whiteListTables['Module2']);
            $this->jsonPersistor->expects(self::exactly(2))
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
            $this->readerCompositeMock->expects(self::once())
                ->method('read')
                ->with($moduleName)
                ->willReturn($whiteListTables['SomeModule']);
            $this->jsonPersistor->expects(self::once())
                ->method('persist')
                ->with(
                    $expected['SomeModule'],
                    '/etc/db_schema_whitelist.json'
                );
        }


        $commandTester->execute($options);
    }
}
