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
 * @category    Mage
 * @package     Mage_Backup
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Class to work with archives
 *
 * @category    Mage
 * @package     Mage_Backup
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class Mage_Backup_Abstract implements  Mage_Backup_Interface
{
    /**
     * Backup name
     *
     * @var string
     */
    protected $_name;

    /**
     * Backup creation date
     *
     * @var int
     */
    protected $_time;

    /**
     * Backup file extension
     *
     * @var string
     */
    protected $_backupExtension;

    /**
     * Resource model
     *
     * @var object
     */
    protected $_resourceModel;

    /**
     * Magento's root directory
     *
     * @var string
     */
    protected $_rootDir;

    /**
     * Path to directory where backups stored
     *
     * @var string
     */
    protected $_backupsDir;

    /**
     * Is last operation completed successfully
     *
     * @var bool
     */
    protected $_lastOperationSucceed = false;

    /**
     * Last failed operation error message
     *
     * @var string
     */
    protected $_lastErrorMessage;


    /**
     * Set Backup Extension
     *
     * @param string $backupExtension
     * @return Mage_Backup_Interface
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
     */
    public function getBackupExtension()
    {
        return $this->_backupExtension;
    }

    /**
     * Set Resource Model
     *
     * @param object $resourceModel
     * @return Mage_Backup_Interface
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
     */
    public function getResourceModel()
    {
        return $this->_resourceModel;
    }

    /**
     * Set Time
     *
     * @param int $time
     * @return Mage_Backup_Interface
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
     */
    public function getTime()
    {
        return $this->_time;
    }

    /**
     * Set root directory of Magento installation
     *
     * @param string $rootDir
     * @throws Mage_Exception
     * @return Mage_Backup_Interface
     */
    public function setRootDir($rootDir)
    {
        if (!is_dir($rootDir)) {
            throw new Mage_Exception('Bad root directory');
        }

        $this->_rootDir = $rootDir;
        return $this;
    }

    /**
     * Get Magento's root directory
     * @return string
     */
    public function getRootDir()
    {
        return $this->_rootDir;
    }

    /**
     * Set path to directory where backups stored
     *
     * @param string $backupsDir
     * @return Mage_Backup_Interface
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
     */
    public function getBackupsDir()
    {
        return $this->_backupsDir;
    }

    /**
     * Get path to backup
     *
     * @return string
     */
    public function getBackupPath()
    {
        return $this->getBackupsDir() . DS . $this->getBackupFilename();
    }

    /**
     * Get backup file name
     *
     * @return string
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
     */
    public function getIsSuccess()
    {
        return $this->_lastOperationSucceed;
    }

    /**
     * Get last error message
     *
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->_lastErrorMessage;
    }

    /**
     * Set error message
     *
     * @param string $errorMessage
     * @return string
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
     * @return Mage_Backup_Interface
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
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Get backup display name
     *
     * @return string
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
     */
    protected function _filterName($name)
    {
        $name = trim(preg_replace('/[^\da-zA-Z ]/', '', $name));
        $name = preg_replace('/\s{2,}/', ' ', $name);
        $name = str_replace(' ', '_', $name);

        return $name;
    }
}
