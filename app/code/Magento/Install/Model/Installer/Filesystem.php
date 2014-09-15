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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Install\Model\Installer;

/**
 * Filesystem installer
 */
class Filesystem
{
    /**#@+
     * @deprecated since 1.7.1.0
     */
    const MODE_WRITE = 'write';

    const MODE_READ = 'read';

    /**#@- */

    /**
     * @var \Magento\Framework\App\Filesystem
     */
    protected $_filesystem;

    /**
     * Install Config
     *
     * @var \Magento\Install\Model\Config
     */
    protected $_installConfig;

    /**
     * Application Root Directory
     *
     * @var string
     */
    protected $_appRootDir;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @param \Magento\Framework\App\Filesystem $filesystem
     * @param \Magento\Install\Model\Config $installConfig
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Framework\App\Filesystem $filesystem,
        \Magento\Install\Model\Config $installConfig,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->_filesystem = $filesystem;
        $this->_installConfig = $installConfig;
        $this->messageManager = $messageManager;
    }

    /**
     * Check and prepare file system
     *
     * @return $this
     * @throws \Exception
     */
    public function install()
    {
        if (!$this->_checkFilesystem()) {
            throw new \Exception();
        }
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
        $directory = $this->_filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem::ROOT_DIR);
        $path = $directory->getRelativePath($fullPath);
        if ($recursive && $directory->isDirectory($path)) {
            $pathsToCheck = $directory->read($path);
            array_unshift($pathsToCheck, $path);
        } else {
            $pathsToCheck = array($path);
        }

        $skipFileNames = array('.svn', '.htaccess');
        foreach ($pathsToCheck as $pathToCheck) {
            if (in_array(basename($pathToCheck), $skipFileNames)) {
                continue;
            }

            if ($existence) {
                $setError = !$directory->isWritable($path);
            } else {
                $setError = $directory->isExist($path) && !$directory->isWritable($path);
            }

            if ($setError) {
                $this->messageManager->addError(__('Path "%1" must be writable.', $pathToCheck));
                $result = false;
            }
        }

        return $result;
    }
}
