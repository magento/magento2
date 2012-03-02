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
 * @category    Magento
 * @package     Magento
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Tests entry point. Implements application installation, initialization and uninstall
 */
class Magento_Test_Bootstrap
{
    /**
     * Name for DB backups, used by bootstrap
     */
    const DB_BACKUP_NAME = 'bootstrap_backup';

    /**
     * CLEANUP_* constants represent allowed cleanup actions
     */
    const CLEANUP_NONE       = '';
    const CLEANUP_UNINSTALL  = 'uninstall';
    const CLEANUP_RESTORE_DB = 'restoreDatabase';

    /**
     * @var Magento_Test_Bootstrap
     */
    private static $_instance;

    /**
     * Filename of an existing local.xml configuration file
     *
     * @var string
     */
    protected $_localXmlFile;

    /**
     * @var SimpleXMLElement
     */
    protected $_localXml;

    /**
     * Root directory of the Magento source code
     *
     * @var string
     */
    protected $_magentoDir;

    /**
     * Application *.xml configuration files
     *
     * @var array
     */
    protected $_globalEtcFiles;

    /**
     * Module declaration *.xml configuration files
     *
     * @var array
     */
    protected $_moduleEtcFiles;

    /**
     * Installation destination directory
     *
     * @var string
     */
    protected $_installDir;

    /**
     * Installation destination directory with configuration files
     *
     * @var string
     */
    protected $_installEtcDir;

    /**
     * Application initialization options
     *
     * @var array
     */
    protected $_options = array();

    /**
     * DB vendor name
     *
     * @var string
     */
    protected $_dbVendorName = '';

    /**
     * DB vendor adapter instance
     *
     * @var Magento_Test_Db_DbAbstract
     */
    protected $_db = null;

    /**
     * Cleanup action represented by CLEANUP_* constants
     *
     * @var string
     */
    protected $_cleanupAction;

    /**
     * Developer mode flag
     *
     * @var bool
     */
    protected $_developerMode = false;

    /**
     * Set self instance for static access
     *
     * @param Magento_Test_Bootstrap $instance
     */
    public static function setInstance(Magento_Test_Bootstrap $instance)
    {
        self::$_instance = $instance;
    }

    /**
     * Self instance getter
     *
     * @return Magento_Test_Bootstrap
     * @throws Magento_Exception
     */
    public static function getInstance()
    {
        if (!self::$_instance) {
            throw new Magento_Exception('Bootstrap instance is not defined yet.');
        }
        return self::$_instance;
    }

    /**
     * Check the possibility to send headers or to use headers related function (like set_cookie)
     *
     * @return bool
     */
    public static function canTestHeaders()
    {
        if (!headers_sent() && extension_loaded('xdebug') && function_exists('xdebug_get_headers')) {
            return true;
        }
        return false;
    }

    /**
     * Initialize DB configuration, db vendor and install dir
     *
     * @param string $magentoDir
     * @param string $localXmlFile
     * @param string $globalEtcFiles
     * @param string $moduleEtcFiles
     * @param string $tmpDir
     * @param string $cleanupAction
     * @param bool $developerMode
     * @throws Magento_Exception
     */
    public function __construct(
        $magentoDir, $localXmlFile, $globalEtcFiles, $moduleEtcFiles, $tmpDir, $cleanupAction = self::CLEANUP_NONE,
        $developerMode = false
    ) {
        if (!in_array($cleanupAction, array(self::CLEANUP_NONE, self::CLEANUP_UNINSTALL, self::CLEANUP_RESTORE_DB))) {
            throw new Magento_Exception("Cleanup action '{$cleanupAction}' is not supported.");
        }

        $this->_magentoDir = $magentoDir;
        $this->_localXmlFile = $localXmlFile;
        $this->_globalEtcFiles = $this->_exposeFiles($globalEtcFiles);
        $this->_moduleEtcFiles = $this->_exposeFiles($moduleEtcFiles);

        $this->_readLocalXml();

        $this->_verifyDirectories($tmpDir);

        $sandboxUniqueId = md5(sha1_file($this->_localXmlFile) . '_' . $globalEtcFiles . '_' . $moduleEtcFiles);
        $this->_installDir = "{$tmpDir}/sandbox-{$this->_dbVendorName}-{$sandboxUniqueId}";
        $this->_installEtcDir = $this->_installDir . '/etc';

        $this->_db = $this->_instantiateDb();

        $this->_cleanupAction = $cleanupAction;
        $this->_cleanup();
        $this->_ensureDirExists($this->_installDir);

        $this->_developerMode = $developerMode;

        $this->_emulateEnvironment();

        if ($this->_isInstalled()) {
            $this->initialize();
        } else {
            $this->_db->verifyEmptyDatabase();
            $this->_install();
        }
    }

    /**
     * Get DB vendor name
     *
     * @return string
     */
    public function getDbVendorName()
    {
        return $this->_dbVendorName;
    }

    /**
     * Initialize an already installed Magento application
     *
     * @param string $scopeCode
     * @param string $scopeType
     */
    public function initialize($scopeCode = '', $scopeType = 'store')
    {
        if (!class_exists('Mage', false)) {
            require_once $this->_magentoDir . '/app/bootstrap.php';
        } else {
            $resource = Mage::registry('_singleton/Mage_Core_Model_Resource');
            Mage::reset();
            if ($resource) {
                Mage::register('_singleton/Mage_Core_Model_Resource', $resource);
            }
        }
        $this->_options = array(
            'etc_dir'     => $this->_installEtcDir,
            'var_dir'     => $this->_installDir,
            'tmp_dir'     => $this->_installDir . DIRECTORY_SEPARATOR . 'tmp',
            'cache_dir'   => $this->_installDir . DIRECTORY_SEPARATOR . 'cache',
            'log_dir'     => $this->_installDir . DIRECTORY_SEPARATOR . 'log',
            'session_dir' => $this->_installDir . DIRECTORY_SEPARATOR . 'session',
            'media_dir'   => $this->_installDir . DIRECTORY_SEPARATOR . 'media',
            'upload_dir'  => $this->_installDir . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'upload',
        );

        Mage::setIsDeveloperMode($this->_developerMode);
        Mage::app($scopeCode, $scopeType, $this->_options);
    }

    /**
     * Remove cached configuration and reinitialize the application
     */
    public function refreshConfiguration()
    {
        Mage::app()->cleanCache(array(Mage_Core_Model_Config::CACHE_TAG));
        $this->initialize();
    }

    /**
     * Re-create empty temporary dir by specified
     *
     * @param string $optionCode
     * @throws Magento_Exception if one of protected directories specified
     */
    public function cleanupDir($optionCode)
    {
        if (in_array($optionCode, array('etc_dir', 'var_dir', 'media_dir'))) {
            throw new Magento_Exception("Directory '{$optionCode}' must not be cleaned up while running tests.");
        }
        $dir = $this->_options[$optionCode];
        $this->_removeDirectory($dir, false);
    }

    /**
     * Get application initialization options
     *
     * @return array
     */
    public function getAppOptions()
    {
        return $this->_options;
    }

    /**
     * Perform requested cleanup operations
     */
    public function __destruct()
    {
        $this->_cleanup();
    }

    /**
     * Perform a cleanup action
     */
    protected function _cleanup()
    {
        switch ($this->_cleanupAction) {
            case self::CLEANUP_UNINSTALL:
                $this->_db->cleanup();
                $this->_cleanupFilesystem();
                break;
            case self::CLEANUP_RESTORE_DB:
                $this->_db->restoreBackup(self::DB_BACKUP_NAME);
                break;
        }
    }

    /**
     * Load application local.xml file, determine database vendor name
     *
     * @throws Magento_Exception
     */
    protected function _readLocalXml()
    {
        if (!is_file($this->_localXmlFile)) {
            throw new Magento_Exception("Local XML configuration file '{$this->_localXmlFile}' does not exist.");
        }
        $this->_localXml = simplexml_load_file($this->_localXmlFile);
        $dbVendorId = (string)$this->_localXml->global->resources->default_setup->connection->model;
        $dbVendorMap = array('mysql4' => 'mysql', 'mssql' => 'mssql', 'oracle' => 'oracle');
        if (!array_key_exists($dbVendorId, $dbVendorMap)) {
            throw new Magento_Exception("Database vendor '{$dbVendorId}' is not supported.");
        }
        $this->_dbVendorName = $dbVendorMap[$dbVendorId];
    }

    /**
     * Check all required directories contents and permissions
     *
     * @param string $tmpDir
     * @throws Magento_Exception when any of required directories is not eligible
     */
    protected function _verifyDirectories($tmpDir)
    {
        /* Magento application dir */
        if (!is_file($this->_magentoDir . '/app/bootstrap.php')) {
            throw new Magento_Exception('Unable to locate Magento root folder and bootstrap.php.');
        }
        /* Temporary directory */
        if (!is_dir($tmpDir) || !is_writable($tmpDir)) {
            throw new Magento_Exception("The '{$tmpDir}' is not a directory or not writable.");
        }
    }

    /**
     * Create object of configured DB vendor adapter
     *
     * @return Magento_Test_Db_DbAbstract
     */
    protected function _instantiateDb()
    {
        $suffix = ucfirst($this->_dbVendorName);
        require_once __DIR__ . '/Db/DbAbstract.php';
        require_once __DIR__ . "/Db/{$suffix}.php";
        $class = "Magento_Test_Db_{$suffix}";
        $dbConfig = $this->_localXml->global->resources->default_setup->connection;
        $this->_ensureDirExists($this->_installDir);
        return new $class(
            (string)$dbConfig->host,
            (string)$dbConfig->username,
            (string)$dbConfig->password,
            (string)$dbConfig->dbname,
            $this->_installDir
        );
    }

    /**
     * Weather the application is installed or not
     *
     * @return bool
     */
    protected function _isInstalled()
    {
        return is_file($this->_installEtcDir . '/local.xml');
    }

    /**
     * Set environment variables or apply workarounds, so that they would be closer to real application
     */
    protected function _emulateEnvironment()
    {
        // emulate entry point to ensure that tests generate invariant URLs
        $_SERVER['SCRIPT_FILENAME'] = 'index.php';
        // prevent session_start, because it may rely on cookies
        $_SESSION = array();
        // application relies on a non-empty session ID
        session_id(uniqid());
    }

    /**
     * Create a directory with write permissions or don't touch existing one
     *
     * @throws Magento_Exception
     * @param string $dir
     */
    protected function _ensureDirExists($dir)
    {
        if (!file_exists($dir)) {
            $old = umask(0);
            mkdir($dir, 0777);
            umask($old);
        } else if (!is_dir($dir)) {
            throw new Magento_Exception("'$dir' is not a directory.");
        }
    }

    /**
     * Remove entire directory from the file system
     *
     * @param string $dir
     * @param bool $removeDirItself Whether to remove directory itself along with all its children
     */
    protected function _removeDirectory($dir, $removeDirItself = true)
    {
        foreach (scandir($dir) as $dirOrFile) {
            if ($dirOrFile == '.' || $dirOrFile == '..') {
                continue;
            }
            $dirOrFile = $dir . DIRECTORY_SEPARATOR . $dirOrFile;
            if (is_dir($dirOrFile)) {
                $this->_removeDirectory($dirOrFile);
            } else {
                unlink($dirOrFile);
            }
        }
        if ($removeDirItself) {
            rmdir($dir);
        }
    }

    /**
     * Install application using temporary directory and vendor-specific database settings
     *
     * @throws Magento_Exception
     */
    protected function _install()
    {
        $this->_ensureDirExists($this->_installDir);
        $this->_ensureDirExists($this->_installEtcDir);
        $this->_ensureDirExists($this->_installDir . DIRECTORY_SEPARATOR . 'media');
        $this->_ensureDirExists($this->_installDir . DIRECTORY_SEPARATOR . 'skin');

        /* Copy *.xml configuration files */
        $dirs = array(
            $this->_installEtcDir              => $this->_globalEtcFiles,
            $this->_installEtcDir . '/modules' => $this->_moduleEtcFiles,
        );
        foreach ($dirs as $targetEtcDir => $sourceEtcFiles) {
            $this->_ensureDirExists($targetEtcDir);
            foreach ($sourceEtcFiles as $sourceEtcFile) {
                $targetEtcFile = $targetEtcDir . '/' . basename($sourceEtcFile);
                copy($sourceEtcFile, $targetEtcFile);
            }
        }

        /* Make sure that local.xml contains an invalid installation date */
        $installDate = (string)$this->_localXml->global->install->date;
        if ($installDate && strtotime($installDate)) {
            throw new Magento_Exception(
                "Configuration file '$this->_localXmlFile' must contain an invalid installation date."
            );
        }

        /* Replace local.xml */
        $targetLocalXml = $this->_installEtcDir . '/local.xml';
        copy($this->_localXmlFile, $targetLocalXml);

        /* Initialize an application in non-installed mode */
        $this->initialize();

        /* Run all install and data-install scripts */
        Mage_Core_Model_Resource_Setup::applyAllUpdates();
        Mage_Core_Model_Resource_Setup::applyAllDataUpdates();

        /* Enable configuration cache by default in order to improve tests performance */
        Mage::app()->getCacheInstance()->saveOptions(array('config' => 1));

        /* Fill installation date in local.xml to indicate that application is installed */
        $localXml = file_get_contents($targetLocalXml);
        $localXml = str_replace($installDate, date('r'), $localXml, $replacementCount);
        if ($replacementCount != 1) {
            throw new Magento_Exception("Unable to replace installation date properly in '$targetLocalXml' file.");
        }
        file_put_contents($targetLocalXml, $localXml, LOCK_EX);

        /* Make a database backup to be able to restore it to initial state any time */
        $this->_db->createBackup(self::DB_BACKUP_NAME);

        /* Switch an application to installed mode */
        $this->initialize();
        /**
         * Initialization of front controller with all routers.
         * Should be here as needed only once after installation process. 
         */
        Mage::app()->getFrontController();
    }

    /**
     * Remove temporary files and directories from the filesystem
     */
    protected function _cleanupFilesystem()
    {
        $this->_removeDirectory($this->_installDir);
    }

    /**
     * Expose provided pattern to the real files
     *
     * @param string $pattern
     * @return array
     */
    protected function _exposeFiles($pattern)
    {
        $result = array();
        $allPatterns = preg_split('/\s*;\s*/', trim($pattern), -1, PREG_SPLIT_NO_EMPTY);
        foreach ($allPatterns as $onePattern) {
            $onePattern = __DIR__ . '/../../../' . $onePattern;
            $files = glob($onePattern, GLOB_BRACE);
            $result = array_merge($result, $files);
        }
        return $result;
    }

    /**
     * Removes cache polluted by other tests. Leaves performance critical cache (configuration, ddl) untouched.
     *
     * @return null
     */
    public function cleanupCache()
    {
        Mage::app()->getCache()->clean(
            Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG,
            array(Mage_Core_Model_Config::CACHE_TAG,
                Varien_Db_Adapter_Pdo_Mysql::DDL_CACHE_TAG,
                'DB_PDO_MSSQL_DDL', // Varien_Db_Adapter_Pdo_Mssql::DDL_CACHE_TAG
                'DB_ORACLE_DDL', // Varien_Db_Adapter_Oracle::DDL_CACHE_TAG
            )
        );
    }
}
