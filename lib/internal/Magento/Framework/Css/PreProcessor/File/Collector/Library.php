<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Css\PreProcessor\File\Collector;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadFactory;
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
     * @var FileListFactory
     */
    protected $fileListFactory;

    /**
     * @var ReadFactory
     */
    private $readFactory;

    /**
     * Component registry
     *
     * @var ComponentRegistrarInterface
     */
    private $componentRegistrar;

    /**
     * @param FileListFactory $fileListFactory
     * @param Filesystem $filesystem
     * @param Factory $fileFactory
     * @param ReadFactory $readFactory
     * @param ComponentRegistrarInterface $componentRegistrar
     */
    public function __construct(
        FileListFactory $fileListFactory,
        Filesystem $filesystem,
        Factory $fileFactory,
        ReadFactory $readFactory,
        ComponentRegistrarInterface $componentRegistrar
    ) {
        $this->fileListFactory = $fileListFactory;
        $this->libraryDirectory = $filesystem->getDirectoryRead(
            \Magento\Framework\App\Filesystem\DirectoryList::LIB_WEB
        );
        $this->fileFactory = $fileFactory;
        $this->readFactory = $readFactory;
        $this->componentRegistrar = $componentRegistrar;
    }

    /**
     * Retrieve files
     *
     * @param ThemeInterface $theme
     * @param string $filePath
     * @return \Magento\Framework\View\File[]
     */
    public function getFiles(ThemeInterface $theme, $filePath)
    {
        $list = $this->fileListFactory->create('Magento\Framework\Css\PreProcessor\File\FileList\Collator');
        $files = $this->libraryDirectory->search($filePath);
        $list->add($this->createFiles($this->libraryDirectory, $theme, $files));

        foreach ($theme->getInheritedThemes() as $currentTheme) {
            $themeFullPath = $currentTheme->getFullPath();
            $directoryRead = $this->readFactory->create(
                $this->componentRegistrar->getPath(ComponentRegistrar::THEME, $themeFullPath)
            );
            $foundFiles = $directoryRead->search("web/{$filePath}");
            $files = [];
            foreach ($foundFiles as $foundFile) {
                $foundFile = $directoryRead->getAbsolutePath($foundFile);
                $files[] = $foundFile;
            }
            $list->replace($this->createFiles($directoryRead, $theme, $files));
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
