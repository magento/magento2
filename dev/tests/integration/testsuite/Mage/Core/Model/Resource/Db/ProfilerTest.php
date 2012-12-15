<?php
/**
 * Test for Mage_Core_Model_Resource_Db_Profiler
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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Core_Model_Resource_Db_ProfilerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Resource
     */
    protected $_model;

    /**
     * @var string
     */
    protected static $_testResourceName = 'testtest_0000_setup';

    public static function setUpBeforeClass()
    {
        self::$_testResourceName = 'testtest_' . mt_rand(1000, 9999) . '_setup';

        Magento_Profiler::enable();
    }

    public static function tearDownAfterClass()
    {
        Magento_Profiler::disable();
    }

    public function setUp()
    {
        $this->_model = Mage::getModel('Mage_Core_Model_Resource');
    }

    /**
     * @return Varien_Simplexml_Element
     */
    protected function _getConnectionReadConfig()
    {
        $connReadConfig = Mage::getConfig()->getResourceConnectionConfig('core_read');
        $profilerConfig = $connReadConfig->addChild('profiler');
        $profilerConfig->addChild('class', 'Mage_Core_Model_Resource_Db_Profiler');
        $profilerConfig->addChild('enabled', 'true');

        return $connReadConfig;
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
        $connReadConfig = $this->_getConnectionReadConfig();
        /** @var Magento_Test_Db_Adapter_Mysql $connection */
        $connection = $this->_model->getConnection('core_read');

        /** @var Mage_Core_Model_Resource $resource */
        $resource = Mage::getSingleton('Mage_Core_Model_Resource');
        $testTableName = $resource->getTableName('core_resource');
        $selectQuery = sprintf($selectQuery, $testTableName);

        $result = $connection->query($selectQuery);
        if ($queryType == Zend_Db_Profiler::SELECT) {
            $result->fetchAll();
        }

        /** @var Mage_Core_Model_Resource_Db_Profiler $profiler */
        $profiler = $connection->getProfiler();
        $this->assertInstanceOf('Mage_Core_Model_Resource_Db_Profiler', $profiler);
        $this->assertAttributeEquals((string)$connReadConfig->type, '_type', $profiler);

        $queryProfiles = $profiler->getQueryProfiles($queryType);
        $this->assertCount(1, $queryProfiles);

        /** @var Zend_Db_Profiler_Query $queryProfile */
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
            array("SELECT * FROM %s", Varien_Db_Profiler::SELECT),
            array("INSERT INTO %s (code, version, data_version) "
                . "VALUES ('" . self::$_testResourceName . "', '1.1', '1.1')", Varien_Db_Profiler::INSERT),
            array("UPDATE %s SET version = '1.2' WHERE code = '" . self::$_testResourceName . "'",
                Varien_Db_Profiler::UPDATE),
            array("DELETE FROM %s WHERE code = '" . self::$_testResourceName . "'",
                Varien_Db_Profiler::DELETE),
        );
    }

    /**
     * Test correct event starting and stopping in magento profile during SQL query fail
     */
    public function testProfilerDuringSqlException()
    {
        /** @var Zend_Db_Adapter_Pdo_Abstract $connection */
        $connection = $this->_model->getConnection('core_read');

        try {
            $connection->query('SELECT * FROM unknown_table');
        } catch (Zend_Db_Statement_Exception $exception) {
        }

        if (!isset($exception)) {
            $this->fail("Expected exception didn't thrown!");
        }

        /** @var Mage_Core_Model_Resource $resource */
        $resource = Mage::getSingleton('Mage_Core_Model_Resource');
        $testTableName = $resource->getTableName('core_resource');
        $connection->query('SELECT * FROM ' . $testTableName);

        /** @var Mage_Core_Model_Resource_Db_Profiler $profiler */
        $profiler = $connection->getProfiler();
        $this->assertInstanceOf('Mage_Core_Model_Resource_Db_Profiler', $profiler);

        $queryProfiles = $profiler->getQueryProfiles(Varien_Db_Profiler::SELECT);
        $this->assertCount(2, $queryProfiles);
    }
}
