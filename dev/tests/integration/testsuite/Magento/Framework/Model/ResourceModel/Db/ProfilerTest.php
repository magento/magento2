<?php
/**
 * Test for \Magento\Framework\Model\ResourceModel\Db\Profiler
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\ResourceModel\Db;

use Magento\Framework\Config\ConfigOptionsListConstants;

class ProfilerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_model;

    /**
     * @var string
     */
    protected static $_testResourceName = 'testtest_0000_setup';

    public static function setUpBeforeClass()
    {
        self::$_testResourceName = 'testtest_' . mt_rand(1000, 9999) . '_setup';

        \Magento\Framework\Profiler::enable();
    }

    public static function tearDownAfterClass()
    {
        \Magento\Framework\Profiler::disable();
    }

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Framework\App\ResourceConnection');
    }

    /**
     * @return \Magento\TestFramework\Db\Adapter\Mysql
     */
    protected function _getConnection()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $reader = $objectManager->get('Magento\Framework\App\DeploymentConfig');
        $dbConfig = $reader->getConfigData(ConfigOptionsListConstants::KEY_DB);
        $connectionConfig = $dbConfig['connection']['default'];
        $connectionConfig['profiler'] = [
            'class' => 'Magento\Framework\Model\ResourceModel\Db\Profiler',
            'enabled' => 'true',
        ];

        return $objectManager->create('Magento\TestFramework\Db\Adapter\Mysql', ['config' => $connectionConfig]);
    }

    /**
     * Init profiler during creation of DB connect
     *
     * @param string $selectQuery
     * @param int $queryType
     * @dataProvider profileQueryDataProvider
     */
    public function testProfilerInit($selectQuery, $queryType)
    {
        $connection = $this->_getConnection();

        /** @var \Magento\Framework\App\ResourceConnection $resource */
        $resource = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Framework\App\ResourceConnection');
        $testTableName = $resource->getTableName('setup_module');
        $selectQuery = sprintf($selectQuery, $testTableName);

        $result = $connection->query($selectQuery);
        if ($queryType == \Zend_Db_Profiler::SELECT) {
            $result->fetchAll();
        }

        /** @var \Magento\Framework\Model\ResourceModel\Db\Profiler $profiler */
        $profiler = $connection->getProfiler();
        $this->assertInstanceOf('Magento\Framework\Model\ResourceModel\Db\Profiler', $profiler);

        $queryProfiles = $profiler->getQueryProfiles($queryType);
        $this->assertCount(1, $queryProfiles);

        /** @var \Zend_Db_Profiler_Query $queryProfile */
        $queryProfile = end($queryProfiles);
        $this->assertInstanceOf('Zend_Db_Profiler_Query', $queryProfile);

        $this->assertEquals($selectQuery, $queryProfile->getQuery());
    }

    /**
     * @return array
     */
    public function profileQueryDataProvider()
    {
        return [
            ["SELECT * FROM %s", \Magento\Framework\DB\Profiler::SELECT],
            [
                "INSERT INTO %s (module, schema_version, data_version) " .
                "VALUES ('" .
                self::$_testResourceName .
                "', '1.1', '1.1')",
                \Magento\Framework\DB\Profiler::INSERT
            ],
            [
                "UPDATE %s SET schema_version = '1.2' WHERE module = '" . self::$_testResourceName . "'",
                \Magento\Framework\DB\Profiler::UPDATE
            ],
            [
                "DELETE FROM %s WHERE module = '" . self::$_testResourceName . "'",
                \Magento\Framework\DB\Profiler::DELETE
            ]
        ];
    }

    /**
     * Test correct event starting and stopping in magento profile during SQL query fail
     */
    public function testProfilerDuringSqlException()
    {
        /** @var \Zend_Db_Adapter_Pdo_Abstract $connection */
        $connection = $this->_getConnection();

        try {
            $connection->query('SELECT * FROM unknown_table');
        } catch (\Zend_Db_Statement_Exception $exception) {
        }

        if (!isset($exception)) {
            $this->fail("Expected exception didn't thrown!");
        }

        /** @var \Magento\Framework\App\ResourceConnection $resource */
        $resource = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Framework\App\ResourceConnection');
        $testTableName = $resource->getTableName('setup_module');
        $connection->query('SELECT * FROM ' . $testTableName);

        /** @var \Magento\Framework\Model\ResourceModel\Db\Profiler $profiler */
        $profiler = $connection->getProfiler();
        $this->assertInstanceOf('Magento\Framework\Model\ResourceModel\Db\Profiler', $profiler);

        $queryProfiles = $profiler->getQueryProfiles(\Magento\Framework\DB\Profiler::SELECT);
        $this->assertCount(2, $queryProfiles);
    }
}
