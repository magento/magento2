<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Model\Import;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\DriverPool;

/**
 * Import entity product model
 *
 * @api
 * @since 2.0.0
 */
class Uploader extends \Magento\MediaStorage\Model\File\Uploader
{

    /**
     * HTTP scheme
     * used to compare against the filename and select the proper DriverPool adapter
     * @var string
     * @since 2.2.0
     */
    private $httpScheme = 'http://';

    /**
     * Temp directory.
     *
     * @var string
     * @since 2.0.0
     */
    protected $_tmpDir = '';

    /**
     * Destination directory.
     *
     * @var string
     * @since 2.0.0
     */
    protected $_destDir = '';

    /**
     * All mime types.
     *
     * @var array
     * @since 2.0.0
     */
    protected $_allowedMimeTypes = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'png' => 'image/png',
    ];

    const DEFAULT_FILE_TYPE = 'application/octet-stream';

    /**
     * Image factory.
     *
     * @var \Magento\Framework\Image\AdapterFactory
     * @since 2.0.0
     */
    protected $_imageFactory;

    /**
     * Validator.
     *
     * @var \Magento\MediaStorage\Model\File\Validator\NotProtectedExtension
     * @since 2.0.0
     */
    protected $_validator;

    /**
     * Instance of filesystem directory write interface.
     *
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     * @since 2.0.0
     */
    protected $_directory;

    /**
     * Instance of filesystem read factory.
     *
     * @var \Magento\Framework\Filesystem\File\ReadFactory
     * @since 2.0.0
     */
    protected $_readFactory;

    /**
     * Instance of media file storage database.
     *
     * @var \Magento\MediaStorage\Helper\File\Storage\Database
     * @since 2.0.0
     */
    protected $_coreFileStorageDb;

    /**
     * Instance of media file storage.
     *
     * @var \Magento\MediaStorage\Helper\File\Storage
     * @since 2.0.0
     */
    protected $_coreFileStorage;

    /**
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $coreFileStorageDb
     * @param \Magento\MediaStorage\Helper\File\Storage $coreFileStorage
     * @param \Magento\Framework\Image\AdapterFactory $imageFactory
     * @param \Magento\MediaStorage\Model\File\Validator\NotProtectedExtension $validator
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Filesystem\File\ReadFactory $readFactory
     * @param null $filePath
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function __construct(
        \Magento\MediaStorage\Helper\File\Storage\Database $coreFileStorageDb,
        \Magento\MediaStorage\Helper\File\Storage $coreFileStorage,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        \Magento\MediaStorage\Model\File\Validator\NotProtectedExtension $validator,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filesystem\File\ReadFactory $readFactory,
        $filePath = null
    ) {
        if ($filePath !== null) {
            $this->_setUploadFile($filePath);
        }
        $this->_imageFactory = $imageFactory;
        $this->_coreFileStorageDb = $coreFileStorageDb;
        $this->_coreFileStorage = $coreFileStorage;
        $this->_validator = $validator;
        $this->_directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $this->_readFactory = $readFactory;
    }

    /**
     * Initiate uploader default settings
     *
     * @return void
     * @since 2.0.0
     */
    public function init()
    {
        $this->setAllowRenameFiles(true);
        $this->setAllowCreateFolders(true);
        $this->setFilesDispersion(true);
        $this->setAllowedExtensions(array_keys($this->_allowedMimeTypes));
        $imageAdapter = $this->_imageFactory->create();
        $this->addValidateCallback('catalog_product_image', $imageAdapter, 'validateUploadFile');
        $this->_uploadType = self::SINGLE_STYLE;
    }

    /**
     * Proceed moving a file from TMP to destination folder
     *
     * @param string $fileName
     * @param bool $renameFileOff
     * @return array
     * @since 2.0.0
     */
    public function move($fileName, $renameFileOff = false)
    {
        if ($renameFileOff) {
            $this->setAllowRenameFiles(false);
        }
        if (preg_match('/\bhttps?:\/\//i', $fileName, $matches)) {
            $url = str_replace($matches[0], '', $fileName);

            if ($matches[0] === $this->httpScheme) {
                $read = $this->_readFactory->create($url, DriverPool::HTTP);
            } else {
                $read = $this->_readFactory->create($url, DriverPool::HTTPS);
            }

            $fileName = preg_replace('/[^a-z0-9\._-]+/i', '', $fileName);
            $this->_directory->writeFile(
                $this->_directory->getRelativePath($this->getTmpDir() . '/' . $fileName),
                $read->readAll()
            );
        }

        $filePath = $this->_directory->getRelativePath($this->getTmpDir() . '/' . $fileName);
        $this->_setUploadFile($filePath);
        $destDir = $this->_directory->getAbsolutePath($this->getDestDir());
        $result = $this->save($destDir);
        $result['name'] = self::getCorrectFileName($result['name']);
        return $result;
    }

    /**
     * Prepare information about the file for moving
     *
     * @param string $filePath
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    protected function _setUploadFile($filePath)
    {
        if (!$this->_directory->isReadable($filePath)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('File \'%1\' was not found or has read restriction.', $filePath)
            );
        }
        $this->_file = $this->_readFileInfo($filePath);

        $this->_validateFile();
    }

    /**
     * Reads file info
     *
     * @param string $filePath
     * @return array
     * @since 2.0.0
     */
    protected function _readFileInfo($filePath)
    {
        $fullFilePath = $this->_directory->getAbsolutePath($filePath);
        $fileInfo = pathinfo($fullFilePath);
        return [
            'name' => $fileInfo['basename'],
            'type' => $this->_getMimeTypeByExt($fileInfo['extension']),
            'tmp_name' => $filePath,
            'error' => 0,
            'size' => $this->_directory->stat($filePath)['size']
        ];
    }

    /**
     * Validate uploaded file by type and etc.
     *
     * @return void
     * @throws \Exception
     * @since 2.0.0
     */
    protected function _validateFile()
    {
        $filePath = $this->_file['tmp_name'];
        if ($this->_directory->isReadable($filePath)) {
            $this->_fileExists = true;
        } else {
            $this->_fileExists = false;
        }

        $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
        if (!$this->checkAllowedExtension($fileExtension)) {
            throw new \Exception('Disallowed file type.');
        }
        //run validate callbacks
        foreach ($this->_validateCallbacks as $params) {
            if (is_object($params['object'])
                && method_exists($params['object'], $params['method'])
                && is_callable([$params['object'], $params['method']])
            ) {
                $params['object']->{$params['method']}($this->_directory->getAbsolutePath($filePath));
            }
        }
    }

    /**
     * Returns file MIME type by extension
     *
     * @param string $ext
     * @return string
     * @since 2.0.0
     */
    protected function _getMimeTypeByExt($ext)
    {
        if (array_key_exists($ext, $this->_allowedMimeTypes)) {
            return $this->_allowedMimeTypes[$ext];
        }
        return '';
    }

    /**
     * Obtain TMP file path prefix
     *
     * @return string
     * @since 2.0.0
     */
    public function getTmpDir()
    {
        return $this->_tmpDir;
    }

    /**
     * Set TMP file path prefix
     *
     * @param string $path
     * @return bool
     * @since 2.0.0
     */
    public function setTmpDir($path)
    {
        if (is_string($path) && $this->_directory->isReadable($path)) {
            $this->_tmpDir = $path;
            return true;
        }
        return false;
    }

    /**
     * Obtain destination file path prefix
     *
     * @return string
     * @since 2.0.0
     */
    public function getDestDir()
    {
        return $this->_destDir;
    }

    /**
     * Set destination file path prefix
     *
     * @param string $path
     * @return bool
     * @since 2.0.0
     */
    public function setDestDir($path)
    {
        if (is_string($path) && $this->_directory->isWritable($path)) {
            $this->_destDir = $path;
            return true;
        }
        return false;
    }

    /**
     * Move files from TMP folder into destination folder
     *
     * @param string $tmpPath
     * @param string $destPath
     * @return bool
     * @since 2.0.0
     */
    protected function _moveFile($tmpPath, $destPath)
    {
        if ($this->_directory->isFile($tmpPath)) {
            $tmpRealPath = $this->_directory->getDriver()->getRealPath(
                $this->_directory->getAbsolutePath($tmpPath)
            );
            $destinationRealPath = $this->_directory->getDriver()->getRealPath($destPath);
            $relativeDestPath = $this->_directory->getRelativePath($destPath);
            $isSameFile = $tmpRealPath === $destinationRealPath;
            return $isSameFile ?: $this->_directory->copyFile($tmpPath, $relativeDestPath);
        } else {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function chmod($file)
    {
        return;
    }
}
