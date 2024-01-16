<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Model\Import;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\TargetDirectory;
use Magento\Framework\Filesystem\DriverPool;

/**
 * Import entity product model
 *
 * @api
 * @since 100.0.2
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:disable Magento2.Functions.DiscouragedFunction
 */
class Uploader extends \Magento\MediaStorage\Model\File\Uploader
{

    /**
     * HTTP scheme
     * used to compare against the filename and select the proper DriverPool adapter
     * @var string
     */
    private $httpScheme = 'http://';

    /**
     * Temp directory.
     *
     * @var string
     */
    protected $_tmpDir = '';

    /**
     * Destination directory.
     *
     * @var string
     */
    protected $_destDir = '';

    /**
     * All mime types.
     *
     * @var array
     */
    protected $_allowedMimeTypes = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'png' => 'image/png',
    ];

    public const DEFAULT_FILE_TYPE = 'application/octet-stream';

    /**
     * @var \Magento\Framework\Image\AdapterFactory
     */
    protected $_imageFactory;

    /**
     * @var \Magento\MediaStorage\Model\File\Validator\NotProtectedExtension
     */
    protected $_validator;

    /**
     * Instance of filesystem directory write interface.
     *
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $_directory;

    /**
     * Instance of filesystem read factory.
     *
     * @var \Magento\Framework\Filesystem\File\ReadFactory
     */
    protected $_readFactory;

    /**
     * Instance of media file storage database.
     *
     * @var \Magento\MediaStorage\Helper\File\Storage\Database
     */
    protected $_coreFileStorageDb;

    /**
     * Instance of media file storage.
     *
     * @var \Magento\MediaStorage\Helper\File\Storage
     */
    protected $_coreFileStorage;

    /**
     * Instance of random data generator.
     *
     * @var \Magento\Framework\Math\Random
     */
    private $random;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * Directory and filename must be no more than 255 characters in length
     *
     * @var int
     */
    private $maxFilenameLength = 255;

    /**
     * @var TargetDirectory
     */
    private $targetDirectory;

    /**
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $coreFileStorageDb
     * @param \Magento\MediaStorage\Helper\File\Storage $coreFileStorage
     * @param \Magento\Framework\Image\AdapterFactory $imageFactory
     * @param \Magento\MediaStorage\Model\File\Validator\NotProtectedExtension $validator
     * @param Filesystem $filesystem
     * @param Filesystem\File\ReadFactory $readFactory
     * @param string|null $filePath
     * @param \Magento\Framework\Math\Random|null $random
     * @param TargetDirectory|null $targetDirectory
     * @throws FileSystemException
     * @throws LocalizedException
     */
    public function __construct(
        \Magento\MediaStorage\Helper\File\Storage\Database $coreFileStorageDb,
        \Magento\MediaStorage\Helper\File\Storage $coreFileStorage,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        \Magento\MediaStorage\Model\File\Validator\NotProtectedExtension $validator,
        Filesystem $filesystem,
        Filesystem\File\ReadFactory $readFactory,
        $filePath = null,
        \Magento\Framework\Math\Random $random = null,
        TargetDirectory $targetDirectory = null
    ) {
        $this->_imageFactory = $imageFactory;
        $this->_coreFileStorageDb = $coreFileStorageDb;
        $this->_coreFileStorage = $coreFileStorage;
        $this->_validator = $validator;
        $this->fileSystem = $filesystem;
        $this->_directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $this->_readFactory = $readFactory;
        if ($filePath !== null) {
            $this->_setUploadFile($filePath);
        }
        $this->random = $random ?: ObjectManager::getInstance()->get(\Magento\Framework\Math\Random::class);
        $this->targetDirectory = $targetDirectory ?: ObjectManager::getInstance()->get(TargetDirectory::class);
    }

    /**
     * Initiate uploader default settings
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
     * @param bool $renameFileOff
     * @return array
     * @throws LocalizedException
     */
    public function move($fileName, $renameFileOff = false)
    {
        $this->setAllowRenameFiles(!$renameFileOff);

        if ($fileName && preg_match('/\bhttps?:\/\//i', $fileName, $matches)) {
            $url = str_replace($matches[0], '', $fileName);
            $driver = ($matches[0] === $this->httpScheme) ? DriverPool::HTTP : DriverPool::HTTPS;
            $tmpFilePath = $this->downloadFileFromUrl($url, $driver);
        } else {
            $tmpFilePath = $this->_directory->getRelativePath($this->getTempFilePath($fileName));
        }

        $this->_setUploadFile($tmpFilePath);
        $rootDirectory = $this->targetDirectory->getDirectoryRead(DirectoryList::ROOT);
        $destDir = $rootDirectory->getAbsolutePath($this->getDestDir());
        $result = $this->save($destDir);

        if (\is_array($result)) {
            unset($result['path']);
            $result['name'] = self::getCorrectFileName($result['name']);

            // Directory and filename must be no more than 255 characters in length
            if (strlen($result['file'] ?? '') > $this->maxFilenameLength) {
                throw new \LengthException(
                    __('Filename is too long; must be %1 characters or less', $this->maxFilenameLength)
                );
            }
        }

        return $result;
    }

    /**
     * Writes a url-based file to the temp directory.
     *
     * @param string $url
     * @param string $driver
     * @return string
     * @throws LocalizedException
     */
    private function downloadFileFromUrl($url, $driver)
    {
        $parsedUrlPath = parse_url($url, PHP_URL_PATH);

        if (!$parsedUrlPath) {
            throw new LocalizedException(__('Could not parse resource url.'));
        }
        $urlPathValues = explode('/', $parsedUrlPath);
        $fileName = preg_replace('/[^a-z0-9\._-]+/i', '', end($urlPathValues));
        //phpcs:ignore Magento2.Functions.DiscouragedFunction
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);

        if ($fileExtension && !$this->checkAllowedExtension($fileExtension)) {
            throw new LocalizedException(__('Disallowed file type.'));
        }
        $tmpFileName = str_replace(".$fileExtension", '', $fileName);
        $tmpFileName .= '_' . $this->random->getRandomString(16);
        $tmpFileName .= $fileExtension ? ".$fileExtension" : '';
        $tmpFilePath = $this->_directory->getRelativePath($this->getTempFilePath($tmpFileName));

        if (!$this->_directory->isWritable($this->getTmpDir())) {
            throw new LocalizedException(
                __('Import images directory must be writable in order to process remote images.')
            );
        }
        $this->_directory->writeFile(
            $tmpFilePath,
            $this->_readFactory->create($url, $driver)->readAll()
        );

        return $tmpFilePath;
    }

    /**
     * Prepare information about the file for moving
     *
     * @param string $filePath
     * @return void
     * @throws LocalizedException
     */
    protected function _setUploadFile($filePath)
    {
        try {
            $fullPath = $this->_directory->getAbsolutePath($filePath);
            if ($this->getTmpDir()) {
                $tmpDir = $this->fileSystem->getDirectoryReadByPath(
                    $this->_directory->getAbsolutePath($this->getTmpDir())
                );
            } else {
                $tmpDir = $this->_directory;
            }
            $readable = $tmpDir->isReadable($fullPath);
        } catch (ValidatorException $exception) {
            $readable = false;
        }
        if (!$readable) {
            throw new LocalizedException(
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
     * @throws LocalizedException
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
            throw new LocalizedException(__('Disallowed file type.'));
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
        $directoryRoot = $this->targetDirectory->getDirectoryWrite(DirectoryList::ROOT);
        if (is_string($path) && $directoryRoot->isWritable($path)) {
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
            $tmpRealPath = $this->_directory->getDriver()->getRealPath(
                $this->_directory->getAbsolutePath($tmpPath)
            );
            $destinationRealPath = $this->_directory->getDriver()->getRealPath($destPath);
            $relativeDestPath = $this->_directory->getRelativePath($destPath);
            $isSameFile = $tmpRealPath === $destinationRealPath;
            $rootDirectory = $this->targetDirectory->getDirectoryWrite(DirectoryList::ROOT);
            return $isSameFile ?: $this->_directory->copyFile($tmpPath, $relativeDestPath, $rootDirectory);
        } else {
            return false;
        }
    }

    /**
     * Append temp path to filename
     *
     * @param string $filename
     * @return string
     */
    private function getTempFilePath(string $filename): string
    {
        return $this->getTmpDir()
            ? rtrim($this->getTmpDir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename
            : $filename;
    }

    /**
     * @inheritdoc
     */
    protected function chmod($file)
    {
        //phpcs:ignore Squiz.PHP.NonExecutableCode.ReturnNotRequired
        return;
    }
}
