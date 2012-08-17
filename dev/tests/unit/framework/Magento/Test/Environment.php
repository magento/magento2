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
 * @package     unit_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Environment management class
 */
class Magento_Test_Environment
{
    /**
     * @var Magento_Test_Environment
     */
    private static $_instance;

    /**
     * Temporary directory
     *
     * @var string
     */
    protected $_tmpDir;

    /**
     * Set self instance for static access
     *
     * @param Magento_Test_Environment $instance
     */
    public static function setInstance($instance)
    {
        if(!is_null($instance) && !($instance instanceof Magento_Test_Environment)) {
            throw new Magento_Exception("Instance Parameter must be an Instance of Magento_Test_Environtment");
        }

        self::$_instance = $instance;
    }

    /**
     * Self instance getter
     *
     * @return Magento_Test_Environment
     * @throws Magento_Exception
     */
    public static function getInstance()
    {
        if (!self::$_instance) {
            throw new Magento_Exception('Environment instance is not defined yet.');
        }
        return self::$_instance;
    }

    /**
     * Initialize instance
     *
     * @param string $tmpDir
     * @throws Magento_Exception
     */
    public function __construct($tmpDir)
    {
        $this->_tmpDir = $tmpDir;
        if (!is_writable($this->_tmpDir)) {
            throw new Magento_Exception($this->_tmpDir . ' must be writable.');
        }
    }

    /**
     * Return path to framework's temporary directory
     *
     * @return string
     */
    public function getTmpDir()
    {
        return $this->_tmpDir;
    }

    /**
     * Clean tmp directory
     *
     * @return Magento_Test_Environment
     */
    public function cleanTmpDir()
    {
        return $this->cleanDir($this->_tmpDir);
    }

    /**
     * Clean directory
     *
     * @param string $dir
     * @return Magento_Test_Environment
     */
    public function cleanDir($dir)
    {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($files as $file) {
            if (strpos($file->getFilename(), '.') === 0) {
                continue;
            }
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }

        return $this;
    }

    /**
     * Clean all files in temp dir
     *
     * @return Magento_Test_Environment
     */
    public function cleanTmpDirOnShutdown()
    {
        register_shutdown_function(array($this, 'cleanTmpDir'));
    }
}
