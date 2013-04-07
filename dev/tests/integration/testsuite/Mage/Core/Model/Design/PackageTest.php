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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @magentoDataFixture Mage/Core/Model/_files/design/themes.php
 */
class Mage_Core_Model_Design_PackageTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Design_Package
     */
    protected $_model;

    public static function setUpBeforeClass()
    {
        $themeDir = Mage::getBaseDir(Mage_Core_Model_Dir::MEDIA) . 'theme';
        $filesystem = Mage::getObjectManager()->create('Magento_Filesystem');
        $filesystem->delete($themeDir . '/frontend');
        $filesystem->delete($themeDir . '/_merged');

        $ioAdapter = new Varien_Io_File();
        $ioAdapter->cp(
            Mage::getBaseDir(Mage_Core_Model_Dir::PUB_LIB) . '/prototype/prototype.js',
            Mage::getBaseDir(Mage_Core_Model_Dir::PUB_LIB) . '/prototype/prototype.min.js'
        );
    }

    public static function tearDownAfterClass()
    {
        $ioAdapter = new Varien_Io_File();
        $ioAdapter->rm(Mage::getBaseDir(Mage_Core_Model_Dir::PUB_LIB) . '/prototype/prototype.min.js');
    }

    protected function setUp()
    {
        $this->_model = Mage::getModel('Mage_Core_Model_Design_Package');
    }

    /**
     * Emulate fixture design theme
     *
     * @param string $themePath
     */
    protected function _emulateFixtureTheme($themePath = 'test/default')
    {
        Magento_Test_Helper_Bootstrap::getInstance()->reinitialize(array(
            Mage::PARAM_APP_DIRS => array(
                Mage_Core_Model_Dir::THEMES => realpath(__DIR__ . '/../_files/design'),
            ),
        ));
        $this->_model = Mage::getModel('Mage_Core_Model_Design_Package');
        $this->_model->setDesignTheme($themePath);
    }

    public function testSetGetArea()
    {
        $this->assertEquals(Mage_Core_Model_Design_Package::DEFAULT_AREA, $this->_model->getArea());
        $this->_model->setArea('test');
        $this->assertEquals('test', $this->_model->getArea());
    }

    public function testSetDesignTheme()
    {
        $this->_model->setDesignTheme('test/test', 'test');
        $this->assertEquals('test', $this->_model->getArea());
        $this->assertEquals(null, $this->_model->getDesignTheme()->getThemePath());
    }

    public function testGetDesignTheme()
    {
        $this->assertInstanceOf('Mage_Core_Model_Theme', $this->_model->getDesignTheme());
    }

    /**
     * @magentoConfigFixture frontend/design/theme/full_name f
     * @magentoConfigFixture install/design/theme/full_name i
     * @magentoConfigFixture adminhtml/design/theme/full_name b
     * @magentoConfigFixture current_store design/theme/theme_id 0
     */
    public function testGetConfigurationDesignThemeDefaults()
    {
        $this->assertEquals('f', $this->_model->getConfigurationDesignTheme());
        $this->assertEquals('f', $this->_model->getConfigurationDesignTheme('frontend'));
        $this->assertEquals('f', $this->_model->getConfigurationDesignTheme('frontend', array('store' => 0)));
        $this->assertEquals('f', $this->_model->getConfigurationDesignTheme('frontend', array('store' => null)));
        $this->assertEquals('i', $this->_model->getConfigurationDesignTheme('install'));
        $this->assertEquals('i', $this->_model->getConfigurationDesignTheme('install', array('store' => uniqid())));
        $this->assertEquals('b', $this->_model->getConfigurationDesignTheme('adminhtml'));
        $this->assertEquals('b', $this->_model->getConfigurationDesignTheme('adminhtml', array('store' => uniqid())));
    }

    /**
     * @magentoConfigFixture current_store design/theme/theme_id one
     * @magentoConfigFixture fixturestore_store design/theme/theme_id two
     * @magentoDataFixture Mage/Core/_files/store.php
     */
    public function testGetConfigurationDesignThemeStore()
    {
        $storeId = Mage::app()->getStore()->getId();
        $this->assertEquals('one', $this->_model->getConfigurationDesignTheme());
        $this->assertEquals('one', $this->_model->getConfigurationDesignTheme(null, array('store' => $storeId)));
        $this->assertEquals('one', $this->_model->getConfigurationDesignTheme('frontend', array('store' => $storeId)));
        $this->assertEquals('two', $this->_model->getConfigurationDesignTheme(null, array('store' => 'fixturestore')));
        $this->assertEquals('two', $this->_model->getConfigurationDesignTheme(
            'frontend', array('store' => 'fixturestore')
        ));
    }

    /**
     * @dataProvider getFilenameDataProvider
     * @magentoAppIsolation enabled
     */
    public function testGetFilename($file, $params)
    {
        $this->_emulateFixtureTheme();
        $this->assertFileExists($this->_model->getFilename($file, $params));
    }

    /**
     * @return array
     */
    public function getFilenameDataProvider()
    {
        return array(
            array('theme_file.txt', array('module' => 'Mage_Catalog')),
            array('Mage_Catalog::theme_file.txt', array()),
            array('Mage_Catalog::theme_file_with_2_dots..txt', array()),
            array('Mage_Catalog::theme_file.txt', array('module' => 'Overriden_Module')),
        );
    }

    /**
     * @param string $file
     * @expectedException Magento_Exception
     * @dataProvider extractScopeExceptionDataProvider
     */
    public function testExtractScopeException($file)
    {
        $this->_model->getFilename($file, array());
    }

    public function extractScopeExceptionDataProvider()
    {
        return array(
            array('::no_scope.ext'),
            array('./file.ext'),
            array('../file.ext'),
            array('dir/./file.ext'),
            array('dir/../file.ext'),
        );
    }


    /**
     * @magentoAppIsolation enabled
     */
    public function testGetViewConfig()
    {
        $this->_emulateFixtureTheme();
        $config = $this->_model->getViewConfig();
        $this->assertInstanceOf('Magento_Config_View', $config);
        $this->assertEquals(array('var1' => 'value1', 'var2' => 'value2'), $config->getVars('Namespace_Module'));
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetConfigCustomized()
    {
        $this->_emulateFixtureTheme();
        $theme = $this->_model->getDesignTheme();
        $customConfigFile = $theme->getCustomViewConfigPath();
        /** @var $filesystem Magento_Filesystem */
        $filesystem = Mage::getObjectManager()->create('Magento_Filesystem');
        $filesystem->setIsAllowCreateDirectories(true);
        try {
            $filesystem->write($customConfigFile, '<?xml version="1.0" encoding="UTF-8"?>
                <view><vars  module="Namespace_Module"><var name="customVar">custom value</var></vars></view>');

            $config = $this->_model->getViewConfig();
            $this->assertInstanceOf('Magento_Config_View', $config);
            $this->assertEquals(array('customVar' => 'custom value'), $config->getVars('Namespace_Module'));
        } catch (Exception $e) {
            $filesystem->delete($customConfigFile);
            throw $e;
        }
        $filesystem->delete($customConfigFile);
    }

    /**
     * @param string $appMode
     * @param string $file
     * @param string $result
     *
     * @dataProvider getViewUrlDataProvider
     *
     * @magentoConfigFixture current_store dev/static/sign 0
     * @magentoAppIsolation enabled
     */
    public function testGetViewUrl($appMode, $file, $result)
    {
        $currentAppMode = Mage::getObjectManager()->get('Mage_Core_Model_App_State')->getMode();
        if ($currentAppMode != $appMode) {
            $this->markTestSkipped("Implemented to be run in {$appMode} mode");
        }
        $this->_emulateFixtureTheme();
        $this->assertEquals($this->_model->getViewFileUrl($file), $result);
    }

    /**
     * @param string $appMode
     * @param string $file
     * @param string $result
     *
     * @dataProvider getViewUrlDataProvider
     *
     * @magentoConfigFixture current_store dev/static/sign 1
     * @magentoAppIsolation enabled
     */
    public function testGetViewUrlSigned($appMode, $file, $result)
    {
        $currentAppMode = Mage::getObjectManager()->get('Mage_Core_Model_App_State')->getMode();
        if ($currentAppMode != $appMode) {
            $this->markTestSkipped("Implemented to be run in {$appMode} mode");
        }
        $url = $this->_model->getViewFileUrl($file);
        $this->assertEquals(strpos($url, $result), 0);
        $lastModified = array();
        preg_match('/.*\?(.*)$/i', $url, $lastModified);
        $this->assertArrayHasKey(1, $lastModified);
        $this->assertEquals(10, strlen($lastModified[1]));
        $this->assertLessThanOrEqual(time(), $lastModified[1]);
        $this->assertGreaterThan(1970, date('Y', $lastModified[1]));
    }

    /**
     * @return array
     */
    public function getViewUrlDataProvider()
    {
        return array(
            array(
                Mage_Core_Model_App_State::MODE_DEFAULT,
                'Mage_Page::favicon.ico',
                'http://localhost/pub/static/frontend/test/default/en_US/Mage_Page/favicon.ico',
            ),
            array(
                Mage_Core_Model_App_State::MODE_DEVELOPER,
                'prototype/prototype.js',
                'http://localhost/pub/lib/prototype/prototype.js'
            ),
            array(
                Mage_Core_Model_App_State::MODE_DEFAULT,
                'prototype/prototype.js',
                'http://localhost/pub/lib/prototype/prototype.min.js'
            ),
            array(
                Mage_Core_Model_App_State::MODE_DEVELOPER,
                'Mage_Page::menu.js',
                'http://localhost/pub/static/frontend/test/default/en_US/Mage_Page/menu.js'
            ),
            array(
                Mage_Core_Model_App_State::MODE_DEFAULT,
                'Mage_Page::menu.js',
                'http://localhost/pub/static/frontend/test/default/en_US/Mage_Page/menu.js'
            ),
            array(
                Mage_Core_Model_App_State::MODE_DEFAULT,
                'Mage_Catalog::widgets.css',
                'http://localhost/pub/static/frontend/test/default/en_US/Mage_Catalog/widgets.css'
            ),
            array(
                Mage_Core_Model_App_State::MODE_DEVELOPER,
                'Mage_Catalog::widgets.css',
                'http://localhost/pub/static/frontend/test/default/en_US/Mage_Catalog/widgets.css'
            ),
        );
    }
}
