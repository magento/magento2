<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Theme;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Theme\Model\Theme;
use Magento\Theme\Model\ResourceModel\Theme\Collection as ThemeCollection;
use Magento\TestFramework\Helper\CacheCleaner;

class ThemeProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ThemeProvider
     */
    private $themeProvider1;

    /**
     * @var ThemeProvider
     */
    private $themeProvider2;

    /**
     * @var ThemeCollection
     */
    private $themeCollection;

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->themeProvider1 = $objectManager->create(ThemeProvider::class);
        $this->themeProvider2 = clone $this->themeProvider1;
        $this->themeCollection = $objectManager->create(ThemeCollection::class);
        CacheCleaner::clean();
    }

    public function testGetThemeById()
    {
        /** @var Theme $theme */
        foreach ($this->themeCollection as $theme) {
            $this->assertEquals(
                $this->themeProvider1->getThemeById($theme->getId())->getData(),
                $this->themeProvider2->getThemeById($theme->getId())->getData()
            );
        }
    }

    public function testGetThemeByFullPath()
    {
        /** @var Theme $theme */
        foreach ($this->themeCollection as $theme) {
            $this->assertEquals(
                $this->themeProvider1->getThemeByFullPath($theme->getFullPath())->getData(),
                $this->themeProvider2->getThemeByFullPath($theme->getFullPath())->getData()
            );
        }
    }
}
