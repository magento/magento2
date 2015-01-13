<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Layout\File\Collector;

use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\File\CollectorInterface;
use Magento\Framework\View\File\FileList\Factory;

/**
 * Source of layout files aggregated from a theme and its parents according to merging and overriding conventions
 */
class Aggregated implements CollectorInterface
{
    /**
     * File list factory
     *
     * @var Factory
     */
    protected $fileListFactory;

    /**
     * Base files
     *
     * @var CollectorInterface
     */
    protected $baseFiles;

    /**
     * Theme files
     *
     * @var CollectorInterface
     */
    protected $themeFiles;

    /**
     * Overridden base files
     *
     * @var CollectorInterface
     */
    protected $overrideBaseFiles;

    /**
     * Overridden theme files
     *
     * @var \Magento\Framework\View\File\CollectorInterface
     */
    protected $overrideThemeFiles;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\File\FileList\Factory $fileListFactory
     * @param \Magento\Framework\View\File\CollectorInterface $baseFiles
     * @param CollectorInterface $themeFiles
     * @param \Magento\Framework\View\File\CollectorInterface $overrideBaseFiles
     * @param CollectorInterface $overrideThemeFiles
     */
    public function __construct(
        Factory $fileListFactory,
        CollectorInterface $baseFiles,
        CollectorInterface $themeFiles,
        CollectorInterface $overrideBaseFiles,
        CollectorInterface $overrideThemeFiles
    ) {
        $this->fileListFactory = $fileListFactory;
        $this->baseFiles = $baseFiles;
        $this->themeFiles = $themeFiles;
        $this->overrideBaseFiles = $overrideBaseFiles;
        $this->overrideThemeFiles = $overrideThemeFiles;
    }

    /**
     * Retrieve files
     *
     * Aggregate layout files from modules and a theme and its ancestors
     *
     * @param ThemeInterface $theme
     * @param string $filePath
     * @return \Magento\Framework\View\File[]
     */
    public function getFiles(ThemeInterface $theme, $filePath)
    {
        $list = $this->fileListFactory->create();
        $list->add($this->baseFiles->getFiles($theme, $filePath));

        foreach ($theme->getInheritedThemes() as $currentTheme) {
            $list->add($this->themeFiles->getFiles($currentTheme, $filePath));
            $list->replace($this->overrideBaseFiles->getFiles($currentTheme, $filePath));
            $list->replace($this->overrideThemeFiles->getFiles($currentTheme, $filePath));
        }
        return $list->getAll();
    }
}
