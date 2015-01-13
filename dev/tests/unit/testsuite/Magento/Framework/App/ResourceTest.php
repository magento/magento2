<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App;

class ResourceTest extends \PHPUnit_Framework_TestCase
{
    const RESOURCE_NAME = \Magento\Framework\App\Resource::DEFAULT_READ_RESOURCE;
    const CONNECTION_NAME = 'Connection Name';
    const TABLE_PREFIX = 'prefix_';

    /**
     * @var \Magento\Framework\App\Resource\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_config;

    /**
     * @var \Magento\Framework\Model\Resource\Type\Db\ConnectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_connectionFactory;

    /**
     * @var \Magento\Framework\App\DeploymentConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $deploymentConfig;

    /**
     * @var \Magento\Framework\App\Resource
     */
    protected $resource;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connection;

    public function setUp()
    {
        $this->_connectionFactory = $this->getMockBuilder('Magento\Framework\Model\Resource\Type\Db\ConnectionFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->_config = $this->getMockBuilder('Magento\Framework\App\Resource\ConfigInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getConnectionName'])
            ->getMock();
        $this->_config->expects($this->any())
            ->method('getConnectionName')
            ->with(self::RESOURCE_NAME)
            ->will($this->returnValue(self::CONNECTION_NAME));

        $this->deploymentConfig = $this->getMock('Magento\Framework\App\DeploymentConfig', [], [], '', false);
        $this->deploymentConfig->expects($this->any())
            ->method('getSegment')
            ->with(\Magento\Framework\App\DeploymentConfig\DbConfig::CONFIG_KEY)
            ->will($this->returnValue(
                    [
                        'connection' => [
                            'default' => [
                                'host' => 'localhost',
                                'dbname' => 'magento',
                                'username' => 'username',
                            ],
                            self::CONNECTION_NAME => [
                                'host' => 'localhost',
                                'dbname' => 'magento',
                                'username' => 'username',
                            ],
                        ],
                    ]
                )
            );

        $this->connection = $this->getMockForAbstractClass('Magento\Framework\DB\Adapter\AdapterInterface');
        $this->connection->expects($this->any())
            ->method('getTableName')
            ->will($this->returnArgument(0));

        $this->resource = new Resource(
            $this->_config,
            $this->_connectionFactory,
            $this->deploymentConfig,
            self::TABLE_PREFIX
        );
    }

    public function testGetConnectionFail()
    {
        $this->_connectionFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue(null));
        $this->assertFalse($this->resource->getConnection(self::RESOURCE_NAME));
    }

    public function testGetConnectionInitConnection()
    {
        $this->_connectionFactory->expects($this->once())
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
        $this->_connectionFactory->expects($this->once())
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
        $this->_connectionFactory->expects($this->once())
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
        $this->_connectionFactory->expects($this->once())
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
        $this->_connectionFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->connection));

        $this->assertEquals('fkName', $this->resource->getFkName($table, $columnName, $refTable, $refColumnName));
    }
}
