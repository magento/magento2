<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Core\Model;

class ThemeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test crud operations for theme model using valid data
     *
     * @magentoDbIsolation enabled
     */
    public function testCrud()
    {
        /** @var $themeModel \Magento\Framework\View\Design\ThemeInterface */
        $themeModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\View\Design\ThemeInterface'
        );
        $themeModel->setData($this->_getThemeValidData());

        $crud = new \Magento\TestFramework\Entity($themeModel, []);
        $crud->testCrud();
    }

    /**
     * Get theme valid data
     *
     * @return array
     */
    protected function _getThemeValidData()
    {
        return [
            'area' => 'space_area',
            'theme_title' => 'Space theme',
            'parent_id' => null,
            'is_featured' => false,
            'theme_path' => 'default/space',
            'preview_image' => 'images/preview.png',
            'type' => \Magento\Framework\View\Design\ThemeInterface::TYPE_VIRTUAL
        ];
    }

    /**
     * Test theme on child relations
     */
    public function testChildRelation()
    {
        /** @var $theme \Magento\Framework\View\Design\ThemeInterface */
        $theme = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\Design\ThemeInterface'
        );
        $collection = $theme->getCollection()
            ->addTypeFilter(\Magento\Framework\View\Design\ThemeInterface::TYPE_VIRTUAL);
        /** @var $currentTheme \Magento\Framework\View\Design\ThemeInterface */
        foreach ($collection as $currentTheme) {
            $parentTheme = $currentTheme->getParentTheme();
            if (!empty($parentTheme)) {
                $this->assertTrue($parentTheme->hasChildThemes());
            }
        }
    }

    /**
     * @magentoDataFixture Magento/Core/Model/_files/design/themes.php
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     */
    public function testGetInheritedThemes()
    {
        /** @var \Magento\Framework\View\Design\Theme\FlyweightFactory $themeFactory */
        $themeFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\Design\Theme\FlyweightFactory'
        );
        $theme = $themeFactory->create('Vendor/custom_theme');
        $this->assertCount(2, $theme->getInheritedThemes());
        $expected = [];
        foreach ($theme->getInheritedThemes() as $someTheme) {
            $expected[] = $someTheme->getFullPath();
        }
        $this->assertEquals(['frontend/Vendor/default', 'frontend/Vendor/custom_theme'], $expected);
    }
}
