<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Theme;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Theme\Model\Theme;
use Magento\Theme\Model\ResourceModel\Theme\Collection as ThemeCollection;
use Magento\TestFramework\Helper\CacheCleaner;

class ThemeProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ThemeProvider
     */
    private $themeProviderOne;

    /**
     * @var ThemeProvider
     */
    private $themeProviderTwo;

    /**
     * @var ThemeCollection
     */
    private $themeCollection;

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->themeProviderOne = $objectManager->create(ThemeProvider::class);
        $this->themeProviderTwo = clone $this->themeProviderOne;
        $this->themeCollection = $objectManager->create(ThemeCollection::class);
        CacheCleaner::clean();
    }

    public function testGetThemeById()
    {
        /** @var Theme $theme */
        foreach ($this->themeCollection as $theme) {
            $theme = $this->themeProviderOne->getThemeById($theme->getId());
            $this->assertSame(
                $theme,
                $this->themeProviderOne->getThemeById($theme->getId())
            );
            $this->assertSame(
                $theme->getData(),
                $this->themeProviderTwo->getThemeById($theme->getId())->getData()
            );
        }
    }

    public function testGetThemeByFullPath()
    {
        /** @var Theme $theme */
        foreach ($this->themeCollection as $theme) {
            $theme = $this->themeProviderOne->getThemeByFullPath($theme->getFullPath());
            $this->assertSame(
                $theme,
                $this->themeProviderOne->getThemeByFullPath($theme->getFullPath())
            );
            $this->assertSame(
                $theme->getData(),
                $this->themeProviderTwo->getThemeByFullPath($theme->getFullPath())->getData()
            );
        }
    }
}
