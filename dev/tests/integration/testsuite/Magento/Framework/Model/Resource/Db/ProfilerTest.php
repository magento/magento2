<?php
/**
 * Test for \Magento\Framework\Model\Resource\Db\Profiler
 *
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
namespace Magento\Framework\Model\Resource\Db;

class ProfilerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Resource
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
            ->create('Magento\Framework\App\Resource');
    }

    /**
     * @return \Magento\TestFramework\Db\Adapter\Mysql
     */
    protected function _getConnectionRead()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $localConfig = $objectManager->get('Magento\Framework\App\Arguments');
        $connectionConfig = $localConfig->getConnection('default');
        $connectionConfig['profiler'] = array(
            'class' => 'Magento\Framework\Model\Resource\Db\Profiler',
            'enabled' => 'true'
        );
        $connectionConfig['dbname'] = $connectionConfig['dbName'];

        return $objectManager->create('Magento\TestFramework\Db\Adapter\Mysql', array('config' => $connectionConfig));
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
        $connection = $this->_getConnectionRead();

        /** @var \Magento\Framework\App\Resource $resource */
        $resource = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\App\Resource');
        $testTableName = $resource->getTableName('core_resource');
        $selectQuery = sprintf($selectQuery, $testTableName);

        $result = $connection->query($selectQuery);
        if ($queryType == \Zend_Db_Profiler::SELECT) {
            $result->fetchAll();
        }

        /** @var \Magento\Framework\Model\Resource\Db\Profiler $profiler */
        $profiler = $connection->getProfiler();
        $this->assertInstanceOf('Magento\Framework\Model\Resource\Db\Profiler', $profiler);

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
        return array(
            array("SELECT * FROM %s", \Magento\Framework\DB\Profiler::SELECT),
            array(
                "INSERT INTO %s (code, version, data_version) " .
                "VALUES ('" .
                self::$_testResourceName .
                "', '1.1', '1.1')",
                \Magento\Framework\DB\Profiler::INSERT
            ),
            array(
                "UPDATE %s SET version = '1.2' WHERE code = '" . self::$_testResourceName . "'",
                \Magento\Framework\DB\Profiler::UPDATE
            ),
            array(
                "DELETE FROM %s WHERE code = '" . self::$_testResourceName . "'",
                \Magento\Framework\DB\Profiler::DELETE
            )
        );
    }

    /**
     * Test correct event starting and stopping in magento profile during SQL query fail
     */
    public function testProfilerDuringSqlException()
    {
        /** @var \Zend_Db_Adapter_Pdo_Abstract $connection */
        $connection = $this->_getConnectionRead();

        try {
            $connection->query('SELECT * FROM unknown_table');
        } catch (\Zend_Db_Statement_Exception $exception) {
        }

        if (!isset($exception)) {
            $this->fail("Expected exception didn't thrown!");
        }

        /** @var \Magento\Framework\App\Resource $resource */
        $resource = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\App\Resource');
        $testTableName = $resource->getTableName('core_resource');
        $connection->query('SELECT * FROM ' . $testTableName);

        /** @var \Magento\Framework\Model\Resource\Db\Profiler $profiler */
        $profiler = $connection->getProfiler();
        $this->assertInstanceOf('Magento\Framework\Model\Resource\Db\Profiler', $profiler);

        $queryProfiles = $profiler->getQueryProfiles(\Magento\Framework\DB\Profiler::SELECT);
        $this->assertCount(2, $queryProfiles);
    }
}
