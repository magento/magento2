<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Test\Unit;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaListenerDefinition\BooleanDefinition;
use Magento\Framework\Setup\SchemaListenerDefinition\IntegerDefinition;
use Magento\Framework\Setup\SchemaListenerDefinition\RealDefinition;
use Magento\Framework\Setup\SchemaListenerDefinition\TimestampDefinition;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit test for schema listener.
 *
 * @package Magento\Framework\Setup\Test\Unit
 */
class SchemaListenerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Setup\SchemaListener
     */
    private $model;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    protected function setUp() : void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            \Magento\Framework\Setup\SchemaListener::class,
            [
                'definitionMappers' => [
                    'timestamp' => new TimestampDefinition(),
                    'integer' => new IntegerDefinition(
                        new BooleanDefinition()
                    ),
                    'decimal' => new RealDefinition()
                ]
            ]
        );
        $this->model->flush();
    }

    /**
     * @return Table
     */
    private function getCreateTableDDL($tableName) : Table
    {
        $table = new Table();
        $table->setName($tableName);
        $table->setOption('type', 'innodb');
        return $table->addColumn(
            'timestamp',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
            'Column with type timestamp init update'
        )->addColumn(
            'integer',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'primary' => true, 'identity' => true],
            'Integer'
        )->addColumn(
            'decimal',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '25,12',
            ['unsigned' => false, 'nullable' => false],
            'Decimal'
        )
        ->addIndex(
            'INDEX_KEY',
            ['column_with_type_text'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT]
        )
        ->addForeignKey(
            'some_key',
            'decimal',
            'setup_tests_table1',
            'column_with_type_integer',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Related Table'
        );
    }

    public function testRenameTable() : void
    {
        $this->model->setModuleName('First_Module');
        $this->model->createTable($this->getCreateTableDDL('old_table'));
        $this->model->renameTable('old_table', 'new_table');
        $tables = $this->model->getTables();
        self::assertArrayHasKey('new_table', $tables['First_Module']);
        self::assertArrayNotHasKey('old_table', $tables['First_Module']);
    }

    public function testDropIndex() : void
    {
        $this->model->setModuleName('First_Module');
        $this->model->createTable($this->getCreateTableDDL('index_table'));
        $this->model->dropIndex('index_table', 'INDEX_KEY', 'index');
        self::assertTrue($this->model->getTables()['First_Module']['index_table']['indexes']['INDEX_KEY']['disabled']);
    }

    public function testCreateTable() : void
    {
        $this->model->setModuleName('First_Module');
        $this->model->createTable($this->getCreateTableDDL('new_table'));
        $tables = $this->model->getTables();
        self::assertArrayHasKey('new_table', $tables['First_Module']);
        self::assertEquals(
            [
                'timestamp' =>
                    [
                        'xsi:type' => 'timestamp',
                        'name' => 'timestamp',
                        'on_update' => true,
                        'nullable' => false,
                        'default' => 'CURRENT_TIMESTAMP',
                        'disabled' => false,
                        'onCreate' => null,
                        'comment' => 'Column with type timestamp init update',
                    ],
                'integer' =>
                    [
                        'xsi:type' => 'int',
                        'name' => 'integer',
                        'padding' => 11,
                        'unsigned' => false,
                        'nullable' => false,
                        'identity' => true,
                        'default' => null,
                        'disabled' => false,
                        'onCreate' => null,
                        'comment' => 'Integer'
                    ],
                'decimal' =>
                    [
                        'xsi:type' => 'decimal',
                        'name' => 'decimal',
                        'scale' => '12',
                        'precision' => '25',
                        'unsigned' => false,
                        'nullable' => false,
                        'default' => null,
                        'disabled' => false,
                        'onCreate' => null,
                        'comment' => 'Decimal'
                    ],
            ],
            $tables['First_Module']['new_table']['columns']
        );
        self::assertEquals(
            [
                'primary' =>
                    [
                        'PRIMARY' =>
                            [
                                'type' => 'primary',
                                'name' => 'PRIMARY',
                                'disabled' => false,
                                'columns' =>
                                    [
                                        'INTEGER' => 'integer',
                                    ],
                            ],
                    ],
                'foreign' =>
                    [
                        'SOME_KEY' =>
                            [
                                'table' => 'new_table',
                                'column' => 'decimal',
                                'referenceTable' => 'setup_tests_table1',
                                'referenceColumn' => 'column_with_type_integer',
                                'onDelete' => 'CASCADE',
                                'disabled' => false,
                            ],
                    ],
            ],
            $tables['First_Module']['new_table']['constraints']
        );

        self::assertEquals(
            [
                'INDEX_KEY' =>
                    [
                        'columns' =>
                            [
                                'column_with_type_text' => 'column_with_type_text',
                            ],
                        'indexType' => 'fulltext',
                        'disabled' => false,
                    ],
            ],
            $tables['First_Module']['new_table']['indexes']
        );
    }

    public function testDropTable() : void
    {
        $this->model->setModuleName('Old_Module');
        $this->model->createTable($this->getCreateTableDDL('old_table'));
        $this->model->setModuleName('New_Module');
        $this->model->dropTable('old_table');
        self::assertTrue($this->model->getTables()['New_Module']['old_table']['disabled']);
    }

    public function testDropTableInSameModule() : void
    {
        $this->model->setModuleName('Old_Module');
        $this->model->createTable($this->getCreateTableDDL('old_table'));
        $this->model->dropTable('old_table');
        self::assertArrayNotHasKey('old_table', $this->model->getTables()['Old_Module']);
    }
}
