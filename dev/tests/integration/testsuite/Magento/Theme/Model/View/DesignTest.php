<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\View;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Store\Model\ScopeInterface;

/**
 * @magentoComponentsDir Magento/Theme/Model/_files/design
 * @magentoDbIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
        /** @var \Magento\Framework\Filesystem $filesystem */
        $filesystem = $objectManager->get(\Magento\Framework\Filesystem::class);
        $themeDir = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $themeDir->delete('theme/frontend');
        $themeDir->delete('theme/_merged');

        $libDir = $filesystem->getDirectoryWrite(DirectoryList::LIB_WEB);
        $libDir->copyFile('prototype/prototype.js', 'prototype/prototype.min.js');
    }

    public static function tearDownAfterClass()
    {
        /** @var \Magento\Framework\Filesystem $filesystem */
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(\Magento\Framework\Filesystem::class);
        $libDir = $filesystem->getDirectoryWrite(DirectoryList::LIB_WEB);
        $libDir->delete('prototype/prototype.min.js');
    }

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Theme\Model\Theme\Registration $registration */
        $registration = $objectManager->get(
            \Magento\Theme\Model\Theme\Registration::class
        );
        $registration->register();
        $this->_model = $objectManager->create(\Magento\Framework\View\DesignInterface::class);
        $this->_viewFileSystem = $objectManager->create(\Magento\Framework\View\FileSystem::class);
        $this->_viewConfig = $objectManager->create(\Magento\Framework\View\ConfigInterface::class);
        $objectManager->get(\Magento\Framework\App\State::class)->setAreaCode('frontend');
    }

    /**
     * Emulate fixture design theme
     *
     * @param string $themePath
     */
    protected function _emulateFixtureTheme($themePath = 'Test_FrameworkThemeTest/default')
    {
        \Magento\TestFramework\Helper\Bootstrap::getInstance()->loadArea('frontend');
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get(\Magento\Framework\View\DesignInterface::class)->setDesignTheme($themePath);

        $this->_viewFileSystem = $objectManager->create(\Magento\Framework\View\FileSystem::class);
        $this->_viewConfig = $objectManager->create(\Magento\Framework\View\ConfigInterface::class);
    }

    public function testSetGetArea()
    {
        $this->assertEquals(\Magento\Framework\View\DesignInterface::DEFAULT_AREA, $this->_model->getArea());
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Framework\App\State::class)
            ->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        $this->assertEquals(\Magento\Framework\App\Area::AREA_ADMINHTML, $this->_model->getArea());
    }

    public function testSetDesignTheme()
    {
        $this->_model->setDesignTheme('Magento/blank', 'frontend');
        $this->assertEquals('Magento/blank', $this->_model->getDesignTheme()->getThemePath());
    }

    public function testGetDesignTheme()
    {
        $this->assertInstanceOf(\Magento\Framework\View\Design\ThemeInterface::class, $this->_model->getDesignTheme());
    }

    /**
     * @magentoConfigFixture current_store design/theme/theme_id 0
     */
    public function testGetConfigurationDesignThemeDefaults()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $themes = ['frontend' => 'test_f', 'adminhtml' => 'test_a'];
        $design = $objectManager->create(\Magento\Theme\Model\View\Design::class, ['themes' => $themes]);
        $objectManager->addSharedInstance($design, \Magento\Theme\Model\View\Design::class);

        $model = $objectManager->get(\Magento\Theme\Model\View\Design::class);

        $this->assertEquals('test_f', $model->getConfigurationDesignTheme());
        $this->assertEquals('test_f', $model->getConfigurationDesignTheme('frontend'));
        $this->assertEquals('test_f', $model->getConfigurationDesignTheme('frontend', ['store' => 0]));
        $this->assertEquals('test_f', $model->getConfigurationDesignTheme('frontend', ['store' => null]));
        $this->assertEquals('test_a', $model->getConfigurationDesignTheme('adminhtml'));
        $this->assertEquals('test_a', $model->getConfigurationDesignTheme('adminhtml', ['store' => uniqid()]));
    }

    /**
     * @magentoConfigFixture current_store design/theme/theme_id one
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     */
    public function testGetConfigurationDesignThemeStore()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var \Magento\Framework\App\Config\MutableScopeConfigInterface $mutableConfig */
        $mutableConfig = $objectManager->get(\Magento\Framework\App\Config\MutableScopeConfigInterface::class);
        $mutableConfig->setValue('design/theme/theme_id', 'two', ScopeInterface::SCOPE_STORE, 'fixturestore');

        $storeId = $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class)
            ->getStore()
            ->getId();
        $this->assertEquals('one', $this->_model->getConfigurationDesignTheme());
        $this->assertEquals('one', $this->_model->getConfigurationDesignTheme(null, ['store' => $storeId]));
        $this->assertEquals('one', $this->_model->getConfigurationDesignTheme('frontend', ['store' => $storeId]));
        $this->assertEquals('two', $this->_model->getConfigurationDesignTheme(null, ['store' => 'fixturestore']));
        $this->assertEquals(
            'two',
            $this->_model->getConfigurationDesignTheme('frontend', ['store' => 'fixturestore'])
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
        return [
            ['theme_file.txt', ['module' => 'Magento_Catalog']],
            ['Magento_Catalog::theme_file.txt', []],
            ['Magento_Catalog::theme_file_with_2_dots..txt', []],
            ['Magento_Catalog::theme_file.txt', ['module' => 'Overridden_Module']]
        ];
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetViewConfig()
    {
        $this->_emulateFixtureTheme();
        $config = $this->_viewConfig->getViewConfig();
        $this->assertInstanceOf(\Magento\Framework\Config\View::class, $config);
        $this->assertEquals(['var1' => 'value1', 'var2' => 'value2'], $config->getVars('Namespace_Module'));
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetConfigCustomized()
    {
        $this->_emulateFixtureTheme();
        /** @var $theme \Magento\Framework\View\Design\ThemeInterface */
        $theme = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\DesignInterface::class
        )->getDesignTheme();
        $customConfigFile = $theme->getCustomization()->getCustomViewConfigPath();
        /** @var $filesystem \Magento\Framework\Filesystem */
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Framework\Filesystem::class);
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $relativePath = $directory->getRelativePath($customConfigFile);
        try {
            $directory->writeFile(
                $relativePath,
                '<?xml version="1.0" encoding="UTF-8"?>
                <view><vars  module="Namespace_Module"><var name="customVar">custom value</var></vars></view>'
            );

            $config = $this->_viewConfig->getViewConfig();
            $this->assertInstanceOf(\Magento\Framework\Config\View::class, $config);
            $this->assertEquals(['customVar' => 'custom value'], $config->getVars('Namespace_Module'));
        } catch (\Exception $e) {
            $directory->delete($relativePath);
            throw $e;
        }
        $directory->delete($relativePath);
    }
}
