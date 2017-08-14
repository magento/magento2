<?php
/**
 * Theme file uploader service
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Uploader;

use Magento\Framework\Convert\DataSize;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\DirectoryList;

/**
 * Class \Magento\Theme\Model\Uploader\Service
 *
 */
class Service
{
    /**
     * Uploaded file path
     *
     * @var string|null
     */
    protected $_filePath;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    protected $_tmpDirectory;

    /**
     * File size
     *
     * @var \Magento\Framework\File\Size
     */
    protected $_fileSize;

    /**
     * Data size converter
     *
     * @var \Magento\Framework\Convert\DataSize
     */
    protected $dataSize;

    /**
     * File uploader
     *
     * @var \Magento\MediaStorage\Model\File\Uploader
     */
    protected $_uploader;

    /**
     * @var \Magento\MediaStorage\Model\File\Uploader
     */
    protected $_uploaderFactory;

    /**
     * @var  string|null
     */
    protected $_cssUploadLimit;

    /**
     * @var  string|null
     */
    protected $_jsUploadLimit;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\File\Size $fileSize
     * @param \Magento\Framework\Convert\DataSize $dataSize
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory
     * @param array $uploadLimits keys are 'css' and 'js' for file type, values defines maximum file size, example: 2M
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\File\Size $fileSize,
        \Magento\Framework\Convert\DataSize $dataSize,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        array $uploadLimits = []
    ) {
        $this->_tmpDirectory = $filesystem->getDirectoryRead(DirectoryList::SYS_TMP);
        $this->_fileSize = $fileSize;
        $this->_uploaderFactory = $uploaderFactory;
        $this->dataSize = $dataSize;
        if (isset($uploadLimits['css'])) {
            $this->_cssUploadLimit = $uploadLimits['css'];
        }
        if (isset($uploadLimits['js'])) {
            $this->_jsUploadLimit = $uploadLimits['js'];
        }
    }

    /**
     * Upload css file
     *
     * @param string $file - Key in the $_FILES array
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function uploadCssFile($file)
    {
        /** @var $fileUploader \Magento\MediaStorage\Model\File\Uploader */
        $fileUploader = $this->_uploaderFactory->create(['fileId' => $file]);
        $fileUploader->setAllowedExtensions(['css']);
        $fileUploader->setAllowRenameFiles(true);
        $fileUploader->setAllowCreateFolders(true);

        $isValidFileSize = $this->_validateFileSize($fileUploader->getFileSize(), $this->getCssUploadMaxSize());
        if (!$isValidFileSize) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The CSS file must be less than %1M.', $this->getCssUploadMaxSizeInMb())
            );
        }

        $file = $fileUploader->validateFile();
        return ['filename' => $file['name'], 'content' => $this->getFileContent($file['tmp_name'])];
    }

    /**
     * Upload js file
     *
     * @param string $file - Key in the $_FILES array
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function uploadJsFile($file)
    {
        /** @var $fileUploader \Magento\MediaStorage\Model\File\Uploader */
        $fileUploader = $this->_uploaderFactory->create(['fileId' => $file]);
        $fileUploader->setAllowedExtensions(['js']);
        $fileUploader->setAllowRenameFiles(true);
        $fileUploader->setAllowCreateFolders(true);

        $isValidFileSize = $this->_validateFileSize($fileUploader->getFileSize(), $this->getJsUploadMaxSize());
        if (!$isValidFileSize) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The JS file must be less than %1M.', $this->getJsUploadMaxSizeInMb())
            );
        }

        $file = $fileUploader->validateFile();
        return ['filename' => $file['name'], 'content' => $this->getFileContent($file['tmp_name'])];
    }

    /**
     * Get uploaded file content
     *
     * @param string $filePath
     * @return string
     */
    public function getFileContent($filePath)
    {
        return $this->_tmpDirectory->readFile($this->_tmpDirectory->getRelativePath($filePath));
    }

    /**
     * Get css upload max size
     *
     * @return int
     */
    public function getCssUploadMaxSize()
    {
        return $this->_getMaxUploadSize($this->_cssUploadLimit);
    }

    /**
     * Get js upload max size
     *
     * @return int
     */
    public function getJsUploadMaxSize()
    {
        return $this->_getMaxUploadSize($this->_jsUploadLimit);
    }

    /**
     * Get max upload size
     *
     * @param string $configuredLimit
     * @return int
     */
    private function _getMaxUploadSize($configuredLimit)
    {
        $maxIniUploadSize = $this->_fileSize->getMaxFileSize();
        if ($configuredLimit === null) {
            return $maxIniUploadSize;
        }
        $maxUploadSize = $this->dataSize->convertSizeToBytes($configuredLimit);
        return min($maxUploadSize, $maxIniUploadSize);
    }

    /**
     * Get css upload max size in megabytes
     *
     * @return float
     */
    public function getCssUploadMaxSizeInMb()
    {
        return $this->_fileSize->getFileSizeInMb($this->getCssUploadMaxSize());
    }

    /**
     * Get js upload max size in megabytes
     *
     * @return float
     */
    public function getJsUploadMaxSizeInMb()
    {
        return $this->_fileSize->getFileSizeInMb($this->getJsUploadMaxSize());
    }

    /**
     * Validate max file size
     *
     * @param int $fileSize
     * @param int $maxFileSize
     * @return bool
     */
    protected function _validateFileSize($fileSize, $maxFileSize)
    {
        if ($fileSize > $maxFileSize) {
            return false;
        }
        return true;
    }
}
