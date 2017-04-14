<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Theme;

use Magento\Framework\Component\ComponentRegistrar;

class RegistrationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Theme\Model\Theme\Registration
     */
    protected $_model;

    /**
     * @var \Magento\Theme\Model\Theme
     */
    protected $_theme;

    public static function setUpBeforeClass()
    {
        ComponentRegistrar::register(
            ComponentRegistrar::THEME,
            'frontend/Test/test_theme',
            dirname(__DIR__) . '/_files/design/frontend/Test/test_theme'
        );
    }

    /**
     * Initialize base models
     */
    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get(\Magento\Framework\App\AreaList::class)
            ->getArea(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE)
            ->load(\Magento\Framework\App\Area::PART_CONFIG);

        $objectManager->get(\Magento\Framework\App\State::class)
            ->setAreaCode(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE);
        $this->_theme = $objectManager
            ->create(\Magento\Framework\View\Design\ThemeInterface::class);
        $this->_model = $objectManager
            ->create(\Magento\Theme\Model\Theme\Registration::class);
    }

    /**
     * Register themes
     * Use this method only with database isolation
     *
     * @return \Magento\Theme\Model\Theme\RegistrationTest
     */
    protected function registerThemes()
    {
        $this->_model->register();
        return $this;
    }

    /**
     * Use this method only with database isolation
     *
     * @return \Magento\Theme\Model\Theme
     */
    protected function _getTestTheme()
    {
        $theme = $this->_theme->getCollection()->getThemeByFullPath(
            implode(\Magento\Framework\View\Design\ThemeInterface::PATH_SEPARATOR, ['frontend', 'Test/test_theme'])
        );
        $this->assertNotEmpty($theme->getId());
        return $theme;
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testVirtualByVirtualRelation()
    {
        $this->registerThemes();
        $theme = $this->_getTestTheme();

        $virtualTheme = clone $this->_theme;
        $virtualTheme->setData($theme->getData())->setId(null);
        $virtualTheme->setType(\Magento\Framework\View\Design\ThemeInterface::TYPE_VIRTUAL)->save();

        $subVirtualTheme = clone $this->_theme;
        $subVirtualTheme->setData($theme->getData())->setId(null);
        $subVirtualTheme->setParentId(
            $virtualTheme->getId()
        )->setType(
            \Magento\Framework\View\Design\ThemeInterface::TYPE_VIRTUAL
        )->save();

        $this->registerThemes();
        $parentId = $subVirtualTheme->getParentId();
        $subVirtualTheme->load($subVirtualTheme->getId());
        $this->assertNotEquals($parentId, $subVirtualTheme->getParentId());
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testPhysicalThemeElimination()
    {
        $this->registerThemes();
        $theme = $this->_getTestTheme();

        $testTheme = clone $this->_theme;
        $testTheme->setData($theme->getData())->setThemePath('empty')->setId(null);
        $testTheme->setType(\Magento\Framework\View\Design\ThemeInterface::TYPE_PHYSICAL)->save();

        $this->registerThemes();
        $testTheme->load($testTheme->getId());
        $this->assertNotEquals(
            (int)$testTheme->getType(),
            \Magento\Framework\View\Design\ThemeInterface::TYPE_PHYSICAL
        );
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testRegister()
    {
        $this->registerThemes();
        $themePath = implode(
            \Magento\Framework\View\Design\ThemeInterface::PATH_SEPARATOR,
            ['frontend', 'Test/test_theme']
        );
        $theme = $this->_model->getThemeFromDb($themePath);
        $this->assertEquals($themePath, $theme->getFullPath());
    }
}
