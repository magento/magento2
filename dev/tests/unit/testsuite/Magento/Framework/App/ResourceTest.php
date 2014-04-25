<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\App;

class ResourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_config;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_connectionFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cache;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_connection;

    /**
     * @var \Magento\Framework\App\Resource
     */
    protected $_resorce;

    const RESOURCE_NAME = \Magento\Framework\App\Resource::DEFAULT_READ_RESOURCE;

    const CONNECTION_NAME = 'Connection Name';

    public function setUp()
    {
        $this->_cache = $this->getMockBuilder('Magento\Framework\App\CacheInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->_connectionFactory = $this->getMockBuilder(
            'Magento\Framework\App\Resource\ConnectionFactory'
        )->disableOriginalConstructor()->setMethods(['create'])->getMock();
        $this->_connection = $this->getMockBuilder(
            'Magento\Framework\DB\Adapter\AdapterInterface'
        )->disableOriginalConstructor()->setMethods(
            []
        )->getMock();
        $this->_config = $this->getMockBuilder(
            'Magento\Framework\App\Resource\ConfigInterface'
        )->disableOriginalConstructor()->setMethods(['getConnectionName'])->getMock();
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_resorce = $objectManager->getObject(
            'Magento\Framework\App\Resource',
            [
                'cache' => $this->_cache,
                'resourceConfig' => $this->_config,
                'adapterFactory' => $this->_connectionFactory
            ]
        );
        $this->_config->expects($this->any())->method('getConnectionName')->with(self::RESOURCE_NAME)->will(
            $this->returnValue(self::CONNECTION_NAME)
        );
    }

    public function testGetConnectionFail()
    {
        $this->_connectionFactory->expects($this->once())->method('create')->with(self::CONNECTION_NAME)->will(
            $this->returnValue(null)
        );
        $this->assertFalse($this->_resorce->getConnection(self::RESOURCE_NAME));
    }

    public function testGetConnectionInitConnection()
    {
        $this->_connectionFactory->expects($this->once())->method('create')->with(self::CONNECTION_NAME)->will(
            $this->returnValue($this->_connection)
        );
        $this->_connection->expects($this->once())->method('setCacheAdapter')->with(
            $this->isInstanceOf('Magento\Framework\Cache\FrontendInterface')
        );
        $frontendInterface = $this->getMockBuilder(
            'Magento\Framework\Cache\FrontendInterface'
        )->disableOriginalConstructor()->setMethods([])->getMock();
        $this->_cache->expects($this->once())->method('getFrontend')->will($this->returnValue($frontendInterface));

        $this->assertInstanceOf(
            'Magento\Framework\DB\Adapter\AdapterInterface',
            $this->_resorce->getConnection(self::RESOURCE_NAME)
        );
        $this->assertInstanceOf(
            'Magento\Framework\DB\Adapter\AdapterInterface',
            $this->_resorce->getConnection(self::RESOURCE_NAME)
        );
    }

    public function testGetTableName()
    {
        $expected = 'tableName';
        $modelEntity = $this->prepareTableName($expected);
        $this->assertEquals($expected, $this->_resorce->getTableName($modelEntity));
    }

    public function testGetTableNameWithPrefix()
    {
        $this->setConnection();
        $modelEntity = ['modelEntity', 'tableSuffix'];
        $expected = 'tablename';
        $tablePrefix = 'tablePrefix';
        $this->_resorce->setTablePrefix($tablePrefix);

        $this->_connection->expects($this->once())->method('getTableName')->with(
            $tablePrefix . $modelEntity[0] . '_' . $modelEntity[1]
        )->will($this->returnValue($expected));

        $this->assertEquals($expected, $this->_resorce->getTableName($modelEntity));
    }

    public function testGetIdxName()
    {
        $expectedTableName = 'tablename';
        $modelEntity = $this->prepareTableName($expectedTableName);

        $fields = ['field'];
        $this->_connection->expects($this->once())->method('getIndexName')->with(
            $expectedTableName, $fields, \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
        )->will($this->returnValue('idxName'));
        $this->assertEquals(
            'idxName',
            $this->_resorce->getIdxName(
                $modelEntity,
                $fields,
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
            )
        );
    }

    public function testGetFkName()
    {
        $expectedTableName = 'tablename';
        $modelEntity = $this->prepareTableName($expectedTableName, false);
        $columnName = 'columnName';

        $this->_connection->expects($this->once())->method('getForeignKeyName')->with(
            $expectedTableName, $columnName, $expectedTableName, $columnName
        )->will($this->returnValue('fkName'));

        $this->assertEquals('fkName', $this->_resorce->getFkName($modelEntity, $columnName, $modelEntity, $columnName));
    }

    /**
     * Prepares data for \Resource::getTableName($modelEntity)
     *
     * @param string $expected
     * @param bool $useSuffix does an entity has a suffix
     * @return array $modelEntity
     */
    private function prepareTableName($expected, $useSuffix = true)
    {
        $this->setConnection();
        $modelEntity = ['modelEntity', 'tableSuffix'];
        $mappedName = 'mappedName';
        $this->_resorce->setMappedTableName($modelEntity[0], $mappedName);

        $this->_connection->expects($this->any())->method('getTableName')->with(
            $useSuffix ? $mappedName . '_' . $modelEntity[1] : $mappedName
        )->will($this->returnValue($expected));
        return $useSuffix ? $modelEntity : $modelEntity[0];
    }

    /**
     * Sets connection for resource
     */
    private function setConnection()
    {
        $connectionSetter = \Closure::bind(
            function (\Magento\Framework\App\Resource $resource, $connection, $connectionName) {
                $resource->_connections[$connectionName] = $connection;
            }, null, 'Magento\Framework\App\Resource'
        );

        $connectionSetter($this->_resorce, $this->_connection, self::CONNECTION_NAME);
    }
}
