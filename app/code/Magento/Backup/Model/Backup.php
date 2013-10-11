<?php
/**
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Backup file item model
 *
 * @method string getPath()
 * @method \Magento\Backup\Model\Backup setPath() setPath($path)
 * @method string getName()
 * @method \Magento\Backup\Model\Backup setName() setName($name)
 * @method string getTime()
 * @method \Magento\Backup\Model\Backup setTime() setTime($time)
 */
namespace Magento\Backup\Model;

class Backup extends \Magento\Object implements \Magento\Backup\Db\BackupInterface
{
    /* internal constants */
    const COMPRESS_RATE     = 9;

    /**
     * Type of backup file
     *
     * @var string
     */
    private $_type  = 'db';

    /**
     * Gz file pointer
     *
     * @var \Magento\Filesystem\Stream\Zlib
     */
    protected $_stream = null;

    /**
     * @var \Magento\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\Backup\Helper\Data
     */
    protected $_helper;

    /**
     * Core data
     *
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreData = null;

    /**
     * Locale model
     *
     * @var \Magento\Core\Model\LocaleInterface
     */
    protected $_locale;

    /**
     * Backend auth session
     *
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_backendAuthSession;

    /**
     * Construct
     *
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Backup\Helper\Data $helper
     * @param \Magento\Core\Model\LocaleInterface $locale
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Helper\Data $coreData,
        \Magento\Backup\Helper\Data $helper,
        \Magento\Core\Model\LocaleInterface $locale,
        \Magento\Backend\Model\Auth\Session $authSession,
        $data = array()
    ) {
        parent::__construct($data);

        $this->_coreData = $coreData;
        $adapter = new \Magento\Filesystem\Adapter\Zlib(self::COMPRESS_RATE);
        $this->_filesystem = new \Magento\Filesystem($adapter);
        $this->_filesystem->setIsAllowCreateDirectories(true);
        $this->_helper = $helper;
        $this->_locale = $locale;
        $this->_backendAuthSession = $authSession;
    }

    /**
     * Set backup time
     *
     * @param int $time
     * @return \Magento\Backup\Db\BackupInterface
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
     * @return \Magento\Backup\Db\BackupInterface
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
     * @return \Magento\Backup\Db\BackupInterface
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
     * @return \Magento\Backup\Model\Backup
     */
    public function load($fileName, $filePath)
    {
        $backupData = $this->_helper->extractDataFromFilename($fileName);

        $this->addData(array(
            'id'   => $filePath . DS . $fileName,
            'time' => (int)$backupData->getTime(),
            'path' => $filePath,
            'extension' => $this->_helper->getExtensionByType($backupData->getType()),
            'display_name' => $this->_helper->nameToDisplayName($backupData->getName()),
            'name' => $backupData->getName(),
            'date_object' => new \Zend_Date((int)$backupData->getTime(), $this->_locale->getLocaleCode())
        ));

        $this->setType($backupData->getType());
        return $this;
    }

    /**
     * Checks backup file exists.
     *
     * @return boolean
     */
    public function exists()
    {
        return $this->_filesystem->isFile($this->_getFilePath());
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
     * @return \Magento\Backup\Model\Backup
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
     * @param string $content
     * @return \Magento\Backup\Model\Backup
     * @throws \Magento\Core\Exception
     */
    public function setFile(&$content)
    {
        if (!$this->hasData('time') || !$this->hasData('type') || !$this->hasData('path')) {
            throw new \Magento\Core\Exception(__('Please correct the order of creation for a new backup.'));
        }

        $this->_filesystem->write($this->_getFilePath(), $content);
        return $this;
    }

    /**
     * Return content of backup file
     *
     * @return string
     * @throws \Magento\Core\Exception
     */
    public function &getFile()
    {
        if (!$this->exists()) {
            throw new \Magento\Core\Exception(__("The backup file does not exist."));
        }

        return $this->_filesystem->read($this->_getFilePath());
    }

    /**
     * Delete backup file
     *
     * @return \Magento\Backup\Model\Backup
     * @throws \Magento\Core\Exception
     */
    public function deleteFile()
    {
        if (!$this->exists()) {
            throw new \Magento\Core\Exception(__("The backup file does not exist."));
        }

        $this->_filesystem->delete($this->_getFilePath());
        return $this;
    }

    /**
     * Open backup file (write or read mode)
     *
     * @param bool $write
     * @return \Magento\Backup\Model\Backup
     * @throws \Magento\Backup\Exception
     * @throws \Magento\Backup\Exception\NotEnoughPermissions
     */
    public function open($write = false)
    {
        if (is_null($this->getPath())) {
            throw new \Magento\Backup\Exception(__('The backup file path was not specified.'));
        }

        if ($write && $this->_filesystem->isFile($this->_getFilePath())) {
            $this->_filesystem->delete($this->_getFilePath());
        }
        if (!$write && !$this->_filesystem->isFile($this->_getFilePath())) {
            throw new \Magento\Backup\Exception(__('The backup file "%1" does not exist.', $this->getFileName()));
        }

        $mode = $write ? 'wb' . self::COMPRESS_RATE : 'rb';

        try {
            $compressStream = 'compress.zlib://';
            $workingDirectory = $this->_filesystem->getWorkingDirectory();
            $this->_stream = $this->_filesystem->createAndOpenStream($compressStream . $this->_getFilePath(), $mode,
                $compressStream . $workingDirectory);
        }
        catch (\Magento\Filesystem\Exception $e) {
            throw new \Magento\Backup\Exception\NotEnoughPermissions(
                __('Sorry, but we cannot read from or write to backup file "%1".', $this->getFileName())
            );
        }

        return $this;
    }

    /**
     * Get zlib handler
     *
     * @return \Magento\Filesystem\Stream\Zlib
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
     * Read backup uncomressed data
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
     * @return \Magento\Backup\Model\Backup
     * @throws \Magento\Backup\Exception
     */
    public function write($string)
    {
        try {
            $this->_getStream()->write($string);
        }
        catch (\Magento\Filesystem\Exception $e) {
            throw new \Magento\Backup\Exception(__('Something went wrong writing to the backup file "%1".',
                $this->getFileName()));
        }

        return $this;
    }

    /**
     * Close open backup file
     *
     * @return \Magento\Backup\Model\Backup
     */
    public function close()
    {
        $this->_getStream()->close();
        $this->_stream = null;

        return $this;
    }

    /**
     * Print output
     */
    public function output()
    {
        if (!$this->exists()) {
            return ;
        }

        $stream = $this->_filesystem->createAndOpenStream($this->_getFilePath(), 'r');
        while ($buffer = $stream->read(1024)) {
            echo $buffer;
        }
        $stream->close();
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
            $this->setData('size', $this->_filesystem->getFileSize($this->_getFilePath()));
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
        return $this->_coreData->validateHash($password, $userPasswordHash);
    }

    /**
     * Load backup by it's type and creation timestamp
     *
     * @param int $timestamp
     * @param string $type
     * @return \Magento\Backup\Model\Backup
     */
    public function loadByTimeAndType($timestamp, $type)
    {
        $backupId = $timestamp . '_' . $type;

        foreach ($this->_fsCollection as $backup) {
            if ($backup->getId() == $backupId) {
                $this->setType($backup->getType())
                    ->setTime($backup->getTime())
                    ->setName($backup->getName())
                    ->setPath($backup->getPath());
                break;
            }
        }

        return $this;
    }

    /**
     * Get file path.
     *
     * @return string
     */
    protected function _getFilePath()
    {
        return $this->getPath() . DS . $this->getFileName();
    }
}
