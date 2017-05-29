<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mview\View;

use Magento\Framework\App\ResourceConnection;

/**
 * Test Class for \Magento\Framework\Mview\View\Changelog
 */
class ChangelogTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * Write connection adapter
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var \Magento\Framework\Mview\View\Changelog
     */
    protected $model;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->resource = $this->objectManager->get(\Magento\Framework\App\ResourceConnection::class);
        $this->connection = $this->resource->getConnection();

        $this->model = $this->objectManager->create(
            \Magento\Framework\Mview\View\Changelog::class,
            ['resource' => $this->resource]
        );
        $this->model->setViewId('test_view_id_1');
        $this->model->create();
    }

    /**
     * @return void
     */
    public function tearDown()
    {
        $this->model->drop();
    }

    /**
     * Test for create() and drop() methods
     *
     * @return void
     */
    public function testCreateAndDrop()
    {
        /** @var \Magento\Framework\Mview\View\Changelog $model */
        $model = $this->objectManager->create(
            \Magento\Framework\Mview\View\Changelog::class,
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
     */
    public function testGetVersion()
    {
        $model = $this->objectManager->create(
            \Magento\Framework\Mview\View\Changelog::class,
            ['resource' => $this->resource]
        );
        $model->setViewId('test_view_id_2');
        $model->create();
        $this->assertEquals(0, $model->getVersion());
        $changelogName = $this->resource->getTableName($model->getName());
        $this->connection->insert($changelogName, [$model->getColumnName() => mt_rand(1, 200)]);
        $this->assertEquals($this->connection->lastInsertId($changelogName, 'version_id'), $model->getVersion());
        $model->drop();
    }

    /**
     * Test for clear() method
     *
     * @return void
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
     * Test for getList() method
     *
     * @return void
     */
    public function testGetList()
    {
        $this->assertEquals(0, $this->model->getVersion());
        //the same that a table is empty
        $changelogName = $this->resource->getTableName($this->model->getName());
        $testChengelogData = [
            ['version_id' => 1, 'entity_id' => 1],
            ['version_id' => 2, 'entity_id' => 1],
            ['version_id' => 3, 'entity_id' => 2],
            ['version_id' => 4, 'entity_id' => 3],
            ['version_id' => 5, 'entity_id' => 1],
        ];
        foreach ($testChengelogData as $data) {
            $this->connection->insert($changelogName, $data);
        }
        $this->assertEquals(5, $this->model->getVersion());
        $this->assertEquals(3, count($this->model->getList(0, 5)));//distinct entity_ids
        $this->assertEquals(3, count($this->model->getList(2, 5)));//distinct entity_ids
        $this->assertEquals(2, count($this->model->getList(0, 3)));//distinct entity_ids
        $this->assertEquals(1, count($this->model->getList(0, 2)));//distinct entity_ids
        $this->assertEquals(1, count($this->model->getList(2, 3)));//distinct entity_ids
        $this->assertEquals(0, count($this->model->getList(4, 3)));//because fromVersionId > toVersionId
    }
}
