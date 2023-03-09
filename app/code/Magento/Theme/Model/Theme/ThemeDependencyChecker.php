<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Model\Theme;

use Magento\Theme\Model\Theme\Data as ThemeData;
use Magento\Theme\Model\Theme\Data\Collection as ThemeCollection;

/**
 * Class checks theme dependencies
 */
class ThemeDependencyChecker
{
    /**
     * Constructor
     *
     * @param ThemeCollection $themeCollection
     * @param ThemeProvider $themeProvider
     * @param ThemePackageInfo $themePackageInfo,
     */
    public function __construct(
        private readonly ThemeCollection $themeCollection,
        private readonly ThemeProvider $themeProvider,
        private readonly ThemePackageInfo $themePackageInfo
    ) {
    }

    /**
     * Check theme by package name(s) if has child virtual and physical theme
     *
     * @param string[] $packages
     * @return string[]
     */
    public function checkChildThemeByPackagesName($packages)
    {
        $themePaths = [];
        foreach ($packages as $package) {
            $themePath = $this->themePackageInfo->getFullThemePath($package);
            if ($themePath) {
                $themePaths[] = $themePath;
            }
        }
        if ($themePaths) {
            return $this->checkChildTheme($themePaths);
        }

        return [];
    }

    /**
     * Check theme if has child virtual and physical theme
     *
     * @param string[] $themePaths
     * @return string[]
     */
    public function checkChildTheme($themePaths)
    {
        $messages = [];
        $themeHasVirtualChildren = [];
        $themeHasPhysicalChildren = [];
        $parentChildMap = $this->getParentChildThemeMap();
        foreach ($themePaths as $themePath) {
            $theme = $this->themeProvider->getThemeByFullPath($themePath);
            if ($theme->hasChildThemes()) {
                $themeHasVirtualChildren[] = $themePath;
            }
            if (isset($parentChildMap[$themePath])) {
                $themeHasPhysicalChildren[] = $themePath;
            }
        }
        if (!empty($themeHasVirtualChildren)) {
            $text = count($themeHasVirtualChildren) > 1 ? ' are parents of' : ' is a parent of';
            $messages[] = implode(', ', $themeHasVirtualChildren) . $text . ' virtual theme.'
                . ' Parent themes cannot be uninstalled.';
        }
        if (!empty($themeHasPhysicalChildren)) {
            $text = count($themeHasPhysicalChildren) > 1 ? ' are parents of' : ' is a parent of';
            $messages[] = implode(', ', $themeHasPhysicalChildren) . $text . ' physical theme.'
                . ' Parent themes cannot be uninstalled.';
        }
        return $messages;
    }

    /**
     * Obtain a parent theme -> children themes map from the filesystem
     *
     * @return array
     */
    private function getParentChildThemeMap()
    {
        $map = [];
        $this->themeCollection->resetConstraints();
        $this->themeCollection->clear();
        /** @var ThemeData $theme */
        foreach ($this->themeCollection as $theme) {
            if ($theme->getParentTheme()) {
                $map[$theme->getParentTheme()->getFullPath()][] = $theme->getFullPath();
            }
        }
        return $map;
    }
}
