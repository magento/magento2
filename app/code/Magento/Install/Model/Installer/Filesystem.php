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
 * @package     Magento_Install
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Fylesystem installer
 *
 * @category   Magento
 * @package    Magento_Install
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Install\Model\Installer;

class Filesystem extends \Magento\Install\Model\Installer\AbstractInstaller
{
    /**#@+
     * @deprecated since 1.7.1.0
     */
    const MODE_WRITE = 'write';
    const MODE_READ  = 'read';
    /**#@- */

    /**
     * @var \Magento\Filesystem
     */
    protected $_filesystem;

    /**
     * Install Config
     *
     * @var \Magento\Install\Model\Config
     */
    protected $_installConfig;

    /**
     * @var \Magento\App\Dir
     */
    protected $_dir;

    /**
     * Application Root Directory
     *
     * @var string
     */
    protected $_appRootDir;

    /**
     * @param \Magento\Install\Model\Installer $installer
     * @param \Magento\Filesystem $filesystem
     * @param \Magento\Install\Model\Config $installConfig
     * @param \Magento\App\Dir $dir
     */
    public function __construct(
        \Magento\Install\Model\Installer $installer,
        \Magento\Filesystem $filesystem,
        \Magento\Install\Model\Config $installConfig,
        \Magento\App\Dir $dir
    ) {
        parent::__construct($installer);
        $this->_filesystem = $filesystem;
        $this->_installConfig = $installConfig;
    }

    /**
     * Check and prepare file system
     *
     */
    public function install()
    {
        if (!$this->_checkFilesystem()) {
            throw new \Exception();
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
        $config = $this->_installConfig->getWritableFullPathsForCheck();

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
     * @throws \Magento\Exception
     */
    protected function _checkPath($path, $recursive, $existence, $mode)
    {
        $appRootDir = $this->_dir->getDir('app');
        if (!is_readable($appRootDir)) {
            throw new \Magento\Exception("Application root directory '$appRootDir' is not readable.");
        }
        return $this->_checkFullPath(dirname($appRootDir) . $path, $recursive, $existence);
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
        $result = true;

        if ($recursive && $this->_filesystem->isDirectory($fullPath)) {
            $pathsToCheck = $this->_filesystem->getNestedKeys($fullPath);
            array_unshift($pathsToCheck, $fullPath);
        } else {
            $pathsToCheck = array($fullPath);
        }

        $skipFileNames = array('.svn', '.htaccess');
        foreach ($pathsToCheck as $pathToCheck) {
            if (in_array(basename($pathToCheck), $skipFileNames)) {
                continue;
            }

            if ($existence) {
                $setError = !$this->_filesystem->isWritable($fullPath);
            } else {
                $setError = $this->_filesystem->has($fullPath) && !$this->_filesystem->isWritable($fullPath);
            }

            if ($setError) {
                $this->_getInstaller()->getDataModel()->addError(
                    __('Path "%1" must be writable.', $pathToCheck)
                );
                $result = false;
            }
        }

        return $result;
    }
}
