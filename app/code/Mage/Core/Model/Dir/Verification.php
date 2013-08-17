<?php
/**
 * The class, which verifies existence and write access to the needed application directories
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Core_Model_Dir_Verification
{
    /**
     * Codes of directories to create and verify in production mode
     *
     * @var array
     */
    protected static $_productionDirs = array(
        Mage_Core_Model_Dir::MEDIA,
        Mage_Core_Model_Dir::VAR_DIR,
        Mage_Core_Model_Dir::TMP,
        Mage_Core_Model_Dir::CACHE,
        Mage_Core_Model_Dir::LOG,
        Mage_Core_Model_Dir::SESSION,
    );

    /**
     * Codes of directories to create and verify in non-production mode
     *
     * @var array
     */
    protected static $_nonProductionDirs = array(
        Mage_Core_Model_Dir::MEDIA,
        Mage_Core_Model_Dir::STATIC_VIEW,
        Mage_Core_Model_Dir::VAR_DIR,
        Mage_Core_Model_Dir::TMP,
        Mage_Core_Model_Dir::CACHE,
        Mage_Core_Model_Dir::LOG,
        Mage_Core_Model_Dir::SESSION,
    );

    /**
     * @var Magento_Filesystem
     */
    protected $_filesystem;

    /**
     * @var Mage_Core_Model_Dir
     */
    protected $_dirs;

    /**
     * Cached list of directories to create and verify write access
     *
     * @var array
     */
    protected $_dirsToVerify = array();

    /**
     * Constructor - initialize object with required dependencies, determine application state
     *
     * @param Magento_Filesystem $filesystem
     * @param Mage_Core_Model_Dir $dirs
     * @param Mage_Core_Model_App_State $appState
     */
    public function __construct(
        Magento_Filesystem $filesystem,
        Mage_Core_Model_Dir $dirs,
        Mage_Core_Model_App_State $appState
    ) {
        $this->_filesystem = $filesystem;
        $this->_dirs = $dirs;
        $this->_dirsToVerify = $this->_getDirsToVerify($appState);
    }

    /**
     * Return list of directories, that must be verified according to the application mode
     *
     * @param Mage_Core_Model_App_State $appState
     * @return array
     */
    protected function _getDirsToVerify(Mage_Core_Model_App_State $appState)
    {
        $result = array();
        $codes = ($appState->getMode() == Mage_Core_Model_App_State::MODE_PRODUCTION)
            ? self::$_productionDirs
            : self::$_nonProductionDirs;
        foreach ($codes as $code) {
            $result[] = str_replace(DIRECTORY_SEPARATOR, '/', $this->_dirs->getDir($code));
        }
        return $result;
    }

    /**
     * Create the required directories, if they don't exist, and verify write access for existing directories
     */
    public function createAndVerifyDirectories()
    {
        $fails = array();
        foreach ($this->_dirsToVerify as $dir) {
            if ($this->_filesystem->isDirectory($dir)) {
                if (!$this->_filesystem->isWritable($dir)) {
                    $fails[] = $dir;
                }
            } else {
                try {
                    $this->_filesystem->createDirectory($dir);
                } catch (Magento_Filesystem_Exception $e) {
                    $fails[] = $dir;
                }
            }
        }

        if ($fails) {
            $dirList = implode(', ', $fails);
            throw new Magento_BootstrapException(
                "Cannot create or verify write access: {$dirList}"
            );
        }
    }
}
