<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Backup;

/**
 * Class to work with archives
 *
 * @api
 * @since 2.0.0
 */
abstract class AbstractBackup implements BackupInterface
{
    /**
     * Backup name
     *
     * @var string
     * @since 2.0.0
     */
    protected $_name;

    /**
     * Backup creation date
     *
     * @var int
     * @since 2.0.0
     */
    protected $_time;

    /**
     * Backup file extension
     *
     * @var string
     * @since 2.0.0
     */
    protected $_backupExtension;

    /**
     * Resource model
     *
     * @var object
     * @since 2.0.0
     */
    protected $_resourceModel;

    /**
     * Magento's root directory
     *
     * @var string
     * @since 2.0.0
     */
    protected $_rootDir;

    /**
     * Path to directory where backups stored
     *
     * @var string
     * @since 2.0.0
     */
    protected $_backupsDir;

    /**
     * Is last operation completed successfully
     *
     * @var bool
     * @since 2.0.0
     */
    protected $_lastOperationSucceed = false;

    /**
     * Last failed operation error message
     *
     * @var string
     * @since 2.0.0
     */
    protected $_lastErrorMessage;

    /**
     * Set Backup Extension
     *
     * @param string $backupExtension
     * @return $this
     * @since 2.0.0
     */
    public function setBackupExtension($backupExtension)
    {
        $this->_backupExtension = $backupExtension;
        return $this;
    }

    /**
     * Get Backup Extension
     *
     * @return string
     * @since 2.0.0
     */
    public function getBackupExtension()
    {
        return $this->_backupExtension;
    }

    /**
     * Set Resource Model
     *
     * @param object $resourceModel
     * @return $this
     * @since 2.0.0
     */
    public function setResourceModel($resourceModel)
    {
        $this->_resourceModel = $resourceModel;
        return $this;
    }

    /**
     * Get Resource Model
     *
     * @return object
     * @since 2.0.0
     */
    public function getResourceModel()
    {
        return $this->_resourceModel;
    }

    /**
     * Set Time
     *
     * @param int $time
     * @return $this
     * @since 2.0.0
     */
    public function setTime($time)
    {
        $this->_time = $time;
        return $this;
    }

    /**
     * Get Time
     *
     * @return int
     * @since 2.0.0
     */
    public function getTime()
    {
        return $this->_time;
    }

    /**
     * Set root directory of Magento installation
     *
     * @param string $rootDir
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return $this
     * @since 2.0.0
     */
    public function setRootDir($rootDir)
    {
        if (!is_dir($rootDir)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase('Bad root directory')
            );
        }

        $this->_rootDir = $rootDir;
        return $this;
    }

    /**
     * Get Magento's root directory
     * @return string
     * @since 2.0.0
     */
    public function getRootDir()
    {
        return $this->_rootDir;
    }

    /**
     * Set path to directory where backups stored
     *
     * @param string $backupsDir
     * @return $this
     * @since 2.0.0
     */
    public function setBackupsDir($backupsDir)
    {
        $this->_backupsDir = $backupsDir;
        return $this;
    }

    /**
     * Get path to directory where backups stored
     *
     * @return string
     * @since 2.0.0
     */
    public function getBackupsDir()
    {
        return $this->_backupsDir;
    }

    /**
     * Get path to backup
     *
     * @return string
     * @since 2.0.0
     */
    public function getBackupPath()
    {
        return $this->getBackupsDir() . '/' . $this->getBackupFilename();
    }

    /**
     * Get backup file name
     *
     * @return string
     * @since 2.0.0
     */
    public function getBackupFilename()
    {
        $filename = $this->getTime() . '_' . $this->getType();

        $name = $this->getName();

        if (!empty($name)) {
            $filename .= '_' . $name;
        }

        $filename .= '.' . $this->getBackupExtension();

        return $filename;
    }

    /**
     * Check whether last operation completed successfully
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @since 2.0.0
     */
    public function getIsSuccess()
    {
        return $this->_lastOperationSucceed;
    }

    /**
     * Get last error message
     *
     * @return string
     * @since 2.0.0
     */
    public function getErrorMessage()
    {
        return $this->_lastErrorMessage;
    }

    /**
     * Set error message
     *
     * @param string $errorMessage
     * @return void
     * @since 2.0.0
     */
    public function setErrorMessage($errorMessage)
    {
        $this->_lastErrorMessage = $errorMessage;
    }

    /**
     * Set backup name
     *
     * @param string $name
     * @param bool $applyFilter
     * @return $this
     * @since 2.0.0
     */
    public function setName($name, $applyFilter = true)
    {
        if ($applyFilter) {
            $name = $this->_filterName($name);
        }
        $this->_name = $name;
        return $this;
    }

    /**
     * Get backup name
     *
     * @return string
     * @since 2.0.0
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Get backup display name
     *
     * @return string
     * @since 2.0.0
     */
    public function getDisplayName()
    {
        return str_replace('_', ' ', $this->_name);
    }

    /**
     * Removes disallowed characters and replaces spaces with underscores
     *
     * @param string $name
     * @return string
     * @since 2.0.0
     */
    protected function _filterName($name)
    {
        $name = trim(preg_replace('/[^\da-zA-Z ]/', '', $name));
        $name = preg_replace('/\s{2,}/', ' ', $name);
        $name = str_replace(' ', '_', $name);

        return $name;
    }
}
