<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Service of copying customizations from one theme to another
 */
namespace Magento\Theme\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\View\Design\Theme\Customization\Path;
use Magento\Framework\View\Design\Theme\FileFactory;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Theme\Model\Theme\File;
use Magento\Widget\Model\Layout\Link;
use Magento\Widget\Model\Layout\Update as ModelLayoutUpdate;
use Magento\Widget\Model\Layout\UpdateFactory;
use Magento\Widget\Model\ResourceModel\Layout\Link\Collection as ResourceLayoutLinkCollection;
use Magento\Widget\Model\ResourceModel\Layout\Update\Collection as ResourceLayoutUpdateCollection;

class CopyService
{
    /**
     * @var \Magento\Framework\Filesystem\Directory\Write
     */
    protected $_directory;

    /**
     * @var FileFactory
     */
    protected $_fileFactory;

    /**
     * @var Link
     */
    protected $_link;

    /**
     * @var UpdateFactory
     */
    protected $_updateFactory;

    /**
     * @var ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var Path
     */
    protected $_customizationPath;

    /**
     * @param Filesystem $filesystem
     * @param FileFactory $fileFactory
     * @param Link $link
     * @param UpdateFactory $updateFactory
     * @param ManagerInterface $eventManager
     * @param Path $customization
     */
    public function __construct(
        Filesystem $filesystem,
        FileFactory $fileFactory,
        Link $link,
        UpdateFactory $updateFactory,
        ManagerInterface $eventManager,
        Path $customization
    ) {
        $this->_directory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->_fileFactory = $fileFactory;
        $this->_link = $link;
        $this->_updateFactory = $updateFactory;
        $this->_eventManager = $eventManager;
        $this->_customizationPath = $customization;
    }

    /**
     * Copy customizations from one theme to another
     *
     * @param ThemeInterface $source
     * @param ThemeInterface $target
     * @return void
     */
    public function copy(ThemeInterface $source, ThemeInterface $target)
    {
        $this->_copyDatabaseCustomization($source, $target);
        $this->_copyLayoutCustomization($source, $target);
        $this->_copyFilesystemCustomization($source, $target);
    }

    /**
     * Copy customizations stored in a database from one theme to another, overriding existing data
     *
     * @param ThemeInterface $source
     * @param ThemeInterface $target
     * @return void
     */
    protected function _copyDatabaseCustomization(ThemeInterface $source, ThemeInterface $target)
    {
        /** @var File $themeFile */
        foreach ($target->getCustomization()->getFiles() as $themeFile) {
            $themeFile->delete();
        }
        /** @var File $newFile */
        foreach ($source->getCustomization()->getFiles() as $themeFile) {
            /** @var File $newThemeFile */
            $newThemeFile = $this->_fileFactory->create();
            $newThemeFile->setData(
                [
                    'theme_id' => $target->getId(),
                    'file_path' => $themeFile->getFilePath(),
                    'file_type' => $themeFile->getFileType(),
                    'content' => $themeFile->getContent(),
                    'sort_order' => $themeFile->getData('sort_order'),
                ]
            );
            $newThemeFile->save();
        }
    }

    /**
     * Add layout links to general layout updates for themes
     *
     * @param ThemeInterface $source
     * @param ThemeInterface $target
     * @return void
     */
    protected function _copyLayoutCustomization(ThemeInterface $source, ThemeInterface $target)
    {
        $update = $this->_updateFactory->create();
        /** @var ResourceLayoutUpdateCollection $targetUpdates */
        $targetUpdates = $update->getCollection();
        $targetUpdates->addThemeFilter($target->getId());
        $targetUpdates->delete();

        /** @var ResourceLayoutLinkCollection $sourceCollection */
        $sourceCollection = $this->_link->getCollection();
        $sourceCollection->addThemeFilter($source->getId());
        /** @var $layoutLink Link */
        foreach ($sourceCollection as $layoutLink) {
            /** @var ModelLayoutUpdate $update */
            $update = $this->_updateFactory->create();
            $update->load($layoutLink->getLayoutUpdateId());
            if ($update->getId()) {
                $update->setId(null);
                $update->save();
                $layoutLink->setThemeId($target->getId());
                $layoutLink->setLayoutUpdateId($update->getId());
                $layoutLink->setId(null);
                $layoutLink->save();
            }
        }
    }

    /**
     * Copy customizations stored in a file system from one theme to another, overriding existing data
     *
     * @param ThemeInterface $source
     * @param ThemeInterface $target
     * @return void
     */
    protected function _copyFilesystemCustomization(ThemeInterface $source, ThemeInterface $target)
    {
        $sourcePath = $this->_customizationPath->getCustomizationPath($source);
        $targetPath = $this->_customizationPath->getCustomizationPath($target);

        if (!$sourcePath || !$targetPath) {
            return;
        }

        $this->_deleteFilesRecursively($targetPath);

        if ($this->_directory->isDirectory($sourcePath)) {
            $this->_copyFilesRecursively($sourcePath, $sourcePath, $targetPath);
        }
    }

    /**
     * Copies all files in a directory recursively
     *
     * @param string $baseDir
     * @param string $sourceDir
     * @param string $targetDir
     * @return void
     */
    protected function _copyFilesRecursively($baseDir, $sourceDir, $targetDir)
    {
        foreach ($this->_directory->read($sourceDir) as $path) {
            if ($this->_directory->isDirectory($path)) {
                $this->_copyFilesRecursively($baseDir, $path, $targetDir);
            } else {
                $filePath = substr($path, strlen($baseDir) + 1);
                $this->_directory->copyFile($path, $targetDir . '/' . $filePath);
            }
        }
    }

    /**
     * Delete all files in a directory recursively
     *
     * @param string $targetDir
     * @return void
     */
    protected function _deleteFilesRecursively($targetDir)
    {
        if ($this->_directory->isExist($targetDir)) {
            foreach ($this->_directory->read($targetDir) as $path) {
                $this->_directory->delete($path);
            }
        }
    }
}
