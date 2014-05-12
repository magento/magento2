<?php
/**
 * Theme file uploader service
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Theme\Model\Uploader;

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
     * File uploader
     *
     * @var \Magento\Core\Model\File\Uploader
     */
    protected $_uploader;

    /**
     * @var \Magento\Core\Model\File\Uploader
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
     * @param \Magento\Framework\App\Filesystem $filesystem
     * @param \Magento\Framework\File\Size $fileSize
     * @param \Magento\Core\Model\File\UploaderFactory $uploaderFactory
     * @param array $uploadLimits keys are 'css' and 'js' for file type, values defines maximum file size, example: 2M
     */
    public function __construct(
        \Magento\Framework\App\Filesystem $filesystem,
        \Magento\Framework\File\Size $fileSize,
        \Magento\Core\Model\File\UploaderFactory $uploaderFactory,
        array $uploadLimits = array()
    ) {
        $this->_tmpDirectory = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem::SYS_TMP_DIR);
        $this->_fileSize = $fileSize;
        $this->_uploaderFactory = $uploaderFactory;
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
     * @throws \Magento\Framework\Model\Exception
     */
    public function uploadCssFile($file)
    {
        /** @var $fileUploader \Magento\Core\Model\File\Uploader */
        $fileUploader = $this->_uploaderFactory->create(array('fileId' => $file));
        $fileUploader->setAllowedExtensions(array('css'));
        $fileUploader->setAllowRenameFiles(true);
        $fileUploader->setAllowCreateFolders(true);

        $isValidFileSize = $this->_validateFileSize($fileUploader->getFileSize(), $this->getCssUploadMaxSize());
        if (!$isValidFileSize) {
            throw new \Magento\Framework\Model\Exception(
                __('The CSS file must be less than %1M.', $this->getCssUploadMaxSizeInMb())
            );
        }

        $file = $fileUploader->validateFile();
        return array('filename' => $file['name'], 'content' => $this->getFileContent($file['tmp_name']));
    }

    /**
     * Upload js file
     *
     * @param string $file - Key in the $_FILES array
     * @return array
     * @throws \Magento\Framework\Model\Exception
     */
    public function uploadJsFile($file)
    {
        /** @var $fileUploader \Magento\Core\Model\File\Uploader */
        $fileUploader = $this->_uploaderFactory->create(array('fileId' => $file));
        $fileUploader->setAllowedExtensions(array('js'));
        $fileUploader->setAllowRenameFiles(true);
        $fileUploader->setAllowCreateFolders(true);

        $isValidFileSize = $this->_validateFileSize($fileUploader->getFileSize(), $this->getJsUploadMaxSize());
        if (!$isValidFileSize) {
            throw new \Magento\Framework\Model\Exception(
                __('The JS file must be less than %1M.', $this->getJsUploadMaxSizeInMb())
            );
        }

        $file = $fileUploader->validateFile();
        return array('filename' => $file['name'], 'content' => $this->getFileContent($file['tmp_name']));
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
        if (is_null($configuredLimit)) {
            return $maxIniUploadSize;
        }
        $maxUploadSize = $this->_fileSize->convertSizeToInteger($configuredLimit);
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
