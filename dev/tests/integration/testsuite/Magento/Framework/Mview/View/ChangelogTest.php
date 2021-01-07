<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mview\View;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Mview\View;

/**
 * Test Class for \Magento\Framework\Mview\View\Changelog
 *
 * @magentoDbIsolation disabled
 */
class ChangelogTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * Write connection adapter
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var Changelog
     */
    protected $model;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->resource = $this->objectManager->get(ResourceConnection::class);
        $this->connection = $this->resource->getConnection();

        $this->model = $this->objectManager->create(
            Changelog::class,
            ['resource' => $this->resource]
        );
        $this->model->setViewId('test_view_id_1');
        $this->model->create();
    }

    /**
     * @return void
     * @throws ChangelogTableNotExistsException
     */
    protected function tearDown(): void
    {
        $this->model->drop();
    }

    /**
     * Test for create() and drop() methods
     *
     * @return void
     * @throws ChangelogTableNotExistsException
     */
    public function testCreateAndDrop()
    {
        /** @var Changelog $model */
        $model = $this->objectManager->create(
            Changelog::class,
            ['resource' => $this->resource]
        );
        $model->setViewId('test_view_id_2');
        $changelogName = $this->resource->getTableName($model->getName());
        $this->assertFalse($this->connection->isTableExists($changelogName));
        $model->create();
        $this->assertTrue($this->connection->isTableExists($changelogName));
        $model->drop();
        $this->assertFalse($this->connection->isTableExists($changelogName));
    }

    /**
     * Test for getVersion() method
     *
     * @return void
     * @throws \Exception
     */
    public function testGetVersion()
    {
        $model = $this->objectManager->create(
            Changelog::class,
            ['resource' => $this->resource]
        );
        $model->setViewId('test_view_id_2');
        $model->create();
        $this->assertEquals(0, $model->getVersion());
        $changelogName = $this->resource->getTableName($model->getName());
        $this->connection->insert($changelogName, [$model->getColumnName() => random_int(1, 200)]);
        $this->assertEquals($this->connection->lastInsertId($changelogName, 'version_id'), $model->getVersion());
        $model->drop();
    }

    /**
     * Test for clear() method
     *
     * @return void
     * @throws ChangelogTableNotExistsException
     * @throws \Magento\Framework\Exception\RuntimeException
     */
    public function testClear()
    {
        $this->assertEquals(0, $this->model->getVersion());
        //the same that a table is empty
        $changelogName = $this->resource->getTableName($this->model->getName());
        $this->connection->insert($changelogName, ['version_id' => 1, 'entity_id' => 1]);
        $this->assertEquals(1, $this->model->getVersion());
        $this->model->clear(1);
        $this->assertEquals(1, $this->model->getVersion()); //the same that a table is empty
    }

    /**
     * Create entity table for MView
     *
     * @param string $tableName
     * @return void
     */
    private function createEntityTable(string $tableName)
    {
        $table = $this->resource->getConnection()->newTable(
            $tableName
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Version ID'
        )->addColumn(
            'additional_column',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Entity ID'
        );
        $this->resource->getConnection()->createTable($table);
    }

    public function testAdditionalColumns()
    {
        $tableName = 'test_mview_table';
        $this->createEntityTable($tableName);
        $view = $this->objectManager->create(View::class);
        $view->load('test_view_with_additional_columns');
        $view->subscribe();
        $this->connection->insert($tableName, ['entity_id' => 12, 'additional_column' => 13]);
        $select = $this->connection->select()
            ->from($view->getChangelog()->getName(), ['entity_id', 'test_additional_column']);
        $actual = $this->connection->fetchAll($select);
        $this->assertEquals(
            [
                'entity_id' => "12",
                'test_additional_column' => "13"
            ],
            reset($actual)
        );
        $this->connection->dropTable($tableName);
        $this->connection->dropTable($view->getChangelog()->getName());
    }

    /**
     * Test for getList() method
     *
     * @return void
     * @throws ChangelogTableNotExistsException
     * @throws \Magento\Framework\Exception\RuntimeException
     */
    public function testGetList()
    {
        $this->assertEquals(0, $this->model->getVersion());
        //the same that a table is empty
        $changelogName = $this->resource->getTableName($this->model->getName());
        $testChangelogData = [
            ['version_id' => 1, 'entity_id' => 1],
            ['version_id' => 2, 'entity_id' => 1],
            ['version_id' => 3, 'entity_id' => 2],
            ['version_id' => 4, 'entity_id' => 3],
            ['version_id' => 5, 'entity_id' => 1],
        ];
        foreach ($testChangelogData as $data) {
            $this->connection->insert($changelogName, $data);
        }
        $this->assertEquals(5, $this->model->getVersion());
        $this->assertCount(3, $this->model->getList(0, 5));//distinct entity_ids
        $this->assertCount(3, $this->model->getList(2, 5));//distinct entity_ids
        $this->assertCount(2, $this->model->getList(0, 3));//distinct entity_ids
        $this->assertCount(1, $this->model->getList(0, 2));//distinct entity_ids
        $this->assertCount(1, $this->model->getList(2, 3));//distinct entity_ids
        $this->assertCount(0, $this->model->getList(4, 3));//because fromVersionId > toVersionId
    }
}
