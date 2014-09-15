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
namespace Magento\Core\Model\View;

/**
 * @magentoDataFixture Magento/Core/Model/_files/design/themes.php
 */
class DesignTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\DesignInterface
     */
    protected $_model;

    /**
     * @var \Magento\Framework\View\FileSystem
     */
    protected $_viewFileSystem;

    /**
     * @var \Magento\Framework\View\ConfigInterface
     */
    protected $_viewConfig;

    public static function setUpBeforeClass()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Framework\App\Filesystem $filesystem */
        $filesystem = $objectManager->get('Magento\Framework\App\Filesystem');
        $themeDir = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem::MEDIA_DIR);
        $themeDir->delete('theme/frontend');
        $themeDir->delete('theme/_merged');

        $libPath = $filesystem->getPath(\Magento\Framework\App\Filesystem::LIB_WEB);
        copy($libPath . '/prototype/prototype.js', $libPath . '/prototype/prototype.min.js');
    }

    public static function tearDownAfterClass()
    {
        /** @var \Magento\Framework\App\Filesystem $filesystem */
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Framework\App\Filesystem');
        $libPath = $filesystem->getPath(\Magento\Framework\App\Filesystem::LIB_WEB);
        unlink($libPath . '/prototype/prototype.min.js');
    }

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_model = $objectManager->create('Magento\\Framework\View\DesignInterface');
        $this->_viewFileSystem = $objectManager->create('Magento\Framework\View\FileSystem');
        $this->_viewConfig = $objectManager->create('Magento\Framework\View\ConfigInterface');
        $objectManager->get('Magento\Framework\App\State')->setAreaCode('frontend');
    }

    /**
     * Emulate fixture design theme
     *
     * @param string $themePath
     */
    protected function _emulateFixtureTheme($themePath = 'test_default')
    {
        \Magento\TestFramework\Helper\Bootstrap::getInstance()->reinitialize(
            array(
                \Magento\Framework\App\Filesystem::PARAM_APP_DIRS => array(
                    \Magento\Framework\App\Filesystem::THEMES_DIR => array(
                        'path' => realpath(__DIR__ . '/../_files/design')
                    )
                )
            )
        );
        \Magento\TestFramework\Helper\Bootstrap::getInstance()->loadArea('frontend');
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get('Magento\Framework\View\DesignInterface')->setDesignTheme($themePath);

        $this->_viewFileSystem = $objectManager->create('Magento\Framework\View\FileSystem');
        $this->_viewConfig = $objectManager->create('Magento\Framework\View\ConfigInterface');
    }

    public function testSetGetArea()
    {
        $this->assertEquals(\Magento\Framework\View\DesignInterface::DEFAULT_AREA, $this->_model->getArea());
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\App\State')
            ->setAreaCode('test');
        $this->assertEquals('test', $this->_model->getArea());
    }

    public function testSetDesignTheme()
    {
        $this->_model->setDesignTheme('Magento/blank', 'frontend');
        $this->assertEquals('Magento/blank', $this->_model->getDesignTheme()->getThemePath());
    }

    public function testGetDesignTheme()
    {
        $this->assertInstanceOf('Magento\Framework\View\Design\ThemeInterface', $this->_model->getDesignTheme());
    }

    /**
     * @magentoConfigFixture current_store design/theme/theme_id 0
     */
    public function testGetConfigurationDesignThemeDefaults()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $themes = array('frontend' => 'test_f', 'adminhtml' => 'test_a', 'install' => 'test_i');
        $design = $objectManager->create('Magento\Core\Model\View\Design', array('themes' => $themes));
        $objectManager->addSharedInstance($design, 'Magento\Core\Model\View\Design');

        $model = $objectManager->get('Magento\Core\Model\View\Design');

        $this->assertEquals('test_f', $model->getConfigurationDesignTheme());
        $this->assertEquals('test_f', $model->getConfigurationDesignTheme('frontend'));
        $this->assertEquals('test_f', $model->getConfigurationDesignTheme('frontend', array('store' => 0)));
        $this->assertEquals('test_f', $model->getConfigurationDesignTheme('frontend', array('store' => null)));
        $this->assertEquals('test_i', $model->getConfigurationDesignTheme('install'));
        $this->assertEquals('test_i', $model->getConfigurationDesignTheme('install', array('store' => uniqid())));
        $this->assertEquals('test_a', $model->getConfigurationDesignTheme('adminhtml'));
        $this->assertEquals('test_a', $model->getConfigurationDesignTheme('adminhtml', array('store' => uniqid())));
    }

    /**
     * @magentoConfigFixture current_store design/theme/theme_id one
     * @magentoConfigFixture fixturestore_store design/theme/theme_id two
     * @magentoDataFixture Magento/Core/_files/store.php
     */
    public function testGetConfigurationDesignThemeStore()
    {
        $storeId = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\StoreManagerInterface'
        )->getStore()->getId();
        $this->assertEquals('one', $this->_model->getConfigurationDesignTheme());
        $this->assertEquals('one', $this->_model->getConfigurationDesignTheme(null, array('store' => $storeId)));
        $this->assertEquals('one', $this->_model->getConfigurationDesignTheme('frontend', array('store' => $storeId)));
        $this->assertEquals('two', $this->_model->getConfigurationDesignTheme(null, array('store' => 'fixturestore')));
        $this->assertEquals(
            'two',
            $this->_model->getConfigurationDesignTheme('frontend', array('store' => 'fixturestore'))
        );
    }

    /**
     * @dataProvider getFilenameDataProvider
     * @magentoAppIsolation enabled
     */
    public function testGetFilename($file, $params)
    {
        $this->_emulateFixtureTheme();
        $this->assertFileExists($this->_viewFileSystem->getFilename($file, $params));
    }

    /**
     * @return array
     */
    public function getFilenameDataProvider()
    {
        return array(
            array('theme_file.txt', array('module' => 'Magento_Catalog')),
            array('Magento_Catalog::theme_file.txt', array()),
            array('Magento_Catalog::theme_file_with_2_dots..txt', array()),
            array('Magento_Catalog::theme_file.txt', array('module' => 'Overridden_Module'))
        );
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetViewConfig()
    {
        $this->_emulateFixtureTheme();
        $config = $this->_viewConfig->getViewConfig();
        $this->assertInstanceOf('Magento\Framework\Config\View', $config);
        $this->assertEquals(array('var1' => 'value1', 'var2' => 'value2'), $config->getVars('Namespace_Module'));
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetConfigCustomized()
    {
        $this->_emulateFixtureTheme();
        /** @var $theme \Magento\Framework\View\Design\ThemeInterface */
        $theme = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\DesignInterface'
        )->getDesignTheme();
        $customConfigFile = $theme->getCustomization()->getCustomViewConfigPath();
        /** @var $filesystem \Magento\Framework\App\Filesystem */
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Framework\App\Filesystem');
        $directory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem::ROOT_DIR);
        $relativePath = $directory->getRelativePath($customConfigFile);
        try {
            $directory->writeFile(
                $relativePath,
                '<?xml version="1.0" encoding="UTF-8"?>
                <view><vars  module="Namespace_Module"><var name="customVar">custom value</var></vars></view>'
            );

            $config = $this->_viewConfig->getViewConfig();
            $this->assertInstanceOf('Magento\Framework\Config\View', $config);
            $this->assertEquals(array('customVar' => 'custom value'), $config->getVars('Namespace_Module'));
        } catch (\Exception $e) {
            $directory->delete($relativePath);
            throw $e;
        }
        $directory->delete($relativePath);
    }
}
