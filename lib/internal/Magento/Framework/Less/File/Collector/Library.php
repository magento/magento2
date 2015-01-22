<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Less\File\Collector;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\File\CollectorInterface;
use Magento\Framework\View\File\Factory;
use Magento\Framework\View\File\FileList\Factory as FileListFactory;

/**
 * Source of base layout files introduced by modules
 */
class Library implements CollectorInterface
{
    /**
     * @var Factory
     */
    protected $fileFactory;

    /**
     * @var ReadInterface
     */
    protected $libraryDirectory;

    /**
     * @var ReadInterface
     */
    protected $themesDirectory;

    /**
     * @var FileListFactory
     */
    protected $fileListFactory;

    /**
     * @param FileListFactory $fileListFactory
     * @param Filesystem $filesystem
     * @param Factory $fileFactory
     */
    public function __construct(
        FileListFactory $fileListFactory,
        Filesystem $filesystem,
        Factory $fileFactory
    ) {
        $this->fileListFactory = $fileListFactory;
        $this->libraryDirectory = $filesystem->getDirectoryRead(DirectoryList::LIB_WEB);
        $this->themesDirectory = $filesystem->getDirectoryRead(DirectoryList::THEMES);
        $this->fileFactory = $fileFactory;
    }

    /**
     * Retrieve files
     *
     * @param ThemeInterface $theme
     * @param string $filePath
     * @return array|\Magento\Framework\View\File[]
     */
    public function getFiles(ThemeInterface $theme, $filePath)
    {
        $list = $this->fileListFactory->create();
        $files = $this->libraryDirectory->search($filePath);
        $list->add($this->createFiles($this->libraryDirectory, $theme, $files));

        foreach ($theme->getInheritedThemes() as $currentTheme) {
            $themeFullPath = $currentTheme->getFullPath();
            $files = $this->themesDirectory->search("{$themeFullPath}/web/{$filePath}");
            $list->replace($this->createFiles($this->themesDirectory, $theme, $files), false);
        }
        return $list->getAll();
    }

    /**
     * @param ReadInterface $reader
     * @param ThemeInterface $theme
     * @param array $files
     * @return array
     */
    protected function createFiles(ReadInterface $reader, ThemeInterface $theme, $files)
    {
        $result = [];
        foreach ($files as $file) {
            $filename = $reader->getAbsolutePath($file);
            $result[] = $this->fileFactory->create($filename, false, $theme);
        }
        return $result;
    }
}
