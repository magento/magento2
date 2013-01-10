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
 * @package     Mage_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Configuration options storage and logic
 *
 * @category   Mage
 * @package    Mage_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Core_Model_Config_Options extends Varien_Object
{
    /**
     * Var directory
     *
     * @var string
     */
    const VAR_DIRECTORY = 'var';

    /**
     * Public directory
     *
     * @var string
     */
    const PUB_DIRECTORY = 'pub';

    /**
     * Flag cache for existing or already created directories
     *
     * @var array
     */
    protected $_dirExists = array();

    /**
     * Flag cache for existing or already created directories
     *
     * @var array
     */
    protected $_io;

    /**
     * Initialize default values of the options
     *
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        $this->_io = isset($data['io']) ? $data['io'] : new Varien_Io_File();
        unset ($data['io']);
        parent::__construct($data);
        $appRoot = isset($data['app_dir']) ? $data['app_dir'] : Mage::getRoot();
        $root   = dirname($appRoot);

        $this->_data['app_dir']     = $appRoot;
        $this->_data['base_dir']    = $root;
        $this->_data['code_dir']    = $appRoot . DIRECTORY_SEPARATOR . 'code';
        $this->_data['design_dir']  = $appRoot . DIRECTORY_SEPARATOR . 'design';
        $this->_data['etc_dir']     = $appRoot . DIRECTORY_SEPARATOR . 'etc';
        $this->_data['lib_dir']     = $root . DIRECTORY_SEPARATOR . 'lib';
        $this->_data['locale_dir']  = $appRoot . DIRECTORY_SEPARATOR . 'locale';
        $this->_data['pub_dir']     = $root . DIRECTORY_SEPARATOR . 'pub';
        $this->_data['js_dir']      = $this->_data['pub_dir'] . DIRECTORY_SEPARATOR . 'lib';
        $this->_data['media_dir']   = isset($data['media_dir'])
            ? $data['media_dir']
            : $this->_data['pub_dir'] . DIRECTORY_SEPARATOR . 'media';
        $this->_data['var_dir']     = $this->getVarDir();
        $this->_data['tmp_dir']     = $this->_data['var_dir'] . DIRECTORY_SEPARATOR . 'tmp';
        $this->_data['cache_dir']   = $this->_data['var_dir'] . DIRECTORY_SEPARATOR . 'cache';
        $this->_data['log_dir']     = $this->_data['var_dir'] . DIRECTORY_SEPARATOR . 'log';
        $this->_data['session_dir'] = $this->_data['var_dir'] . DIRECTORY_SEPARATOR . 'session';
        $this->_data['upload_dir']  = $this->_data['media_dir'] . DIRECTORY_SEPARATOR . 'upload';
        $this->_data['export_dir']  = $this->_data['var_dir'] . DIRECTORY_SEPARATOR . 'export';
    }

    /**
     * Directory getter that returm path to directory based on path
     *
     * @throws Mage_Core_Exception
     * @param string $type
     * @return string
     */
    public function getDir($type)
    {
        $method = 'get'.ucwords($type).'Dir';
        $dir = $this->$method();
        if (!$dir) {
            throw Mage::exception('Mage_Core', 'Invalid dir type requested: '.$type);
        }
        return $dir;
    }

    /**
     * Application folder paths getter
     *
     * @return string
     */
    public function getAppDir()
    {
        return $this->_data['app_dir'];
    }

    /**
     * Base folder paths getter
     *
     * @return string
     */
    public function getBaseDir()
    {
        return $this->_data['base_dir'];
    }

    /**
     * Code folder paths getter
     *
     * @return string
     */
    public function getCodeDir()
    {
        return $this->_data['code_dir'];
    }

    /**
     * Design folder paths getter
     *
     * @return string
     */
    public function getDesignDir()
    {
        return $this->_data['design_dir'];
    }

    /**
     * Configuration (etc) folder paths getter
     *
     * @return string
     */
    public function getEtcDir()
    {
        return $this->_data['etc_dir'];
    }

    /**
     * Libraries folder paths getter
     *
     * @return string
     */
    public function getLibDir()
    {
        return $this->_data['lib_dir'];
    }

    /**
     * Locale folder paths getter
     *
     * @return string
     */
    public function getLocaleDir()
    {
        return $this->_data['locale_dir'];
    }

    /**
     * Public folder paths getter
     *
     * @return string
     */
    public function getPubDir()
    {
        return $this->_data['pub_dir'];
    }

    /**
     * JS libraries folder paths getter
     *
     * @return string
     */
    public function getJsDir()
    {
        return $this->_data['js_dir'];
    }

    /**
     * Media folder paths getter
     *
     * @return string
     */
    public function getMediaDir()
    {
        return $this->_data['media_dir'];
    }

    /**
     * Var folder paths getter
     *
     * @return string
     * @throws Mage_Core_Exception
     */
    public function getVarDir()
    {
        $dir = isset($this->_data['var_dir']) ? $this->_data['var_dir']
            : $this->_data['base_dir'] . DIRECTORY_SEPARATOR . self::VAR_DIRECTORY;
        if (!$this->createDirIfNotExists($dir)) {
            throw new Mage_Core_Exception('Unable to find writable var_dir');
        }
        return $dir;
    }

    /**
     * Temporary folder paths getter
     *
     * @return string
     * @throws Mage_Core_Exception
     */
    public function getTmpDir()
    {
        $dir = $this->_data['tmp_dir'];
        if (!$this->createDirIfNotExists($dir)) {
            throw new Mage_Core_Exception('Unable to find writable tmp_dir');
        }
        return $dir;
    }

    /**
     * Cache folder paths getter
     *
     * @return string
     */
    public function getCacheDir()
    {
        $dir = $this->_data['cache_dir'];
        $this->createDirIfNotExists($dir);
        return $dir;
    }

    /**
     * Log folder paths getter
     *
     * @return string
     */
    public function getLogDir()
    {
        $dir = $this->_data['log_dir'];
        $this->createDirIfNotExists($dir);
        return $dir;
    }

    /**
     * Session folder paths getter
     *
     * @return string
     */
    public function getSessionDir()
    {
        $dir = $this->_data['session_dir'];
        $this->createDirIfNotExists($dir);
        return $dir;
    }

    /**
     * Files upload folder paths getter
     *
     * @return string
     */
    public function getUploadDir()
    {
        $dir = $this->_data['upload_dir'];
        $this->createDirIfNotExists($dir);
        return $dir;
    }

    /**
     * Export files folder paths getter
     *
     * @return string
     */
    public function getExportDir()
    {
        $dir = $this->_data['export_dir'];
        $this->createDirIfNotExists($dir);
        return $dir;
    }

    /**
     * Create writable directory if it not exists
     *
     * @param string
     * @return bool
     */
    public function createDirIfNotExists($dir)
    {
        if (!empty($this->_dirExists[$dir])) {
            return true;
        }
        try {
            $this->_io->checkAndCreateFolder($dir);
        } catch (Exception $e) {
            return false;
        }
        $this->_dirExists[$dir] = true;
        return true;
    }
}
