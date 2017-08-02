<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\MediaStorage\Model\File;

/**
 * Core file uploader model
 *
 * @api
 * @since 2.0.0
 */
class Uploader extends \Magento\Framework\File\Uploader
{
    /**
     * Flag, that defines should DB processing be skipped
     *
     * @var bool
     * @since 2.0.0
     */
    protected $_skipDbProcessing = false;

    /**
     * Core file storage
     *
     * @var \Magento\MediaStorage\Helper\File\Storage
     * @since 2.0.0
     */
    protected $_coreFileStorage = null;

    /**
     * Core file storage database
     *
     * @var \Magento\MediaStorage\Helper\File\Storage\Database
     * @since 2.0.0
     */
    protected $_coreFileStorageDb = null;

    /**
     * @var \Magento\MediaStorage\Model\File\Validator\NotProtectedExtension
     * @since 2.0.0
     */
    protected $_validator;

    /**
     * @param string $fileId
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $coreFileStorageDb
     * @param \Magento\MediaStorage\Helper\File\Storage $coreFileStorage
     * @param \Magento\MediaStorage\Model\File\Validator\NotProtectedExtension $validator
     * @since 2.0.0
     */
    public function __construct(
        $fileId,
        \Magento\MediaStorage\Helper\File\Storage\Database $coreFileStorageDb,
        \Magento\MediaStorage\Helper\File\Storage $coreFileStorage,
        \Magento\MediaStorage\Model\File\Validator\NotProtectedExtension $validator
    ) {
        $this->_coreFileStorageDb = $coreFileStorageDb;
        $this->_coreFileStorage = $coreFileStorage;
        $this->_validator = $validator;
        parent::__construct($fileId);
    }

    /**
     * Save file to storage
     *
     * @param  array $result
     * @return $this
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getFileSize()
    {
        return $this->_file['size'];
    }

    /**
     * Validate file
     *
     * @return array
     * @since 2.0.0
     */
    public function validateFile()
    {
        $this->_validateFile();
        return $this->_file;
    }
}
