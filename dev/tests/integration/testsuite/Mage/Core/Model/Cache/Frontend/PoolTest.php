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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Core_Model_Cache_Frontend_PoolTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Cache_Frontend_Pool
     */
    protected $_model;

    /*
     * @var Mage_Core_Model_Config_Primary
     */
    protected $_configPrimary;

    public function setUp()
    {
        $this->_configPrimary = Mage::getSingleton('Mage_Core_Model_Config_Primary');
        $this->_model = new Mage_Core_Model_Cache_Frontend_Pool(
            $this->_configPrimary,
            Mage::getModel('Mage_Core_Model_Cache_Frontend_Factory')
        );
    }

    /**
     * @dataProvider cacheBackendsDataProvider
     */
    public function testGetCache($cacheBackendName)
    {
        $cacheTypePath = Mage_Core_Model_Cache_Frontend_Pool::XML_PATH_SETTINGS_DEFAULT . '/backend';
        $oldCacheBackend = (string)$this->_configPrimary->getNode($cacheTypePath);
        $this->_configPrimary->setNode($cacheTypePath, $cacheBackendName);

        $cache = $this->_model->get(Mage_Core_Model_Cache_Frontend_Pool::DEFAULT_FRONTEND_ID);
        $this->assertInstanceOf('Magento_Cache_FrontendInterface', $cache);
        $this->assertInstanceOf('Zend_Cache_Backend_Interface', $cache->getBackend());

        $this->_configPrimary->setNode($cacheTypePath, $oldCacheBackend);
    }

    public function cacheBackendsDataProvider()
    {
        return array(
            array('sqlite'),
            array('memcached'),
            array('apc'),
            array('xcache'),
            array('eaccelerator'),
            array('database'),
            array('File'),
            array('')
        );
    }
}
