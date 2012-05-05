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

/**
 * First part of Mage_Core_Model_Config testing:
 * - general behaviour is tested
 *
 * @see Mage_Core_Model_ConfigFactoryTest
 */
class Mage_Core_Model_ConfigTest extends PHPUnit_Framework_TestCase
{
    protected static $_options = array();

    public static function setUpBeforeClass()
    {
        self::$_options = Magento_Test_Bootstrap::getInstance()->getAppOptions();
    }

    public function testGetResourceModel()
    {
        $this->assertInstanceOf('Mage_Core_Model_Resource_Config', $this->_createModel(true)->getResourceModel());
    }

    public function testGetOptions()
    {
        $this->assertInstanceOf('Mage_Core_Model_Config_Options', $this->_createModel(true)->getOptions());
    }

    public function testSetOptions()
    {
        $model = $this->_createModel();
        $key = uniqid('key');
        $model->setOptions(array($key  => 'value'));
        $this->assertEquals('value', $model->getOptions()->getData($key));
    }

    public function testInit()
    {
        $model = $this->_createModel();
        $this->assertFalse($model->getNode());
        $model->init(self::$_options);
        $this->assertInstanceOf('Varien_Simplexml_Element', $model->getNode());
    }

    public function testLoadBase()
    {
        $model = $this->_createModel();
        $this->assertFalse($model->getNode());
        $model->setOptions(self::$_options);
        $model->loadBase();
        $this->assertInstanceOf('Varien_Simplexml_Element', $model->getNode('global'));
    }

    public function testLoadModulesCache()
    {
        $model = $this->_createModel();
        $this->assertTrue($model->loadModulesCache());
        $this->assertInstanceOf('Mage_Core_Model_Config_Element', $model->getNode());
    }

    public function testLoadModules()
    {
        $model = $this->_createModel();
        $model->setOptions(self::$_options);
        $model->loadBase();
        $this->assertFalse($model->getNode('modules'));
        $model->loadModules();
        $this->assertInstanceOf('Mage_Core_Model_Config_Element', $model->getNode('modules'));
    }

    public function testIsLocalConfigLoaded()
    {
        $model = $this->_createModel();
        $this->assertFalse($model->isLocalConfigLoaded());
        $model->setOptions(self::$_options);
        $model->loadBase();
        $this->assertTrue($model->isLocalConfigLoaded());
    }

    public function testLoadDb()
    {
        $samplePath = 'general/locale/firstday';

        // emulate a system config value in database
        $configResource = new Mage_Core_Model_Resource_Config;
        $configResource->saveConfig($samplePath, 1, 'default', 0);

        try {
            $model = $this->_createModel();
            $model->setOptions(self::$_options);
            $model->loadBase();
            $model->loadModules();

            // load and assert value
            $model->loadDb();
            $this->assertEquals('1', (string)$model->getNode("default/{$samplePath}"));
            $configResource->deleteConfig($samplePath, 'default', 0);
        } catch (Exception $e) {
            $configResource->deleteConfig($samplePath, 'default', 0);
            throw $e;
        }
    }

    public function testGetCache()
    {
        $this->assertInstanceOf('Varien_Cache_Core', $this->_createModel()->getCache());
    }

    public function testSaveCache()
    {
        $model = $this->_createModel(true);
        $model->removeCache();
        $this->assertFalse($model->loadCache());

        $model->saveCache(array(Mage_Core_Model_Cache::OPTIONS_CACHE_ID));
        $this->assertTrue($model->loadCache());
        $this->assertInstanceOf('Mage_Core_Model_Config_Element', $model->getNode());
    }

    public function testRemoveCache()
    {
        $model = $this->_createModel();
        $model->removeCache();
        $this->assertFalse($model->loadCache());
    }

    public function testGetSectionNode()
    {
        $this->assertInstanceOf(
            'Mage_Core_Model_Config_Element', $this->_createModel(true)->getSectionNode(array('admin'))
        );
    }

    public function testGetNode()
    {
        $model = $this->_createModel();
        $this->assertFalse($model->getNode());
        $model->init(self::$_options);
        $this->assertInstanceOf('Mage_Core_Model_Config_Element', $model->getNode());
        $this->assertInstanceOf('Mage_Core_Model_Config_Element', $model->getNode(null, 'store', 1));
        $this->assertInstanceOf('Mage_Core_Model_Config_Element', $model->getNode(null, 'website', 1));
    }

    public function testSetNode()
    {
        $model = $this->_createModel();
        $model->init(self::$_options);
        /* some existing node should be used */
        $model->setNode('admin/routers/adminhtml/use', 'test');
        $this->assertEquals('test', (string) $model->getNode('admin/routers/adminhtml/use'));
    }

    public function testDetermineOmittedNamespace()
    {
        $model = $this->_createModel(true);
        $this->assertEquals('cms', $model->determineOmittedNamespace('cms'));
        $this->assertEquals('Mage_Cms', $model->determineOmittedNamespace('cms', true));
        $this->assertEquals('', $model->determineOmittedNamespace('nonexistent'));
        $this->assertEquals('', $model->determineOmittedNamespace('nonexistent', true));
    }

    public function testLoadModulesConfiguration()
    {
        $config = $this->_createModel(true)->loadModulesConfiguration('adminhtml.xml');
        $this->assertInstanceOf('Mage_Core_Model_Config_Base', $config);
        $this->assertInstanceOf('Mage_Core_Model_Config_Element', $config->getNode('menu'));
        $this->assertInstanceOf('Mage_Core_Model_Config_Element', $config->getNode('acl'));
    }

    public function testGetModuleConfigurationFiles()
    {
        $files = $this->_createModel(true)->getModuleConfigurationFiles('config.xml');
        $this->assertInternalType('array', $files);
        $this->assertNotEmpty($files);
        foreach ($files as $file) {
            $this->assertStringEndsWith(DIRECTORY_SEPARATOR . 'config.xml', $file);
            $this->assertFileExists($file);
        }
    }

    public function testGetTempVarDir()
    {
        $this->assertTrue(is_dir($this->_createModel()->getTempVarDir()));
    }

    public function testGetDistroServerVars()
    {
        $_SERVER['SCRIPT_NAME'] = __FILE__;
        $_SERVER['HTTP_HOST'] = 'example.com';
        $vars = $this->_createModel()->getDistroServerVars();
        $this->assertArrayHasKey('root_dir', $vars);
        $this->assertArrayHasKey('app_dir', $vars);
        $this->assertArrayHasKey('var_dir', $vars);
        $this->assertArrayHasKey('base_url', $vars);
        $this->assertEquals('http://example.com/', $vars['base_url']);
    }

    public function testSubstDistroServerVars()
    {
        $this->assertEquals('http://localhost/', $this->_createModel()->substDistroServerVars('{{base_url}}'));
    }

    public function testGetModuleConfig()
    {
        $model = $this->_createModel(true);
        $this->assertInstanceOf('Mage_Core_Model_Config_Element', $model->getModuleConfig());
        $this->assertInstanceOf('Mage_Core_Model_Config_Element', $model->getModuleConfig('Mage_Core'));
    }

    public function testGetVarDir()
    {
        $dir = $this->_createModel()->getVarDir();
        $this->assertTrue(is_dir($dir));
        $this->assertTrue(is_writable($dir));
    }

    public function testCreateDirIfNotExists()
    {
        $model = $this->_createModel();
        $dir = $model->getVarDir() . DIRECTORY_SEPARATOR . uniqid('dir');
        try {
            $this->assertFalse(is_dir($dir));
            $this->assertTrue($model->createDirIfNotExists($dir));
            rmdir($dir);
        } catch (Exception $e) {
            rmdir($dir);
            throw $e;
        }
    }

    public function testGetModuleDir()
    {
        $model = $this->_createModel(true);
        foreach (array('etc', 'controllers', 'sql', 'data', 'locale') as $type) {
            $dir = $model->getModuleDir($type, 'Mage_Core');
            $this->assertStringEndsWith($type, $dir);
            $this->assertContains('Mage' . DIRECTORY_SEPARATOR . 'Core', $dir);
        }
        $this->assertTrue(is_dir($this->_createModel(true)->getModuleDir('etc', 'Mage_Core')));
    }

    public function testLoadEventObservers()
    {
        $this->_createModel(true)->loadEventObservers('global');
        $this->assertArrayHasKey('log_log_clean_after', Mage::getEvents()->getAllEvents());
    }

    public function testGetPathVars()
    {
        $result = $this->_createModel()->getPathVars();
        $this->assertArrayHasKey('baseUrl', $result);
        $this->assertArrayHasKey('baseSecureUrl', $result);
    }

    public function testGetResourceConfig()
    {
        $this->assertInstanceOf(
            'Mage_Core_Model_Config_Element', $this->_createModel(true)->getResourceConfig('cms_setup')
        );
    }

    public function testGetResourceConnectionConfig()
    {
        $this->assertInstanceOf(
            'Mage_Core_Model_Config_Element', $this->_createModel(true)->getResourceConnectionConfig('core_read')
        );
    }

    public function testGetResourceTypeConfig()
    {
        $this->assertInstanceOf(
            'Mage_Core_Model_Config_Element', $this->_createModel(true)->getResourceTypeConfig('pdo_mysql')
        );
    }

    public function testGetStoresConfigByPath()
    {
        $model = $this->_createModel(true);

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
        $model = $this->_createModel(true);
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
        $model = $this->_createModel(true);
        $this->assertFalse($model->shouldUrlBeSecure('/'));
        $this->assertFalse($model->shouldUrlBeSecure('/checkout/onepage'));
    }

    public function testGetTablePrefix()
    {
        $_prefix = 'prefix_';
        $_model = $this->_createModel(true);
        $_model->setNode('global/resources/db/table_prefix', $_prefix);
        $this->assertEquals($_prefix, (string)$_model->getTablePrefix());
    }

    public function testGetEventConfig()
    {
        $this->assertInstanceOf('Mage_Core_Model_Config_Element',
            $this->_createModel(true)->getEventConfig('global', 'controller_front_init_routers')
        );
    }

    public function testSaveDeleteConfig()
    {
        $model = $this->_createModel(true);
        $model->saveConfig('web/url/redirect_to_base', 0);
        try {
            $model->reinit();
            $this->assertEquals('0', (string)$model->getNode('default/web/url/redirect_to_base'));

            $model->deleteConfig('web/url/redirect_to_base');
            $model->reinit();
            $this->assertEquals('1', (string)$model->getNode('default/web/url/redirect_to_base'));
        } catch (Exception $e) {
            $model->deleteConfig('web/url/redirect_to_base');
            throw $e;
        }
    }

    public function testGetFieldset()
    {
        $fieldset = $this->_createModel(true)->getFieldset('customer_account');
        $this->assertObjectHasAttribute('prefix', $fieldset);
        $this->assertObjectHasAttribute('firstname', $fieldset);
        $this->assertObjectHasAttribute('middlename', $fieldset);
        $this->assertObjectHasAttribute('lastname', $fieldset);
        $this->assertObjectHasAttribute('suffix', $fieldset);
        $this->assertObjectHasAttribute('email', $fieldset);
        $this->assertObjectHasAttribute('password', $fieldset);
    }

    /**
     * Instantiate Mage_Core_Model_Config and initialize (load configuration) if needed
     *
     * @param bool $initialize
     * @return Mage_Core_Model_Config
     */
    protected function _createModel($initialize = false)
    {
        $model = new Mage_Core_Model_Config;
        if ($initialize) {
            $model->init(self::$_options);
        }
        return $model;
    }

    /**
     * Check if areas loaded correctly from configuration
     *
     * @magentoAppIsolation enabled
     * @magentoDataFixture Mage/Core/_files/load_configuration.php
     */
    public function testGetAreas()
    {
        $allowedAreas = Mage::app()->getConfig()->getAreas();
        $this->assertNotEmpty($allowedAreas, 'Areas are not initialized');

        $this->assertArrayHasKey('test_area1', $allowedAreas, 'Test area #1 is not loaded');

        $testAreaExpected = array(
            'base_controller' => 'Mage_Core_Controller_Varien_Action',
            'routers'         => array(
                'test_router1' => array(
                    'class'   => 'Mage_Core_Controller_Varien_Router_Default'
                ),
                'test_router2' => array(
                    'class'   => 'Mage_Core_Controller_Varien_Router_Default'
                )
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
     *
     * @magentoAppIsolation enabled
     * @magentoDataFixture Mage/Core/_files/load_configuration.php
     */
    public function testGetRouters()
    {
        $loadedRouters = Mage::app()->getConfig()->getRouters();
        $this->assertArrayHasKey('test_router1', $loadedRouters, 'Test router #1 is not initialized in test area.');
        $this->assertArrayHasKey('test_router2', $loadedRouters, 'Test router #2 is not initialized in test area.');

        $testRouterExpected = array(
            'class'           => 'Mage_Core_Controller_Varien_Router_Default',
            'area'            => 'test_area1',
            'base_controller' => 'Mage_Core_Controller_Varien_Action'
        );
        $this->assertEquals($testRouterExpected, $loadedRouters['test_router1'], 'Test router is not loaded correctly');
        $this->assertEquals($testRouterExpected, $loadedRouters['test_router2'], 'Test router is not loaded correctly');
    }
}
