<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\File\Collector\Override;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\File\CollectorInterface;
use Magento\Framework\View\Helper\PathPattern;
use Magento\Framework\View\File\Factory as FileFactory;

/**
 * Source of view files that explicitly override modular files of ancestor themes
 * @since 2.0.0
 */
class ThemeModular implements CollectorInterface
{
    /**
     * Path pattern helper
     *
     * @var \Magento\Framework\View\Helper\PathPattern
     * @since 2.0.0
     */
    private $pathPatternHelper;

    /**
     * View file factopry
     *
     * @var FileFactory
     * @since 2.0.0
     */
    private $fileFactory;

    /**
     * Read directory factory
     *
     * @var \Magento\Framework\Filesystem\Directory\ReadFactory
     * @since 2.0.0
     */
    private $readDirFactory;

    /**
     * Component registrar
     *
     * @var ComponentRegistrarInterface
     * @since 2.0.0
     */
    private $componentRegistrar;

    /**
     * Sub-directory path
     *
     * @var string
     * @since 2.0.0
     */
    private $subDir;

    /**
     * Constructor
     *
     * @param FileFactory $fileFactory
     * @param ReadFactory $readDirFactory
     * @param ComponentRegistrarInterface $componentRegistrar
     * @param PathPattern $pathPatternHelper
     * @param string $subDir
     * @since 2.0.0
     */
    public function __construct(
        FileFactory $fileFactory,
        ReadFactory $readDirFactory,
        ComponentRegistrarInterface $componentRegistrar,
        PathPattern $pathPatternHelper,
        $subDir = ''
    ) {
        $this->pathPatternHelper = $pathPatternHelper;
        $this->fileFactory = $fileFactory;
        $this->readDirFactory = $readDirFactory;
        $this->componentRegistrar = $componentRegistrar;
        $this->subDir = $subDir ? $subDir . '/' : '';
    }

    /**
     * Retrieve files
     *
     * @param ThemeInterface $theme
     * @param string $filePath
     * @return \Magento\Framework\View\File[]
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function getFiles(ThemeInterface $theme, $filePath)
    {
        $namespace = $module = '*';
        $themePath = $theme->getFullPath();
        if (empty($themePath)) {
            return [];
        }
        $themeAbsolutePath = $this->componentRegistrar->getPath(ComponentRegistrar::THEME, $themePath);
        if (!$themeAbsolutePath) {
            return [];
        }
        $themeDir = $this->readDirFactory->create($themeAbsolutePath);
        $files = $themeDir->search("{$namespace}_{$module}/{$this->subDir}*/*/{$filePath}");
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
            $filename = $themeDir->getAbsolutePath($file);
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
}
