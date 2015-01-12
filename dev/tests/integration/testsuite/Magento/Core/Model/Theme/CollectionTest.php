<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test for filesystem themes collection
 */
namespace Magento\Core\Model\Theme;

use Magento\Framework\App\Filesystem\DirectoryList;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\Theme\Collection
     */
    protected $_model;

    protected function setUp()
    {
        $directoryList = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\App\Filesystem\DirectoryList',
            [
                'root' => DirectoryList::ROOT,
                'config' => [
                    DirectoryList::THEMES => [
                        DirectoryList::PATH => dirname(__DIR__) . '/_files/design',
                    ],
                ]
            ]
        );
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\Filesystem',
            ['directoryList' => $directoryList]
        );
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Core\Model\Theme\Collection',
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
        $pathPattern = implode('/', ['frontend', '*', '*', 'theme.xml']);
        $this->_model->addTargetPattern($pathPattern);
        $this->assertEquals(8, count($this->_model));
    }

    /**
     * Load from configuration
     *
     * @dataProvider expectedThemeDataFromConfiguration
     */
    public function testLoadFromConfiguration($themePath, $expectedData)
    {
        $theme = $this->_model->addTargetPattern($themePath)->getFirstItem();
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
                'themePath' => implode('/', ['frontend', 'Magento', 'default', 'theme.xml']),
                'expectedData' => [
                    'area' => 'frontend',
                    'theme_title' => 'Default',
                    'parent_id' => null,
                    'parent_theme_path' => null,
                    'theme_path' => 'Magento/default',
                    'code' => 'Magento/default',
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
     * @covers \Magento\Core\Model\Theme\Collection::hasTheme
     */
    public function testHasThemeInCollection()
    {
        /** @var $themeModel \Magento\Framework\View\Design\ThemeInterface */
        $themeModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\View\Design\ThemeInterface'
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

        $this->_model->addDefaultPattern();
        $this->assertFalse($this->_model->hasTheme($themeModel));
    }
}
