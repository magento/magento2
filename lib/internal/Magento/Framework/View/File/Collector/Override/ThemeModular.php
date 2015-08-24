<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\File\Collector\Override;

use Magento\Framework\View\File\AbstractCollector;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Source of view files that explicitly override modular files of ancestor themes
 */
class ThemeModular extends AbstractCollector
{
    /**
     * Retrieve files
     *
     * @param \Magento\Framework\View\Design\ThemeInterface $theme
     * @param string $filePath
     * @return \Magento\Framework\View\File[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getFiles(ThemeInterface $theme, $filePath)
    {
        $namespace = $module = '*';
        $themePath = $theme->getFullPath();
        $searchPattern = "{$themePath}/{$namespace}_{$module}/{$this->subDir}*/*/{$filePath}";
        $files = $this->directory->search($searchPattern);

        if (empty($files)) {
            return [];
        }

        $themes = [];
        $currentTheme = $theme;
        while ($currentTheme = $currentTheme->getParentTheme()) {
            $themes[$currentTheme->getCode()] = $currentTheme;
        }
        $result = [];
        $pattern = "#/(?<module>[^/]+)/{$this->subDir}(?<themeVendor>[^/]+)/(?<themeName>[^/]+)/"
            . $this->pathPatternHelper->translatePatternFromGlob($filePath) . "$#i";
        foreach ($files as $file) {
            $filename = $this->directory->getAbsolutePath($file);
            if (!preg_match($pattern, $filename, $matches)) {
                continue;
            }
            $moduleFull = $matches['module'];
            $ancestorThemeCode = $matches['themeVendor'] . '/' . $matches['themeName'];
            if (!isset($themes[$ancestorThemeCode])) {
                throw new LocalizedException(
                    new \Magento\Framework\Phrase(
                        "Trying to override modular view file '%1' for theme '%2', which is not ancestor of theme '%3'",
                        [$filename, $ancestorThemeCode, $theme->getCode()]
                    )
                );
            }
            $result[] = $this->fileFactory->create($filename, $moduleFull, $themes[$ancestorThemeCode]);
        }
        return $result;
    }

    /**
     * Get scope directory of this file collector
     *
     * @return string
     */
    protected function getScopeDirectory()
    {
        return DirectoryList::THEMES;
    }
}
