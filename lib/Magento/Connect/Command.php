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
 * @package     Magento_Connect
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Connect;

class Command
{
    /**
     * All commands list
     * @var array
     */
    protected static $_commandsAll = array();

    /**
     * Commands list hash (key=class)
     * @var array
     */
    protected static $_commandsByClass = array();

    /**
     * Frontend object
     * @var \Magento\Connect\Frontend
     */
    protected static $_frontend = null;

    /**
     * @var Config
     */
    protected static $_config = null;

    /**
     * @var mixed
     */
    protected static $_registry = null;

    /**
     * @var Validator
     */
    protected static $_validator = null;

    /**
     * @var Rest
     */
    protected static $_rest = null;

    /**
     * @var Singleconfig
     */
    protected static $_sconfig = null;

    /**
     * @var mixed
     */
    protected $_data;

    /**
     * String name of this class
     *
     * @var string
     */
    protected $_class;

    /**
     * @var \Magento\Connect\Packager
     */
    protected static $_packager = null;

    /**
     * @var array
     */
    protected static $_return = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        $class = $this->_class = get_class($this);
        if (__CLASS__ == $class) {
            throw new \Exception("You shouldn't instantiate {$class} directly!");
        }
        $this->commandsInfo = self::$_commandsByClass[$class];
    }

    /**
     * Get command info (static)
     * @param string $name command name
     * @return array|bool
     */
    public static function commandInfo($name)
    {
        $name = strtolower($name);
        if (!isset(self::$_commandsAll[$name])) {
            return false;
        }
        return self::$_commandsAll[$name];
    }

    /**
     * Get command info for current command object
     * @param string $name
     * @return array|bool
     */
    public function getCommandInfo($name)
    {
        if (!isset(self::$_commandsByClass[$this->_class][$name])) {
            return false;
        }
        return self::$_commandsByClass[$this->_class][$name];
    }

    /**
     * Run command
     * @param string $command
     * @param string $options
     * @param string $params
     * @throws \Exception If there's no needed method
     * @return mixed
     */
    public function run($command, $options, $params)
    {
        $data = $this->getCommandInfo($command);
        $method = $data['function'];
        if (!method_exists($this, $method)) {
            throw new \Exception("{$method} does't exist in class " . $this->_class);
        }
        return $this->{$method}($command, $options, $params);
    }

    /**
     * Static functions
     */

    /**
     * Static
     * @param string $commandName
     * @return object
     * @throws \UnexpectedValueException
     */
    public static function getInstance($commandName)
    {
        if (!isset(self::$_commandsAll[$commandName])) {
            throw new \UnexpectedValueException("Cannot find command {$commandName}");
        }
        $currentCommand = self::$_commandsAll[$commandName];
        return new $currentCommand['class']();
    }

    /**
     * @param Singleconfig $obj
     * @return void
     */
    public static function setSconfig($obj)
    {
        self::$_sconfig = $obj;
    }

    /**
     *
     * @return Singleconfig
     */
    public function getSconfig()
    {
        return self::$_sconfig;
    }

    /**
     * Sets frontend object for all commands
     *
     * @param \Magento\Connect\Frontend $obj
     * @return void
     */
    public static function setFrontendObject($obj)
    {
        self::$_frontend = $obj;
    }

    /**
     * Set config object for all commands
     *
     * @param Config $obj
     * @return void
     */
    public static function setConfigObject($obj)
    {
        self::$_config = $obj;
    }

    /**
     * Non-static getter for config
     *
     * @return Config
     */
    public function config()
    {
        return self::$_config;
    }

    /**
     * Non-static getter for UI
     * @return \Magento\Connect\Frontend
     */
    public function ui()
    {
        return self::$_frontend;
    }

    /**
     * Get validator object
     *
     * @return Validator
     */
    public function validator()
    {
        if (is_null(self::$_validator)) {
            self::$_validator = new Validator();
        }
        return self::$_validator;
    }

    /**
     * Get rest object
     *
     * @return Rest
     */
    public function rest()
    {
        if (is_null(self::$_rest)) {
            self::$_rest = new Rest(self::config()->protocol);
        }
        return self::$_rest;
    }

    /**
     * Get commands list sorted
     * @return array
     */
    public static function getCommands()
    {
        if (!count(self::$_commandsAll)) {
            self::registerCommands();
        }
        ksort(self::$_commandsAll);
        return self::$_commandsAll;
    }

    /**
     * Get Getopt args from command definitions
     * and parse them
     * @param string $command
     * @return array|void
     */
    public static function getGetoptArgs($command)
    {
        $commandInfo = self::commandInfo($command);
        $short_args = '';
        $long_args = array();
        if (empty($commandInfo) || empty($commandInfo['options'])) {
            return;
        }
        reset($commandInfo['options']);
        while (list($option, $info) = each($commandInfo['options'])) {
            $larg = $sarg = '';
            if (isset($info['arg'])) {
                if ($info['arg'][0] == '(') {
                    $larg = '==';
                    $sarg = '::';
                    $arg = substr($info['arg'], 1, -1);
                } else {
                    $larg = '=';
                    $sarg = ':';
                    $arg = $info['arg'];
                }
            }
            if (isset($info['shortopt'])) {
                $short_args .= $info['shortopt'] . $sarg;
            }
            $long_args[] = $option . $larg;
        }
        return array($short_args, $long_args);
    }

    /**
     * Try to register commands automatically
     * @return void
     */
    public static function registerCommands()
    {
        $pathCommands = __DIR__ . '/' . basename(__FILE__, ".php");
        $f = new \DirectoryIterator($pathCommands);
        foreach ($f as $file) {
            if (!$file->isFile()) {
                continue;
            }
            $pattern = preg_match("/(.*)_Header\.php/imsu", $file->getFilename(), $matches);
            if (!$pattern) {
                continue;
            }
            include $file->getPathname();
            if (!isset($commands)) {
                continue;
            }
            $class = __CLASS__ . "_" . $matches[1];
            foreach ($commands as $k => $v) {
                $commands[$k]['class'] = $class;
                self::$_commandsAll[$k] = $commands[$k];
            }
            self::$_commandsByClass[$class] = $commands;
        }
    }

    /**
     * @param string $command
     * @param string $message
     * @return void
     */
    public function doError($command, $message)
    {
        return $this->ui()->doError($command, $message);
    }

    /**
     * Set command return
     * @param string $key
     * @param mixed $val
     * @return void
     */
    public static function setReturn($key, $val)
    {
        self::$_return[$key] = $val;
    }

    /**
     * Get command return
     * @param string $key
     * @param bool $clear
     * @return array|null
     */
    public static function getReturn($key, $clear = true)
    {
        if (isset(self::$_return[$key])) {
            $out = self::$_return[$key];
            if ($clear) {
                unset(self::$_return[$key]);
            }
            return $out;
        }
        return null;
    }

    /**
     * Cleanup command params from empty strings
     *
     * @param array &$params by reference
     * @return void
     */
    public function cleanupParams(array &$params)
    {
        $newParams = array();
        if (!count($params)) {
            return;
        }
        foreach ($params as $k => $v) {
            if (is_string($v)) {
                $v = trim($v);
                if (!strlen($v)) {
                    continue;
                }
            }
            $newParams[] = $v;
        }
        $params = $newParams;
    }

    /**
     * Splits first command argument: channel/package
     * to two arguments if found in top of array
     *
     * @param array &$params
     * @return void
     */
    public function splitPackageArgs(array &$params)
    {
        if (!count($params) || !isset($params[0])) {
            return;
        }
        if ($this->validator()->validateUrl($params[0])) {
            return;
        }
        if (preg_match("@([a-zA-Z0-9_]+)/([a-zA-Z0-9_]+)@ims", $params[0], $subs)) {
            $params[0] = $subs[2];
            array_unshift($params, $subs[1]);
        }
    }

    /**
     * Get packager instance
     * @return \Magento\Connect\Packager
     */
    public function getPackager()
    {
        if (!self::$_packager) {
            self::$_packager = new \Magento\Connect\Packager();
        }
        return self::$_packager;
    }
}
