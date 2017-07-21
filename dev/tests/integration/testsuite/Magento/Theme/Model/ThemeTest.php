<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model;

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
            \Magento\Framework\View\Design\ThemeInterface::class
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
            \Magento\Framework\View\Design\ThemeInterface::class
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
     * @magentoComponentsDir Magento/Theme/Model/_files/design
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoAppArea frontend
     */
    public function testGetInheritedThemes()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Theme\Model\Theme\Registration $registration */
        $registration = $objectManager->get(
            \Magento\Theme\Model\Theme\Registration::class
        );
        $registration->register();
        /** @var \Magento\Framework\View\Design\Theme\FlyweightFactory $themeFactory */
        $themeFactory = $objectManager->get(
            \Magento\Framework\View\Design\Theme\FlyweightFactory::class
        );
        $theme = $themeFactory->create('Vendor_FrameworkThemeTest/custom_theme');
        $this->assertCount(2, $theme->getInheritedThemes());
        $expected = [];
        foreach ($theme->getInheritedThemes() as $someTheme) {
            $expected[] = $someTheme->getFullPath();
        }
        $this->assertEquals(
            ['frontend/Vendor_FrameworkThemeTest/default', 'frontend/Vendor_FrameworkThemeTest/custom_theme'],
            $expected
        );
    }
}
