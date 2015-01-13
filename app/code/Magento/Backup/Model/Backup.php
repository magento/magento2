<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backup\Model;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Backup file item model
 *
 * @method string getPath()
 * @method string getName()
 * @method string getTime()
 */
class Backup extends \Magento\Framework\Object implements \Magento\Framework\Backup\Db\BackupInterface
{
    /**
     * Compress rate
     */
    const COMPRESS_RATE = 9;

    /**
     * Type of backup file
     *
     * @var string
     */
    private $_type = 'db';

    /**
     * Gz file pointer
     *
     * @var \Magento\Framework\Filesystem\File\WriteInterface
     */
    protected $_stream = null;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\Backup\Helper\Data
     */
    protected $_helper;

    /**
     * Locale model
     *
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * Backend auth session
     *
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_backendAuthSession;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $_encryptor;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $varDirectory;

    /**
     * @param \Magento\Backup\Helper\Data $helper
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Framework\Filesystem $filesystem
     * @param array $data
     */
    public function __construct(
        \Magento\Backup\Helper\Data $helper,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\Filesystem $filesystem,
        $data = []
    ) {
        $this->_encryptor = $encryptor;
        parent::__construct($data);

        $this->_filesystem = $filesystem;
        $this->varDirectory = $this->_filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->_helper = $helper;
        $this->_localeResolver = $localeResolver;
        $this->_backendAuthSession = $authSession;
    }

    /**
     * Set backup time
     *
     * @param int $time
     * @return $this
     */
    public function setTime($time)
    {
        $this->setData('time', $time);
        return $this;
    }

    /**
     * Set backup path
     *
     * @param string $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->setData('path', $path);
        return $this;
    }

    /**
     * Set backup name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->setData('name', $name);
        return $this;
    }

    /**
     * Load backup file info
     *
     * @param string $fileName
     * @param string $filePath
     * @return $this
     */
    public function load($fileName, $filePath)
    {
        $backupData = $this->_helper->extractDataFromFilename($fileName);

        $this->addData(
            [
                'id' => $filePath . '/' . $fileName,
                'time' => (int)$backupData->getTime(),
                'path' => $filePath,
                'extension' => $this->_helper->getExtensionByType($backupData->getType()),
                'display_name' => $this->_helper->nameToDisplayName($backupData->getName()),
                'name' => $backupData->getName(),
                'date_object' => new \Magento\Framework\Stdlib\DateTime\Date(
                    (int)$backupData->getTime(),
                    $this->_localeResolver->getLocaleCode()
                ),
            ]
        );

        $this->setType($backupData->getType());
        return $this;
    }

    /**
     * Checks backup file exists.
     *
     * @return bool
     */
    public function exists()
    {
        return $this->varDirectory->isFile($this->_getFilePath());
    }

    /**
     * Return file name of backup file
     *
     * @return string
     */
    public function getFileName()
    {
        $filename = $this->getTime() . "_" . $this->getType();
        $backupName = $this->getName();

        if (!empty($backupName)) {
            $filename .= '_' . $backupName;
        }

        $filename .= '.' . $this->_helper->getExtensionByType($this->getType());

        return $filename;
    }

    /**
     * Sets type of file
     *
     * @param string $value
     * @return $this
     */
    public function setType($value = 'db')
    {
        $possibleTypes = $this->_helper->getBackupTypesList();
        if (!in_array($value, $possibleTypes)) {
            $value = $this->_helper->getDefaultBackupType();
        }

        $this->_type = $value;
        $this->setData('type', $this->_type);

        return $this;
    }

    /**
     * Returns type of backup file
     *
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Set the backup file content
     *
     * @param string &$content
     * @return $this
     * @throws \Magento\Framework\Model\Exception
     */
    public function setFile(&$content)
    {
        if (!$this->hasData('time') || !$this->hasData('type') || !$this->hasData('path')) {
            throw new \Magento\Framework\Model\Exception(__('Please correct the order of creation for a new backup.'));
        }

        $this->varDirectory->writeFile($this->_getFilePath(), $content);
        return $this;
    }

    /**
     * Return content of backup file
     *
     * @return string
     * @throws \Magento\Framework\Model\Exception
     */
    public function &getFile()
    {
        if (!$this->exists()) {
            throw new \Magento\Framework\Model\Exception(__("The backup file does not exist."));
        }

        return $this->varDirectory->read($this->_getFilePath());
    }

    /**
     * Delete backup file
     *
     * @return $this
     * @throws \Magento\Framework\Model\Exception
     */
    public function deleteFile()
    {
        if (!$this->exists()) {
            throw new \Magento\Framework\Model\Exception(__("The backup file does not exist."));
        }

        $this->varDirectory->delete($this->_getFilePath());
        return $this;
    }

    /**
     * Open backup file (write or read mode)
     *
     * @param bool $write
     * @return $this
     * @throws \Magento\Backup\Exception
     * @throws \Magento\Framework\Backup\Exception\NotEnoughPermissions
     */
    public function open($write = false)
    {
        if (is_null($this->getPath())) {
            throw new \Magento\Backup\Exception(__('The backup file path was not specified.'));
        }

        if ($write && $this->varDirectory->isFile($this->_getFilePath())) {
            $this->varDirectory->delete($this->_getFilePath());
        }
        if (!$write && !$this->varDirectory->isFile($this->_getFilePath())) {
            throw new \Magento\Backup\Exception(__('The backup file "%1" does not exist.', $this->getFileName()));
        }

        $mode = $write ? 'wb' . self::COMPRESS_RATE : 'rb';

        try {
            /** @var \Magento\Framework\Filesystem\Directory\WriteInterface $varDirectory */
            $varDirectory = $this->_filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
            $this->_stream = $varDirectory->openFile(
                $this->_getFilePath(),
                $mode,
                \Magento\Framework\Filesystem\DriverPool::ZLIB
            );
        } catch (\Magento\Framework\Filesystem\FilesystemException $e) {
            throw new \Magento\Framework\Backup\Exception\NotEnoughPermissions(
                __('Sorry, but we cannot read from or write to backup file "%1".', $this->getFileName())
            );
        }

        return $this;
    }

    /**
     * Get zlib handler
     *
     * @return \Magento\Framework\Filesystem\File\WriteInterface
     * @throws \Magento\Backup\Exception
     */
    protected function _getStream()
    {
        if (is_null($this->_stream)) {
            throw new \Magento\Backup\Exception(__('The backup file handler was unspecified.'));
        }
        return $this->_stream;
    }

    /**
     * Read backup uncompressed data
     *
     * @param int $length
     * @return string
     */
    public function read($length)
    {
        return $this->_getStream()->read($length);
    }

    /**
     * Check end of file.
     *
     * @return bool
     */
    public function eof()
    {
        return $this->_getStream()->eof();
    }

    /**
     * Write to backup file
     *
     * @param string $string
     * @return $this
     * @throws \Magento\Backup\Exception
     */
    public function write($string)
    {
        try {
            $this->_getStream()->write($string);
        } catch (\Magento\Framework\Filesystem\FilesystemException $e) {
            throw new \Magento\Backup\Exception(
                __('Something went wrong writing to the backup file "%1".', $this->getFileName())
            );
        }

        return $this;
    }

    /**
     * Close open backup file
     *
     * @return $this
     */
    public function close()
    {
        $this->_getStream()->close();
        $this->_stream = null;

        return $this;
    }

    /**
     * Print output
     *
     * @return void
     */
    public function output()
    {
        if (!$this->exists()) {
            return;
        }

        /** @var \Magento\Framework\Filesystem\Directory\ReadInterface $directory */
        $directory = $this->_filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $directory = $directory->readFile($this->_getFilePath());

        echo $directory;
    }

    /**
     * @return int|mixed
     */
    public function getSize()
    {
        if (!is_null($this->getData('size'))) {
            return $this->getData('size');
        }

        if ($this->exists()) {
            $this->setData('size', $this->varDirectory->stat($this->_getFilePath())['size']);
            return $this->getData('size');
        }

        return 0;
    }

    /**
     * Validate user password
     *
     * @param string $password
     * @return bool
     */
    public function validateUserPassword($password)
    {
        $userPasswordHash = $this->_backendAuthSession->getUser()->getPassword();
        return $this->_encryptor->validateHash($password, $userPasswordHash);
    }

    /**
     * Get file path.
     *
     * @return string
     */
    protected function _getFilePath()
    {
        return $this->varDirectory->getRelativePath($this->getPath() . '/' . $this->getFileName());
    }
}
