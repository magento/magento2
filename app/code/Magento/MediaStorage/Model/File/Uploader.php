<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\MediaStorage\Model\File;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Validation\ValidationException;
use Magento\MediaStorage\Model\File\Validator\Image;

/**
 * Core file uploader model
 *
 * @api
 * @since 100.0.2
 */
class Uploader extends \Magento\Framework\File\Uploader
{
    /**
     * Flag, that defines should DB processing be skipped
     *
     * @var bool
     */
    protected $_skipDbProcessing = false;

    /**
     *  File storage
     *
     * @var \Magento\MediaStorage\Helper\File\Storage
     */
    protected $_coreFileStorage = null;

    /**
     * Core file storage database
     *
     * @var \Magento\MediaStorage\Helper\File\Storage\Database
     */
    protected $_coreFileStorageDb = null;

    /**
     * @var \Magento\MediaStorage\Model\File\Validator\NotProtectedExtension
     */
    protected $_validator;

    /**
     * @var Image
     */
    private $imageValidator;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $varDirectory;

    /**
     * @param string $fileId
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $coreFileStorageDb
     * @param \Magento\MediaStorage\Helper\File\Storage $coreFileStorage
     * @param \Magento\MediaStorage\Model\File\Validator\NotProtectedExtension $validator
     * @param \Magento\Framework\Filesystem|null $filesystem
     */
    public function __construct(
        $fileId,
        \Magento\MediaStorage\Helper\File\Storage\Database $coreFileStorageDb,
        \Magento\MediaStorage\Helper\File\Storage $coreFileStorage,
        \Magento\MediaStorage\Model\File\Validator\NotProtectedExtension $validator,
        ?\Magento\Framework\Filesystem $filesystem = null
    ) {
        $this->_coreFileStorageDb = $coreFileStorageDb;
        $this->_coreFileStorage = $coreFileStorage;
        $this->_validator = $validator;
        $filesystem = $filesystem ?: ObjectManager::getInstance()
            ->get(\Magento\Framework\Filesystem::class);
        $this->varDirectory = $filesystem->getDirectoryWrite(DirectoryList::VAR_IMPORT_EXPORT);
        parent::__construct($fileId);
    }

    /**
     * Save file to storage
     *
     * @param  array $result
     * @return $this
     */
    protected function _afterSave($result)
    {
        if (empty($result['path']) || empty($result['file'])) {
            return $this;
        }

        if ($this->_coreFileStorage->isInternalStorage() || $this->skipDbProcessing()) {
            return $this;
        }

        $this->_result['file'] = $this->_coreFileStorageDb->saveUploadedFile($result);

        return $this;
    }

    /**
     * Getter/Setter for _skipDbProcessing flag
     *
     * @param null|bool $flag
     * @return bool|\Magento\MediaStorage\Model\File\Uploader
     */
    public function skipDbProcessing($flag = null)
    {
        if ($flag === null) {
            return $this->_skipDbProcessing;
        }
        $this->_skipDbProcessing = (bool)$flag;
        return $this;
    }

    /**
     * Check protected/allowed extension
     *
     * @param string $extension
     * @return boolean
     */
    public function checkAllowedExtension($extension)
    {
        //validate with protected file types
        if (!$this->_validator->isValid($extension)) {
            return false;
        }

        return parent::checkAllowedExtension($extension);
    }

    /**
     * Get file size
     *
     * @return int
     */
    public function getFileSize()
    {
        return $this->_file['size'];
    }

    /**
     * Validate file
     *
     * @return array
     */
    public function validateFile()
    {
        $this->_validateFile();
        return $this->_file;
    }

    /**
     * Rename Uploaded File
     *
     * @param string $entity
     * @return void
     * @throws LocalizedException
     */
    public function renameFile(string $entity)
    {
        $extension = '';
        $uploadedFile = '';
        if ($this->_result !== false) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $extension = pathinfo($this->_result['file'], PATHINFO_EXTENSION);
            $uploadedFile = $this->_result['path'] . $this->_result['file'];
        }

        if (!$extension) {
            $this->varDirectory->delete($uploadedFile);
            throw new LocalizedException(__('The file you uploaded has no extension.'));
        }
        $sourceFile = $this->varDirectory->getAbsolutePath('importexport/') . $entity;

        $sourceFile .= '.' . $extension;
        $sourceFileRelative = $this->varDirectory->getRelativePath($sourceFile);

        if (strtolower($uploadedFile) != strtolower($sourceFile)) {
            if ($this->varDirectory->isExist($sourceFileRelative)) {
                $this->varDirectory->delete($sourceFileRelative);
            }

            try {
                $this->varDirectory->renameFile(
                    $this->varDirectory->getRelativePath($uploadedFile),
                    $sourceFileRelative
                );
            } catch (FileSystemException $e) {
                throw new LocalizedException(__('The source file moving process failed.'));
            }
        }
    }

    /**
     * @inheritDoc
     * @since 100.4.0
     */
    protected function _validateFile()
    {
        parent::_validateFile();

        if (!$this->getImageValidator()->isValid($this->_file['tmp_name'])) {
            throw new ValidationException(
                __('File validation failed. Check Image Processing Settings in the Store Configuration.')
            );
        }
    }

    /**
     * Return image validator class.
     *
     * Child classes __construct() don't call parent, so we have to retrieve class instance with private function.
     *
     * @return Image
     */
    private function getImageValidator(): Image
    {
        if (!$this->imageValidator) {
            $this->imageValidator = ObjectManager::getInstance()->get(Image::class);
        }

        return $this->imageValidator;
    }
}
