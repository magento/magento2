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
 * @package     Mage_Install
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Fylesystem installer
 *
 * @category   Mage
 * @package    Mage_Install
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Install_Model_Installer_Filesystem extends Mage_Install_Model_Installer_Abstract
{
    /**#@+
     * @deprecated since 1.7.1.0
     */
    const MODE_WRITE = 'write';
    const MODE_READ  = 'read';
    /**#@- */

    public function __construct()
    {
    }

    /**
     * Check and prepare file system
     *
     */
    public function install()
    {
        if (!$this->_checkFilesystem()) {
            throw new Exception();
        };
        return $this;
    }

    /**
     * Check file system by config
     *
     * @return bool
     */
    protected function _checkFilesystem()
    {
        $res = true;
        $config = Mage::getSingleton('Mage_Install_Model_Config')->getWritableFullPathsForCheck();

        if (is_array($config)) {
            foreach ($config as $item) {
                $recursive = isset($item['recursive']) ? (bool)$item['recursive'] : false;
                $existence = isset($item['existence']) ? (bool)$item['existence'] : false;
                $checkRes = $this->_checkFullPath($item['path'], $recursive, $existence);
                $res = $res && $checkRes;
            }
        }
        return $res;
    }

    /**
     * Check file system path
     *
     * @deprecated since 1.7.1.0
     * @param   string $path
     * @param   bool $recursive
     * @param   bool $existence
     * @param   string $mode
     * @return  bool
     */
    protected function _checkPath($path, $recursive, $existence, $mode)
    {
        return $this->_checkFullPath(dirname(Mage::getRoot()) . $path, $recursive, $existence);
    }

    /**
     * Check file system full path
     *
     * @param  string $fullPath
     * @param  bool $recursive
     * @param  bool $existence
     * @return bool
     */
    protected function _checkFullPath($fullPath, $recursive, $existence)
    {
        $res = true;
        $setError = $existence && (is_dir($fullPath) && !is_dir_writeable($fullPath) || !is_writable($fullPath))
            || !$existence && file_exists($fullPath) && !is_writable($fullPath);

        if ($setError) {
            $this->_getInstaller()->getDataModel()->addError(
                Mage::helper('Mage_Install_Helper_Data')->__('Path "%s" must be writable.', $fullPath)
            );
            $res = false;
        }

        if ($recursive && is_dir($fullPath)) {
            $skipFileNames = array('.svn', '.htaccess');
            foreach (new DirectoryIterator($fullPath) as $file) {
                $fileName = $file->getFilename();
                if (!$file->isDot() && !in_array($fileName, $skipFileNames)) {
                    $res = $this->_checkFullPath($fullPath . DS . $fileName, $recursive, $existence) && $res;
                }
            }
        }
        return $res;
    }
}
