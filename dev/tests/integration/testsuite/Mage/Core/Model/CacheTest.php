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
 * @category    Magento
 * @package     Mage_Core
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Core_Model_CacheTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Cache
     */
    protected $_model;

    public function setUp()
    {
        $this->_model = new Mage_Core_Model_Cache();
    }

    public function tearDown()
    {
        $this->_model = null;
    }

    public function testConstructorBackendDatabase()
    {
        $model = new Mage_Core_Model_Cache(array('backend' => 'Database'));
        $backend = $model->getFrontend()->getBackend();
        $this->assertInstanceOf('Varien_Cache_Backend_Database', $backend);
    }

    /**
     * @param string $optionCode
     * @param string $extensionRequired
     * @dataProvider constructorBackendTwoLevelsDataProvider
     */
    public function testConstructorBackendTwoLevels($optionCode, $extensionRequired)
    {
        if ($extensionRequired) {
            if (!extension_loaded($extensionRequired)) {
                $this->markTestSkipped("The PHP extension '{$extensionRequired}' is required for this test.");

            }
        }
        $model = new Mage_Core_Model_Cache(array('backend' => $optionCode));
        $backend = $model->getFrontend()->getBackend();
        $this->assertInstanceOf('Zend_Cache_Backend_TwoLevels', $backend);
    }

    /**
     * @return array
     */
    public function constructorBackendTwoLevelsDataProvider()
    {
        return array(
            array('Memcached', 'memcached'),
            array('Memcached', 'memcache'),
        );
    }

    public function testGetDbAdapter()
    {
        $this->assertInstanceOf('Zend_Db_Adapter_Abstract', $this->_model->getDbAdapter());
    }
}
