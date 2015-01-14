<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\File\Collector\Override;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\View\File\CollectorInterface;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\View\File\Factory;
use Magento\Framework\Exception;

/**
 * Source of view files that explicitly override modular files of ancestor themes
 */
class ThemeModular implements CollectorInterface
{
    /**
     * Themes directory
     *
     * @var ReadInterface
     */
    protected $themesDirectory;

    /**
     * File factory
     *
     * @var Factory
     */
    protected $fileFactory;

    /**
     * @var string
     */
    protected $subDir;

    /**
     * Constructor
     *
     * @param Filesystem $filesystem
     * @param \Magento\Framework\View\File\Factory $fileFactory
     * @param string $subDir
     */
    public function __construct(
        Filesystem $filesystem,
        Factory $fileFactory,
        $subDir = ''
    ) {
        $this->themesDirectory = $filesystem->getDirectoryRead(DirectoryList::THEMES);
        $this->fileFactory = $fileFactory;
        $this->subDir = $subDir ? $subDir . '/' : '';
    }

    /**
     * Retrieve files
     *
     * @param ThemeInterface $theme
     * @param string $filePath
     * @return array|\Magento\Framework\View\File[]
     * @throws \Magento\Framework\Exception
     */
    public function getFiles(ThemeInterface $theme, $filePath)
    {
        $namespace = $module = '*';
        $themePath = $theme->getFullPath();
        $searchPattern = "{$themePath}/{$namespace}_{$module}/{$this->subDir}*/*/{$filePath}";
        $files = $this->themesDirectory->search($searchPattern);

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
            . strtr(preg_quote($filePath), ['\*' => '[^/]+']) . "$#i";
        foreach ($files as $file) {
            $filename = $this->themesDirectory->getAbsolutePath($file);
            if (!preg_match($pattern, $filename, $matches)) {
                continue;
            }
            $moduleFull = $matches['module'];
            $ancestorThemeCode = $matches['themeVendor'] . '/' . $matches['themeName'];
            if (!isset($themes[$ancestorThemeCode])) {
                throw new Exception(
                    sprintf(
                        "Trying to override modular view file '%s' for theme '%s', which is not ancestor of theme '%s'",
                        $filename,
                        $ancestorThemeCode,
                        $theme->getCode()
                    )
                );
            }
            $result[] = $this->fileFactory->create($filename, $moduleFull, $themes[$ancestorThemeCode]);
        }
        return $result;
    }
}
