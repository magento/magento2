<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Model\Import;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Import entity product model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Uploader extends \Magento\Core\Model\File\Uploader
{
    /**
     * @var string
     */
    protected $_tmpDir = '';

    /**
     * @var string
     */
    protected $_destDir = '';

    /**
     * @var array
     */
    protected $_allowedMimeTypes = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'png' => 'image/png',
    ];

    const DEFAULT_FILE_TYPE = 'application/octet-stream';

    /**
     * @var \Magento\Framework\Image\AdapterFactory
     */
    protected $_imageFactory;

    /**
     * @var \Magento\Core\Model\File\Validator\NotProtectedExtension
     */
    protected $_validator;

    /**
     * @param \Magento\Core\Helper\File\Storage\Database $coreFileStorageDb
     * @param \Magento\Core\Helper\File\Storage $coreFileStorage
     * @param \Magento\Framework\Image\AdapterFactory $imageFactory
     * @param \Magento\Core\Model\File\Validator\NotProtectedExtension $validator
     * @param \Magento\Framework\Filesystem $filesystem
     * @param string $filePath
     */
    public function __construct(
        \Magento\Core\Helper\File\Storage\Database $coreFileStorageDb,
        \Magento\Core\Helper\File\Storage $coreFileStorage,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        \Magento\Core\Model\File\Validator\NotProtectedExtension $validator,
        \Magento\Framework\Filesystem $filesystem,
        $filePath = null
    ) {
        if (!is_null($filePath)) {
            $this->_setUploadFile($filePath);
        }
        $this->_imageFactory = $imageFactory;
        $this->_coreFileStorageDb = $coreFileStorageDb;
        $this->_coreFileStorage = $coreFileStorage;
        $this->_validator = $validator;
        $this->_directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
    }

    /**
     * Initiate uploader defoult settings
     *
     * @return void
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
     * @return array
     */
    public function move($fileName)
    {
        $filePath = $this->_directory->getRelativePath($this->getTmpDir() . '/' . $fileName);
        $this->_setUploadFile($filePath);
        $result = $this->save($this->getDestDir());
        $result['name'] = self::getCorrectFileName($result['name']);
        return $result;
    }

    /**
     * Prepare information about the file for moving
     *
     * @param string $filePath
     * @return void
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _setUploadFile($filePath)
    {
        if (!$this->_directory->isReadable($filePath)) {
            throw new \Magento\Framework\Model\Exception("File '{$filePath}' was not found or has read restriction.");
        }
        $this->_file = $this->_readFileInfo($filePath);

        $this->_validateFile();
    }

    /**
     * Reads file info
     *
     * @param string $filePath
     * @return array
     */
    protected function _readFileInfo($filePath)
    {
        $fileInfo = pathinfo($filePath);
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
                $params['object']->{$params['method']}($filePath);
            }
        }
    }

    /**
     * Returns file MIME type by extension
     *
     * @param string $ext
     * @return string
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
     */
    protected function _moveFile($tmpPath, $destPath)
    {
        if ($this->_directory->isFile($tmpPath)) {
            return $this->_directory->copyFile($tmpPath, $destPath);
        } else {
            return false;
        }
    }
}
