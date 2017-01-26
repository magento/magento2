<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Theme;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Theme\Model\Theme;
use Magento\Theme\Model\ResourceModel\Theme\Collection as ThemeCollection;
use Magento\Framework\App\CacheInterface;
use Magento\TestFramework\Helper\CacheCleaner;

class ThemeProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ThemeProvider
     */
    private $themeProvider;

    /**
     * @var ThemeCollection
     */
    private $themeCollection;

    /**
     * @var CacheInterface
     */
    private $cache;

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->themeProvider = $objectManager->create(ThemeProvider::class);
        $this->themeCollection = $objectManager->create(ThemeCollection::class);
        $this->cache = $objectManager->create(CacheInterface::class);
        CacheCleaner::clean();
    }

    public function testGetThemeById()
    {
        /** @var Theme $theme */
        foreach ($this->themeCollection as $theme) {
            $theme = $this->themeProvider->getThemeById($theme->getId());
            $this->assertNotEmpty($this->cache->load('theme-by-id-' . $theme->getId()));
            $themeFullPath = $theme->getArea() . '/' . $theme->getThemePath();
            //$this->assertNotEmpty($this->cache->load('theme' . $themeFullPath));
            $themeLoadedFromCache = $this->themeProvider->getThemeById($theme->getId());
            $this->assertEquals(
                $theme,
                $themeLoadedFromCache
            );
        }
    }

    public function testGetThemeByFullPath()
    {
        /** @var Theme $theme */
        foreach ($this->themeCollection as $theme) {
            $themeFullPath = $theme->getArea() . '/' . $theme->getThemePath();
            $this->assertNotEmpty($this->cache->load('theme-by-id-' . $theme->getId()));
            //$this->assertNotEmpty($this->cache->load('theme' . $themeFullPath));
            $this->assertEquals(
                $this->themeProvider->getThemeById($themeFullPath),
                $this->themeProvider->getThemeById($themeFullPath)
            );
        }
    }
}
