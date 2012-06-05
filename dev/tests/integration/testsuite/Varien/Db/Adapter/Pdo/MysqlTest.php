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
 * @category    Varien
 * @package     Varien_Db
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test for an PDO MySQL adapter
 */
class Varien_Db_Adapter_Pdo_MysqlTest extends PHPUnit_Framework_TestCase
{
    /**
     * DB connection instance
     *
     * @var Varien_Db_Adapter_Pdo_Mysql
     */
    protected $_connection = null;

    /**
     * Test lost connection re-initializing
     *
     * @covers Varien_Db_Adapter_Pdo_Mysql::raw_query
     * @covers Varien_Db_Adapter_Pdo_Mysql::query
     * @throws Exception
     */
    public function testWaitTimeout()
    {
        if (Magento_Test_Bootstrap::getInstance()->getDbVendorName() != 'mysql') {
            $this->markTestSkipped('Test is designed to run on MySQL only.');
        }
        if (!($this->_getConnection() instanceof Varien_Db_Adapter_Pdo_Mysql)) {
            $this->markTestSkipped('This test is for Varien_Db_Adapter_Pdo_Mysql');
        }
        try {
            $defaultWaitTimeout = $this->_getWaitTimeout();
            $minWaitTimeout = 1;
            $this->_setWaitTimeout($minWaitTimeout);
            $this->assertEquals($minWaitTimeout, $this->_getWaitTimeout(), 'Wait timeout was not changed');

            // Sleep for time greater than wait_timeout and try to perform query
            sleep($minWaitTimeout + 1);
            $result = $this->_getConnection()->raw_query('SELECT 1');
            $this->assertInstanceOf('Varien_Db_Statement_Pdo_Mysql', $result);
            // Restore wait_timeout
            $this->_setWaitTimeout($defaultWaitTimeout);
            $this->assertEquals($defaultWaitTimeout, $this->_getWaitTimeout(), 'Default wait timeout was not restored');
        } catch (Exception $e) {
            // Reset connection on failure to restore global variables
            $this->_getConnection()->closeConnection();
            throw $e;
        }
    }

    /**
     * Get session wait_timeout
     *
     * @return int
     */
    protected function _getWaitTimeout()
    {
        return (int) $this->_getConnection()->fetchOne('SELECT @@wait_timeout');
    }

    /**
     * Set session wait_timeout
     *
     * @param int $waitTimeout
     */
    protected function _setWaitTimeout($waitTimeout)
    {
        $this->_getConnection()->query("SET wait_timeout = {$waitTimeout}");
    }

    /**
     * Get DB connection
     *
     * @return Varien_Db_Adapter_Pdo_Mysql
     */
    protected function _getConnection()
    {
        if (is_null($this->_connection)) {
            /** @var $coreResource Mage_Core_Model_Resource */
            $coreResource = Mage::getSingleton('Mage_Core_Model_Resource');
            $this->_connection = $coreResource->getConnection(Mage_Core_Model_Resource::DEFAULT_WRITE_RESOURCE);
        }
        return $this->_connection;
    }
}
