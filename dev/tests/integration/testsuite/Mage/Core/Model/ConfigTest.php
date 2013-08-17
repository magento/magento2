<?php
/**
 * Integration test for Mage_Core_Model_Config
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * First part of Mage_Core_Model_Config testing:
 * - general behaviour is tested
 *
 * @see Mage_Core_Model_ConfigFactoryTest
 *
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class Mage_Core_Model_ConfigTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        Mage::app()->getCacheInstance()->banUse('config');
    }

    public function testSetNode()
    {
        $model = $this->_createModel();
        /* some existing node should be used */
        $model->setNode('admin/routers/adminhtml/use', 'test');
        $this->assertEquals('test', (string) $model->getNode('admin/routers/adminhtml/use'));
    }

    public function testDetermineOmittedNamespace()
    {
        $model = $this->_createModel();
        $this->assertEquals('cms', $model->determineOmittedNamespace('cms'));
        $this->assertEquals('Mage_Cms', $model->determineOmittedNamespace('cms', true));
        $this->assertEquals('', $model->determineOmittedNamespace('nonexistent'));
        $this->assertEquals('', $model->determineOmittedNamespace('nonexistent', true));
    }

    public function testGetDistroBaseUrl()
    {
        $_SERVER['SCRIPT_NAME'] = __FILE__;
        $_SERVER['HTTP_HOST'] = 'example.com';
        $this->assertEquals('http://example.com/', $this->_createModel()->getDistroBaseUrl());
    }

    public function testGetModuleConfig()
    {
        $model = $this->_createModel();
        $this->assertInstanceOf('Mage_Core_Model_Config_Element', $model->getModuleConfig());
        $this->assertInstanceOf('Mage_Core_Model_Config_Element', $model->getModuleConfig('Mage_Core'));
    }

    public function testGetModuleDir()
    {
        $model = $this->_createModel();
        foreach (array('etc', 'controllers', 'sql', 'data', 'locale') as $type) {
            $dir = $model->getModuleDir($type, 'Mage_Core');
            $this->assertStringEndsWith($type, $dir);
            $this->assertContains('Mage' . DIRECTORY_SEPARATOR . 'Core', $dir);
        }
        $this->assertTrue(is_dir($this->_createModel()->getModuleDir('etc', 'Mage_Core')));
    }

    public function testGetStoresConfigByPath()
    {
        $model = $this->_createModel();

        // default
        $baseUrl = $model->getStoresConfigByPath('web/unsecure/base_url');
        $this->assertArrayHasKey(0, $baseUrl);
        $this->assertArrayHasKey(1, $baseUrl);

        // $allowValues
        $baseUrl = $model->getStoresConfigByPath('web/unsecure/base_url', array(uniqid()));
        $this->assertEquals(array(), $baseUrl);

        // store code
        $baseUrl = $model->getStoresConfigByPath('web/unsecure/base_url', array(), 'code');
        $this->assertArrayHasKey('default', $baseUrl);
        $this->assertArrayHasKey('admin', $baseUrl);

        // store name
        $baseUrl = $model->getStoresConfigByPath('web/unsecure/base_url', array(), 'name');
        $this->assertArrayHasKey('Default Store View', $baseUrl);
        $this->assertArrayHasKey('Admin', $baseUrl);
    }

    /**
     * Test shouldUrlBeSecure() function for "Use Secure URLs in Frontend" = Yes
     *
     * @magentoConfigFixture current_store web/secure/use_in_frontend 1
     */
    public function testShouldUrlBeSecureWhenSecureUsedInFrontend()
    {
        $model = $this->_createModel();
        $this->assertFalse($model->shouldUrlBeSecure('/'));
        $this->assertTrue($model->shouldUrlBeSecure('/checkout/onepage'));
    }

    /**
     * Test shouldUrlBeSecure() function for "Use Secure URLs in Frontend" = No
     *
     * @magentoConfigFixture current_store web/secure/use_in_frontend 0
     */
    public function testShouldUrlBeSecureWhenSecureNotUsedInFrontend()
    {
        $model = $this->_createModel();
        $this->assertFalse($model->shouldUrlBeSecure('/'));
        $this->assertFalse($model->shouldUrlBeSecure('/checkout/onepage'));
    }



    /**
     * Instantiate Mage_Core_Model_Config and initialize (load configuration) if needed
     *
     * @param array $arguments
     * @return Mage_Core_Model_Config
     */
    protected function _createModel(array $arguments = array())
    {
        /** @var $model Mage_Core_Model_Config */
        $model = Mage::getModel('Mage_Core_Model_Config', $arguments);
        return $model;
    }

    /**
     * @magentoAppIsolation enabled
     * @expectedException InvalidArgumentException
     */
    public function testGetAreaConfigThrowsExceptionIfNonexistentAreaIsRequested()
    {
        Mage::app()->getConfig()->getAreaConfig('non_existent_area_code');
    }

    /**
     * Check if areas loaded correctly from configuration
     */
    public function testGetAreas()
    {
        $this->markTestIncomplete('MAGETWO-6406');
        $model = $this->_createModel(array('sourceData' => __DIR__ . '/../_files/etc/config.xml'));

        $allowedAreas = $model->getAreas();
        $this->assertNotEmpty($allowedAreas, 'Areas are not initialized');

        $this->assertArrayHasKey('test_area1', $allowedAreas, 'Test area #1 is not loaded');

        $testAreaExpected = array(
            'base_controller' => 'Mage_Core_Controller_Varien_Action',
            'frontName' => 'TESTAREA',
            'routers'         => array(
                'test_router1' => array(
                    'class'   => 'Mage_Core_Controller_Varien_Router_Default'
                ),
                'test_router2' => array(
                    'class'   => 'Mage_Core_Controller_Varien_Router_Default'
                ),
            )
        );
        $this->assertEquals($testAreaExpected, $allowedAreas['test_area1'], 'Test area is not loaded correctly');

        $this->assertArrayNotHasKey('test_area2', $allowedAreas, 'Test area #2 is loaded by mistake');
        $this->assertArrayNotHasKey('test_area3', $allowedAreas, 'Test area #3 is loaded by mistake');
        $this->assertArrayNotHasKey('test_area4', $allowedAreas, 'Test area #4 is loaded by mistake');
        $this->assertArrayNotHasKey('test_area5', $allowedAreas, 'Test area #5 is loaded by mistake');
    }

    /**
     * Check if routers loaded correctly from configuration
     */
    public function testGetRouters()
    {
        $this->markTestIncomplete('MAGETWO-6406');
        $model = $this->_createModel(array('sourceData' => __DIR__ . '/../_files/etc/config.xml'));

        $loadedRouters = $model->getRouters();
        $this->assertArrayHasKey('test_router1', $loadedRouters, 'Test router #1 is not initialized in test area.');
        $this->assertArrayHasKey('test_router2', $loadedRouters, 'Test router #2 is not initialized in test area.');

        $testRouterExpected = array(
            'class'           => 'Mage_Core_Controller_Varien_Router_Default',
            'area'            => 'test_area1',
            'frontName'       => 'TESTAREA',
            'base_controller' => 'Mage_Core_Controller_Varien_Action'
        );
        $this->assertEquals($testRouterExpected, $loadedRouters['test_router1'], 'Test router is not loaded correctly');
        $this->assertEquals($testRouterExpected, $loadedRouters['test_router2'], 'Test router is not loaded correctly');
    }
}
