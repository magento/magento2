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
namespace Magento\Framework\App;

/**
 * Shell scripts abstract class
 */
abstract class AbstractShell
{
    /**
     * Raw arguments, that should be parsed
     *
     * @var string[]
     */
    protected $_rawArgs = array();

    /**
     * Parsed input arguments
     *
     * @var array
     */
    protected $_args = array();

    /**
     * Entry point - script filename that is executed
     *
     * @var string
     */
    protected $_entryPoint = null;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    protected $rootDirectory;

    /**
     * Initializes application and parses input parameters
     *
     * @param \Magento\Framework\App\Filesystem $filesystem
     * @param string $entryPoint
     * @throws \Exception
     */
    public function __construct(\Magento\Framework\App\Filesystem $filesystem, $entryPoint)
    {
        if (isset($_SERVER['REQUEST_METHOD'])) {
            throw new \Exception('This script cannot be run from Browser. This is the shell script.');
        }

        $this->rootDirectory = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem::ROOT_DIR);
        $this->_entryPoint = $entryPoint;
        $this->_rawArgs = $_SERVER['argv'];
        $this->_applyPhpVariables();
        $this->_parseArgs();
    }

    /**
     * Sets raw arguments to be parsed
     *
     * @param string[] $args
     * @return $this
     */
    public function setRawArgs($args)
    {
        $this->_rawArgs = $args;
        $this->_parseArgs();
        return $this;
    }

    /**
     * Parses .htaccess file and apply php settings to shell script
     *
     * @return $this
     */
    protected function _applyPhpVariables()
    {
        $htaccess = '.htaccess';
        if ($this->rootDirectory->isFile($htaccess)) {
            // parse htaccess file
            $data = $this->rootDirectory->readFile($htaccess);
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
     * @return $this
     */
    protected function _parseArgs()
    {
        $current = null;
        foreach ($this->_rawArgs as $arg) {
            $match = array();
            if (preg_match(
                '#^--([\w\d_-]{1,})(=(.*))?$#',
                $arg,
                $match
            ) || preg_match(
                '#^-([\w\d_]{1,})$#',
                $arg,
                $match
            )
            ) {
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
     * @return $this
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
