<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ResourceConnection\ConfigInterface;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Model\ResourceModel\Type\Db\ConnectionFactoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AclResourceTest extends TestCase
{
    const RESOURCE_NAME = ResourceConnection::DEFAULT_CONNECTION;
    const CONNECTION_NAME = 'connection-name';
    const TABLE_PREFIX = 'prefix_';

    /**
     * @var ConfigInterface|MockObject
     */
    protected $config;

    /**
     * @var ConnectionFactoryInterface|MockObject
     */
    protected $connectionFactory;

    /**
     * @var DeploymentConfig|MockObject
     */
    private $deploymentConfig;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connection;

    protected function setUp(): void
    {
        $this->connectionFactory = $this->getMockBuilder(ConnectionFactoryInterface::class)
            ->setMethods(['create'])
            ->getMockForAbstractClass();
        $this->config = $this->getMockBuilder(ConfigInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConnectionName'])
            ->getMockForAbstractClass();
        $this->config->expects($this->any())
            ->method('getConnectionName')
            ->with(self::RESOURCE_NAME)
            ->willReturn(self::CONNECTION_NAME);

        $this->deploymentConfig = $this->createMock(DeploymentConfig::class);
        $this->deploymentConfig
            ->expects($this->any())
            ->method('get')
            ->willReturnMap(
                [
                    [
                        ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTIONS . '/connection-name',
                        null,
                        [
                            'host' => 'localhost',
                            'dbname' => 'magento',
                            'username' => 'username',
                        ]
                    ],
                    [
                        ConfigOptionsListConstants::CONFIG_PATH_DB_PREFIX,
                        null,
                        self::TABLE_PREFIX
                    ]
                ]
            );

        $this->connection = $this->getMockForAbstractClass(AdapterInterface::class);
        $this->connection->expects($this->any())
            ->method('getTableName')
            ->willReturnArgument(0);

        $this->resource = new ResourceConnection(
            $this->config,
            $this->connectionFactory,
            $this->deploymentConfig
        );
    }

    public function testGetConnectionFail()
    {
        $this->expectException('DomainException');
        $this->expectExceptionMessage('Connection "invalid" is not defined');
        $this->resource->getConnectionByName('invalid');
    }

    public function testGetConnectionInitConnection()
    {
        $this->connectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->connection);
        $this->assertSame($this->connection, $this->resource->getConnection(self::RESOURCE_NAME));
        $this->assertSame($this->connection, $this->resource->getConnection(self::RESOURCE_NAME));
    }

    /**
     * @param array|string $modelEntity
     * @param string $expected
     *
     * @dataProvider getTableNameDataProvider
     */
    public function testGetTableName($modelEntity, $expected)
    {
        $this->connectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->connection);
        $this->assertSame($expected, $this->resource->getTableName($modelEntity));
    }

    /**
     * @return array
     */
    public function getTableNameDataProvider()
    {
        return [
            ['tableName', self::TABLE_PREFIX . 'tableName'],
            [['tableName', 'tableSuffix'], self::TABLE_PREFIX . 'tableName_tableSuffix'],
        ];
    }

    /**
     * @param array|string $modelEntity
     * @param string $tableName
     * @param string $mappedName
     * @param string $expected
     *
     * @dataProvider getTableNameMappedDataProvider
     */
    public function testGetTableNameMapped($modelEntity, $tableName, $mappedName, $expected)
    {
        $this->connectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->connection);
        $this->resource->setMappedTableName($tableName, $mappedName);
        $this->assertSame($expected, $this->resource->getTableName($modelEntity));
    }

    /**
     * @return array
     */
    public function getTableNameMappedDataProvider()
    {
        return [
            ['tableName', 'tableName', 'mappedTableName', 'mappedTableName'],
            [['tableName', 'tableSuffix'], 'tableName', 'mappedTableName', 'mappedTableName_tableSuffix'],
        ];
    }

    public function testGetIdxName()
    {
        $table = 'table';
        $calculatedTableName = self::TABLE_PREFIX . 'table';
        $fields = ['field'];
        $indexType = 'index_type';
        $expectedIdxName = 'idxName';

        $this->connection->expects($this->once())
            ->method('getIndexName')
            ->with($calculatedTableName, $fields, $indexType)
            ->willReturn($expectedIdxName);
        $this->connectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->connection);

        $this->assertEquals('idxName', $this->resource->getIdxName($table, $fields, $indexType));
    }

    public function testGetFkName()
    {
        $table = 'table';
        $calculatedTableName = self::TABLE_PREFIX . 'table';
        $refTable = 'ref_table';
        $calculatedRefTableName = self::TABLE_PREFIX . 'ref_table';
        $columnName = 'columnName';
        $refColumnName = 'refColumnName';

        $this->connection->expects($this->once())
            ->method('getForeignKeyName')
            ->with($calculatedTableName, $columnName, $calculatedRefTableName, $refColumnName)
            ->willReturn('fkName');
        $this->connectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->connection);

        $this->assertEquals('fkName', $this->resource->getFkName($table, $columnName, $refTable, $refColumnName));
    }

    public function testGetTriggerName()
    {
        $tableName = 'subject_table';
        $time = 'before';
        $event = 'insert';
        $triggerName = 'trg_subject_table_before_insert';

        $this->connectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->connection);
        $this->connection->expects($this->once())
            ->method('getTriggerName')
            ->with($tableName, $time, $event)
            ->willReturn($triggerName);
        $this->assertSame($triggerName, $this->resource->getTriggerName($tableName, $time, $event));
    }
}
