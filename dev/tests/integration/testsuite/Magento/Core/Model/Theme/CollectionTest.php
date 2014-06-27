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

/**
 * Test for filesystem themes collection
 */
namespace Magento\Core\Model\Theme;

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
            array(
                'root' => \Magento\Framework\App\Filesystem::ROOT_DIR,
                'directories' => array(
                    \Magento\Framework\App\Filesystem::THEMES_DIR => array(
                        'path' => dirname(__DIR__) . '/_files/design'
                    )
                )
            )
        );
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\App\Filesystem',
            array('directoryList' => $directoryList)
        );
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Core\Model\Theme\Collection',
            array('filesystem' => $filesystem)
        );
    }

    /**
     * Test load themes collection from filesystem
     *
     * @magentoAppIsolation enabled
     */
    public function testLoadThemesFromFileSystem()
    {
        $pathPattern = implode('/', array('frontend', '*', 'theme.xml'));
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
        return array(
            array(
                'themePath' => implode('/', array('frontend', 'magento_default', 'theme.xml')),
                'expectedData' => array(
                    'area' => 'frontend',
                    'theme_title' => 'Default',
                    'theme_version' => '0.1.0',
                    'parent_id' => null,
                    'parent_theme_path' => null,
                    'theme_path' => 'magento_default',
                    'code' => 'magento_default',
                    'preview_image' => null,
                    'type' => \Magento\Framework\View\Design\ThemeInterface::TYPE_PHYSICAL
                )
            )
        );
    }

    /**
     * Test is theme present in file system
     *
     * @magentoAppIsolation enabled
     * @covers \Magento\Core\Model\Theme\Collection::hasTheme
     * @magentoAppArea install
     */
    public function testHasThemeInCollection()
    {
        /** @var $themeModel \Magento\Framework\View\Design\ThemeInterface */
        $themeModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\View\Design\ThemeInterface'
        );
        $themeModel->setData(
            array(
                'area' => 'space_area',
                'theme_title' => 'Space theme',
                'theme_version' => '0.1.0',
                'parent_id' => null,
                'is_featured' => false,
                'theme_path' => 'default_space',
                'preview_image' => 'images/preview.png',
                'type' => \Magento\Framework\View\Design\ThemeInterface::TYPE_PHYSICAL
            )
        );

        $this->_model->addDefaultPattern('*');
        $this->assertFalse($this->_model->hasTheme($themeModel));
    }
}
