<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Model\ResourceModel\Type\Db\ConnectionFactoryInterface;

class AclResourceTest extends \PHPUnit\Framework\TestCase
{
    const RESOURCE_NAME = \Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION;
    const CONNECTION_NAME = 'connection-name';
    const TABLE_PREFIX = 'prefix_';

    /**
     * @var \Magento\Framework\App\ResourceConnection\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * @var ConnectionFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connectionFactory;

    /**
     * @var \Magento\Framework\App\DeploymentConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $deploymentConfig;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connection;

    protected function setUp()
    {
        $this->connectionFactory = $this->getMockBuilder(ConnectionFactoryInterface::class)
            ->setMethods(['create'])
            ->getMockForAbstractClass();
        $this->config = $this->getMockBuilder(\Magento\Framework\App\ResourceConnection\ConfigInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConnectionName'])
            ->getMock();
        $this->config->expects($this->any())
            ->method('getConnectionName')
            ->with(self::RESOURCE_NAME)
            ->will($this->returnValue(self::CONNECTION_NAME));

        $this->deploymentConfig = $this->createMock(\Magento\Framework\App\DeploymentConfig::class);
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

        $this->connection = $this->getMockForAbstractClass(\Magento\Framework\DB\Adapter\AdapterInterface::class);
        $this->connection->expects($this->any())
            ->method('getTableName')
            ->will($this->returnArgument(0));

        $this->resource = new ResourceConnection(
            $this->config,
            $this->connectionFactory,
            $this->deploymentConfig
        );
    }

    /**
     * @expectedException \DomainException
     * @expectedExceptionMessage Connection "invalid" is not defined
     */
    public function testGetConnectionFail()
    {
        $this->resource->getConnectionByName('invalid');
    }

    public function testGetConnectionInitConnection()
    {
        $this->connectionFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->connection));
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
            ->will($this->returnValue($this->connection));
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
            ->will($this->returnValue($this->connection));
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
            ->will($this->returnValue($expectedIdxName));
        $this->connectionFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->connection));

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
            ->will($this->returnValue('fkName'));
        $this->connectionFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->connection));

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
            ->will($this->returnValue($this->connection));
        $this->connection->expects($this->once())
            ->method('getTriggerName')
            ->with($tableName, $time, $event)
            ->willReturn($triggerName);
        $this->assertSame($triggerName, $this->resource->getTriggerName($tableName, $time, $event));
    }
}
