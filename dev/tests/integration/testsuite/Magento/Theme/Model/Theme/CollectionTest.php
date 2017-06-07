<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test for filesystem themes collection
 */
namespace Magento\Theme\Model\Theme;

use Magento\Framework\App\Area;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * @magentoComponentsDir Magento/Theme/Model/_files/design
 */
class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Theme\Model\Theme\Collection
     */
    protected $_model;

    protected function setUp()
    {
        $directoryList = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Framework\App\Filesystem\DirectoryList::class,
            [
                'root' => DirectoryList::ROOT,
            ]
        );
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Framework\Filesystem::class,
            ['directoryList' => $directoryList]
        );
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Theme\Model\Theme\Collection::class,
            ['filesystem' => $filesystem]
        );
    }

    /**
     * Test load themes collection from filesystem
     *
     * @magentoAppIsolation enabled
     */
    public function testLoadThemesFromFileSystem()
    {
        $this->_model->addConstraint(\Magento\Theme\Model\Theme\Collection::CONSTRAINT_AREA, 'frontend');
        $this->assertNotEmpty($this->_model->getItemById('frontend/Magento_FrameworkThemeTest/default'));
        $this->assertEmpty($this->_model->getItemById('adminhtml/FrameworkThemeTest/test'));
    }

    /**
     * Load from configuration
     *
     * @dataProvider expectedThemeDataFromConfiguration
     */
    public function testLoadFromConfiguration($area, $vendor, $themeName, $expectedData)
    {
        $this->_model->addConstraint(\Magento\Theme\Model\Theme\Collection::CONSTRAINT_AREA, $area);
        $this->_model->addConstraint(\Magento\Theme\Model\Theme\Collection::CONSTRAINT_VENDOR, $vendor);
        $this->_model->addConstraint(\Magento\Theme\Model\Theme\Collection::CONSTRAINT_THEME_NAME, $themeName);
        $theme = $this->_model->getFirstItem();
        $this->assertEquals($expectedData, $theme->getData());
    }

    /**
     * Expected theme data from configuration
     *
     * @return array
     */
    public function expectedThemeDataFromConfiguration()
    {
        return [
            [
                'frontend', 'Magento_FrameworkThemeTest', 'default',
                [
                    'area' => 'frontend',
                    'theme_title' => 'Default',
                    'parent_id' => null,
                    'parent_theme_path' => null,
                    'theme_path' => 'Magento_FrameworkThemeTest/default',
                    'code' => 'Magento_FrameworkThemeTest/default',
                    'preview_image' => null,
                    'type' => \Magento\Framework\View\Design\ThemeInterface::TYPE_PHYSICAL,
                ],
            ]
        ];
    }

    /**
     * Test if theme present in file system
     *
     * @magentoAppIsolation enabled
     * @covers \Magento\Theme\Model\Theme\Collection::hasTheme
     */
    public function testHasThemeInCollection()
    {
        /** @var $themeModel \Magento\Framework\View\Design\ThemeInterface */
        $themeModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Framework\View\Design\ThemeInterface::class
        );
        $themeModel->setData(
            [
                'area' => 'space_area',
                'theme_title' => 'Space theme',
                'parent_id' => null,
                'is_featured' => false,
                'theme_path' => 'default_space',
                'preview_image' => 'images/preview.png',
                'type' => \Magento\Framework\View\Design\ThemeInterface::TYPE_PHYSICAL,
            ]
        );

        $this->_model->addConstraint(Collection::CONSTRAINT_AREA, Area::AREA_FRONTEND);
        $this->assertFalse($this->_model->hasTheme($themeModel));
    }
}
