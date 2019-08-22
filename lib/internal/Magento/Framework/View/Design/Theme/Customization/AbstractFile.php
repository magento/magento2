<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Design\Theme\Customization;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Theme file service abstract class
 */
abstract class AbstractFile implements
    \Magento\Framework\View\Design\Theme\Customization\FileInterface,
    \Magento\Framework\View\Design\Theme\Customization\FileAssetInterface
{
    /**
     * Customization path
     *
     * @var \Magento\Framework\View\Design\Theme\Customization\Path
     */
    protected $_customizationPath;

    /**
     * File factory
     *
     * @var \Magento\Framework\View\Design\Theme\FileFactory
     */
    protected $_fileFactory;

    /**
     * File system
     *
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Design\Theme\Customization\Path $customizationPath
     * @param \Magento\Framework\View\Design\Theme\FileFactory $fileFactory
     * @param \Magento\Framework\Filesystem $filesystem
     */
    public function __construct(
        \Magento\Framework\View\Design\Theme\Customization\Path $customizationPath,
        \Magento\Framework\View\Design\Theme\FileFactory $fileFactory,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->_customizationPath = $customizationPath;
        $this->_fileFactory = $fileFactory;
        $this->_filesystem = $filesystem;
    }

    /**
     * Create class instance with specified parameters
     *
     * @return \Magento\Framework\View\Design\Theme\FileInterface
     */
    public function create()
    {
        $file = $this->_fileFactory->create();
        $file->setCustomizationService($this);
        return $file;
    }

    /**
     * Returns customization full path
     *
     * @param \Magento\Framework\View\Design\Theme\FileInterface $file
     * @return string
     */
    public function getFullPath(\Magento\Framework\View\Design\Theme\FileInterface $file)
    {
        $customizationPath = $this->_customizationPath->getCustomizationPath($file->getTheme());
        return $customizationPath . '/' . $file->getData('file_path');
    }

    /**
     * Prepare the file
     *
     * @param \Magento\Framework\View\Design\Theme\FileInterface $file
     * @return $this
     */
    public function prepareFile(\Magento\Framework\View\Design\Theme\FileInterface $file)
    {
        $file->setData('file_type', $this->getType());
        if (!$file->getId()) {
            $this->_prepareFileName($file);
            $this->_prepareFilePath($file);
            $this->_prepareSortOrder($file);
        }
        return $this;
    }

    /**
     * Creates or updates file of customization in filesystem
     *
     * @param \Magento\Framework\View\Design\Theme\FileInterface $file
     * @return $this
     */
    public function save(\Magento\Framework\View\Design\Theme\FileInterface $file)
    {
        $this->_saveFileContent($this->getFullPath($file), $file->getContent());
        return $this;
    }

    /**
     * Deletes file of customization in filesystem
     *
     * @param \Magento\Framework\View\Design\Theme\FileInterface $file
     * @return $this
     */
    public function delete(\Magento\Framework\View\Design\Theme\FileInterface $file)
    {
        $this->_deleteFileContent($this->getFullPath($file));
        return $this;
    }

    /**
     * Prepares filename of file
     *
     * @param \Magento\Framework\View\Design\Theme\FileInterface $file
     * @return void
     */
    protected function _prepareFileName(\Magento\Framework\View\Design\Theme\FileInterface $file)
    {
        $customFiles = $file->getTheme()->getCustomization()->getFilesByType($this->getType());

        $fileName = $file->getFileName();
        $fileInfo = pathinfo($fileName);
        $fileIndex = 0;
        /** @var $customFile \Magento\Framework\View\Design\Theme\FileInterface */
        foreach ($customFiles as $customFile) {
            if ($fileName === $customFile->getFileName()) {
                $fileName = sprintf('%s_%d.%s', $fileInfo['filename'], ++$fileIndex, $fileInfo['extension']);
            }
        }
        $file->setFileName($fileName);
    }

    /**
     * Prepares relative path of file
     *
     * @param \Magento\Framework\View\Design\Theme\FileInterface $file
     * @return void
     */
    protected function _prepareFilePath(\Magento\Framework\View\Design\Theme\FileInterface $file)
    {
        $file->setData('file_path', $this->getContentType() . '/' . $file->getFileName());
    }

    /**
     * Prepares sort order of custom file
     *
     * @param \Magento\Framework\View\Design\Theme\FileInterface $file
     * @return void
     */
    protected function _prepareSortOrder(\Magento\Framework\View\Design\Theme\FileInterface $file)
    {
        $customFiles = $file->getTheme()->getCustomization()->getFilesByType($this->getType());
        $sortOrderIndex = (int)$file->getData('sort_order');
        foreach ($customFiles as $customFile) {
            $prevSortOrderIndex = $customFile->getData('sort_order');
            if ($prevSortOrderIndex > $sortOrderIndex) {
                $sortOrderIndex = $prevSortOrderIndex;
            }
        }
        $file->setData('sort_order', ++$sortOrderIndex);
    }

    /**
     * Creates or updates file of customization in filesystem
     *
     * @param string $filePath
     * @param string $content
     * @return void
     */
    protected function _saveFileContent($filePath, $content)
    {
        $this->getDirectoryWrite()->delete($filePath);
        if (!empty($content)) {
            $this->getDirectoryWrite()->writeFile($this->getDirectoryWrite()->getRelativePath($filePath), $content);
        }
    }

    /**
     * Deletes file of customization in filesystem
     *
     * @param string $filePath
     * @return void
     */
    protected function _deleteFileContent($filePath)
    {
        $filePath = $this->getDirectoryWrite()->getRelativePath($filePath);
        if ($this->getDirectoryWrite()->touch($filePath)) {
            $this->getDirectoryWrite()->delete($filePath);
        }
    }

    /**
     * Returns filesystem directory instance for write operations
     *
     * @return \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected function getDirectoryWrite()
    {
        return $this->_filesystem->getDirectoryWrite(DirectoryList::ROOT);
    }
}
