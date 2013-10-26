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
 * @package     Magento_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Shell scripts abstract class
 *
 * @category    Magento
 * @package     Magento_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Core\Model;

abstract class AbstractShell
{
    /**
     * Raw arguments, that should be parsed
     *
     * @var array
     */
    protected $_rawArgs     = array();

    /**
     * Parsed input arguments
     *
     * @var array
     */
    protected $_args        = array();

    /**
     * Entry point - script filename that is executed
     *
     * @var string
     */
    protected $_entryPoint = null;

    /**
     * @var \Magento\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\App\Dir
     */
    protected $_dir;

    /**
     * Initializes application and parses input parameters
     *
     * @param \Magento\Filesystem $filesystem
     * @param string $entryPoint
     * @param \Magento\App\Dir $dir
     * @throws \Exception
     */
    public function __construct(\Magento\Filesystem $filesystem, $entryPoint, \Magento\App\Dir $dir)
    {
        if (isset($_SERVER['REQUEST_METHOD'])) {
            throw new \Exception('This script cannot be run from Browser. This is the shell script.');
        }

        $this->_filesystem = $filesystem;
        $this->_entryPoint = $entryPoint;
        $this->_dir = $dir;
        $this->_rawArgs = $_SERVER['argv'];
        $this->_applyPhpVariables();
        $this->_parseArgs();
    }

    /**
     * Sets raw arguments to be parsed
     *
     * @param array $args
     * @return \Magento\Core\Model\AbstractShell
     */
    public function setRawArgs($args)
    {
        $this->_rawArgs = $args;
        $this->_parseArgs();
        return $this;
    }


    /**
     * Gets Magento root path (with last directory separator)
     *
     * @return string
     */
    protected function _getRootPath()
    {
        return $this->_dir->getDir(\Magento\App\Dir::ROOT);
    }

    /**
     * Parses .htaccess file and apply php settings to shell script
     *
     * @return \Magento\Core\Model\AbstractShell
     */
    protected function _applyPhpVariables()
    {
        $htaccess = $this->_getRootPath() . '.htaccess';
        if ($this->_filesystem->isFile($htaccess)) {
            // parse htaccess file
            $data = $this->_filesystem->read($htaccess);
            $matches = array();
            preg_match_all('#^\s+?php_value\s+([a-z_]+)\s+(.+)$#siUm', $data, $matches, PREG_SET_ORDER);
            if ($matches) {
                foreach ($matches as $match) {
                    @ini_set($match[1], str_replace("\r", '', $match[2]));
                }
            }
            preg_match_all('#^\s+?php_flag\s+([a-z_]+)\s+(.+)$#siUm', $data, $matches, PREG_SET_ORDER);
            if ($matches) {
                foreach ($matches as $match) {
                    @ini_set($match[1], str_replace("\r", '', $match[2]));
                }
            }
        }
        return $this;
    }

    /**
     * Parses input arguments
     *
     * @return \Magento\Core\Model\AbstractShell
     */
    protected function _parseArgs()
    {
        $current = null;
        foreach ($this->_rawArgs as $arg) {
            $match = array();
            if (preg_match('#^--([\w\d_-]{1,})(=(.*))?$#', $arg, $match)
                || preg_match('#^-([\w\d_]{1,})$#', $arg, $match) ) {
                if (isset($match[3])) {
                    $this->_args[$match[1]] = $match[3];
                    $current = null;
                } else {
                    $current = $match[1];
                    $this->_args[$current] = true;
                }
            } else {
                if ($current) {
                    $this->_args[$current] = $arg;
                    $current = null;
                } else if (preg_match('#^([\w\d_]{1,})$#', $arg, $match)) {
                    $this->_args[$match[1]] = true;
                }
            }
        }
        return $this;
    }

    /**
     * Runs script
     *
     * @return \Magento\Core\Model\AbstractShell
     */
    abstract public function run();

    /**
     * Shows usage help, if requested
     *
     * @return bool
     */
    protected function _showHelp()
    {
        if (isset($this->_args['h']) || isset($this->_args['help'])) {
            echo $this->getUsageHelp();
            return true;
        }
        return false;
    }

    /**
     * Retrieves usage help message
     *
     * @return string
     */
    public function getUsageHelp()
    {
        return <<<USAGE
Usage:  php -f {$this->_entryPoint} -- [options]

  -h            Short alias for help
  help          This help
USAGE;
    }

    /**
     * Retrieves argument value by name. If argument is not found - returns FALSE.
     *
     * @param string $name the argument name
     * @return mixed
     */
    public function getArg($name)
    {
        if (isset($this->_args[$name])) {
            return $this->_args[$name];
        }
        return false;
    }
}
