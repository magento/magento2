<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\RequireJs\Config\File\Collector;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\File\CollectorInterface;

/**
 * Source of RequireJs config files basing on list of directories they may be located in
 */
class Aggregated implements CollectorInterface
{
    /**
     * Base files
     *
     * @var \Magento\Framework\View\File\CollectorInterface
     */
    protected $baseFiles;

    /**
     * Theme files
     *
     * @var \Magento\Framework\View\File\CollectorInterface
     */
    protected $themeFiles;

    /**
     * Theme modular files
     *
     * @var \Magento\Framework\View\File\CollectorInterface
     */
    protected $themeModularFiles;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    protected $libDirectory;

    /**
     * @var \Magento\Framework\View\File\Factory
     */
    protected $fileFactory;

    /**
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\View\File\Factory $fileFactory
     * @param CollectorInterface $baseFiles
     * @param CollectorInterface $themeFiles
     * @param CollectorInterface $themeModularFiles
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\View\File\Factory $fileFactory,
        CollectorInterface $baseFiles,
        CollectorInterface $themeFiles,
        CollectorInterface $themeModularFiles
    ) {
        $this->libDirectory = $filesystem->getDirectoryRead(DirectoryList::LIB_WEB);
        $this->fileFactory = $fileFactory;
        $this->baseFiles = $baseFiles;
        $this->themeFiles = $themeFiles;
        $this->themeModularFiles = $themeModularFiles;
    }

    /**
     * Get layout files from modules, theme with ancestors and library
     *
     * @param ThemeInterface $theme
     * @param string $filePath
     * @throws \InvalidArgumentException
     * @return \Magento\Framework\View\File[]
     */
    public function getFiles(ThemeInterface $theme, $filePath)
    {
        if (empty($filePath)) {
            throw new \InvalidArgumentException('File path must be specified');
        }
        $files = [];
        if ($this->libDirectory->isExist($filePath)) {
            $filename = $this->libDirectory->getAbsolutePath($filePath);
            $files[] = $this->fileFactory->create($filename);
        }

        $files = array_merge($files, $this->baseFiles->getFiles($theme, $filePath));

        foreach ($theme->getInheritedThemes() as $currentTheme) {
            $files = array_merge($files, $this->themeModularFiles->getFiles($currentTheme, $filePath));
            $files = array_merge($files, $this->themeFiles->getFiles($currentTheme, $filePath));
        }
        return $files;
    }
}
