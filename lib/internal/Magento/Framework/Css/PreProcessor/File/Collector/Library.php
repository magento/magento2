<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Css\PreProcessor\File\Collector;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\File\CollectorInterface;

/**
 * Source of base layout files introduced by modules
 */
class Library implements CollectorInterface
{
    /**
     * @var \Magento\Framework\View\File\Factory
     */
    protected $fileFactory;

    /**
     * @var ReadInterface
     */
    protected $libraryDirectory;

    /**
     * @var \Magento\Framework\View\File\FileList\Factory
     */
    protected $fileListFactory;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadFactory
     */
    private $readFactory;

    /**
     * Component registry
     *
     * @var ComponentRegistrarInterface
     */
    private $componentRegistrar;

    /**
     * @param \Magento\Framework\View\File\FileList\Factory $fileListFactory
     * @param Filesystem $filesystem
     * @param \Magento\Framework\View\File\Factory $fileFactory
     * @param \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory
     * @param ComponentRegistrarInterface $componentRegistrar
     */
    public function __construct(
        \Magento\Framework\View\File\FileList\Factory $fileListFactory,
        Filesystem $filesystem,
        \Magento\Framework\View\File\Factory $fileFactory,
        \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory,
        ComponentRegistrarInterface $componentRegistrar
    ) {
        $this->fileListFactory = $fileListFactory;
        $this->libraryDirectory = $filesystem->getDirectoryRead(
            DirectoryList::LIB_WEB
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
            $path = $this->componentRegistrar->getPath(
                ComponentRegistrar::THEME,
                $themeFullPath
            );
            if (empty($path)) {
                continue;
            }
            $directoryRead = $this->readFactory->create($path);
            $foundFiles = $directoryRead->search("web/{$filePath}");
            $list->replace($this->createFiles($directoryRead, $theme, $foundFiles));
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
