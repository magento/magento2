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
namespace Magento\Downloader;


error_reporting(E_ALL & ~E_NOTICE);

// add Magento lib in include_path if needed
$_includePath = get_include_path();
$_libDir = dirname(__DIR__) . '/lib';
if (strpos($_includePath, $_libDir) === false) {
    if (substr($_includePath, 0, 2) === '.' . PATH_SEPARATOR) {
        $_includePath = '.' . PATH_SEPARATOR . $_libDir . PATH_SEPARATOR . substr($_includePath, 2);
    } else {
        $_includePath = $_libDir . PATH_SEPARATOR . $_includePath;
    }
    set_include_path($_includePath);
}
/**
 * Class for connect
 *
 * @category   Magento
 * @package    Magento_Connect
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Connect
{
    /**
     * Object of config
     *
     * @var \Magento\Connect\Config
     */
    protected $_config;

    /**
     * Object of single config
     *
     * @var \Magento\Connect\Singleconfig
     */
    protected $_sconfig;

    /**
     * Object of frontend
     *
     * @var \Magento\Connect\Frontend
     */
    protected $_frontend;

    /**
     * Internal cache for command objects
     *
     * @var array
     */
    protected $_cmdCache = array();

    /**
     * Console Started flag
     *
     * @var boolean
     */
    protected $_consoleStarted = false;

    /**
     * Instance of class
     *
     * @var \Magento\Downloader\Connect
     */
    protected static $_instance;

    /**
     * Constructor loads Config, Cache Config and initializes Frontend
     */
    public function __construct()
    {
        $this->getConfig();
        $this->getSingleConfig();
        $this->getFrontend();
    }

    /**
     * Destructor, sends Console footer if Console started
     */
    public function __destruct()
    {
        if ($this->_consoleStarted) {
            $this->_consoleFooter();
        }
    }

    /**
     * Initialize instance
     *
     * @return \Magento\Downloader\Connect
     */
    public static function getInstance()
    {
        if (!self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Retrieve object of config and set it to \Magento\Connect\Command
     *
     * @return \Magento\Connect\Config
     */
    public function getConfig()
    {
        if (!$this->_config) {
            $this->_config = new \Magento\Connect\Config();
            $ftp = $this->_config->__get('remote_config');
            if (!empty($ftp)) {
                $packager = new \Magento\Connect\Packager();
                list($cache, $config, $ftpObj) = $packager->getRemoteConf($ftp);
                $this->_config = $config;
                $this->_sconfig = $cache;
            }
            $this->_config->magento_root = dirname(__DIR__) . '/..';
            \Magento\Connect\Command::setConfigObject($this->_config);
        }
        return $this->_config;
    }

    /**
     * Retrieve object of single config and set it to \Magento\Connect\Command
     *
     * @param bool $reload
     * @return \Magento\Connect\Singleconfig
     */
    public function getSingleConfig($reload = false)
    {
        if (!$this->_sconfig || $reload) {
            $this->_sconfig = new \Magento\Connect\Singleconfig(
                $this->getConfig()->magento_root .
                '/' .
                $this->getConfig()->downloader_path .
                '/' .
                \Magento\Connect\Singleconfig::DEFAULT_SCONFIG_FILENAME
            );
        }
        \Magento\Connect\Command::setSconfig($this->_sconfig);
        return $this->_sconfig;
    }

    /**
     * Retrieve object of frontend and set it to \Magento\Connect\Command
     *
     * @return \Magento\Downloader\Connect\Frontend
     */
    public function getFrontend()
    {
        if (!$this->_frontend) {
            $this->_frontend = new \Magento\Downloader\Connect\Frontend();
            \Magento\Connect\Command::setFrontendObject($this->_frontend);
        }
        return $this->_frontend;
    }

    /**
     * Retrieve lof from frontend
     *
     * @return array
     */
    public function getLog()
    {
        return $this->getFrontend()->getLog();
    }

    /**
     * Retrieve output from frontend
     *
     * @return array
     */
    public function getOutput()
    {
        return $this->getFrontend()->getOutput();
    }

    /**
     * Clean registry
     *
     * @return \Magento\Downloader\Connect
     */
    public function cleanSconfig()
    {
        $this->getSingleConfig()->clear();
        return $this;
    }

    /**
     * Delete directory recursively
     *
     * @param string $path
     * @return \Magento\Downloader\Connect
     */
    public function delTree($path)
    {
        if (@is_dir($path)) {
            $entries = @scandir($path);
            foreach ($entries as $entry) {
                if ($entry != '.' && $entry != '..') {
                    $this->delTree($path . '/' . $entry);
                }
            }
            @rmdir($path);
        } else {
            @unlink($path);
        }
        return $this;
    }

    /**
     * Run commands from \Magento\Connect\Command
     *
     * @param string $command
     * @param array $options
     * @param array $params
     * @return boolean|\Magento\Connect\Error
     */
    public function run($command, $options = array(), $params = array())
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '2048M');

        if (empty($this->_cmdCache[$command])) {
            \Magento\Connect\Command::getCommands();
            /**
             * @var $cmd \Magento\Connect\Command
             */
            $cmd = \Magento\Connect\Command::getInstance($command);
            if ($cmd instanceof \Magento\Connect\Error) {
                return $cmd;
            }
            $this->_cmdCache[$command] = $cmd;
        } else {
            /**
             * @var $cmd \Magento\Connect\Command
             */
            $cmd = $this->_cmdCache[$command];
        }
        $ftp = $this->getConfig()->remote_config;
        if (strlen($ftp) > 0) {
            $options = array_merge($options, array('ftp' => $ftp));
        }
        $cmd->run($command, $options, $params);
        if ($cmd->ui()->hasErrors()) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Set remote Config by URI
     *
     * @param string $uri
     * @return \Magento\Downloader\Connect
     */
    public function setRemoteConfig($uri)
    {
        $this->getConfig()->remote_config = $uri;
        return $this;
    }

    /**
     * Show Errors
     *
     * @param array $errors Error messages
     * @return \Magento\Downloader\Connect
     */
    public function showConnectErrors($errors)
    {
        echo '<script type="text/javascript">';
        $run = new \Magento\Downloader\Model\Connect\Request();
        if ($callback = $run->get('failure_callback')) {
            if (is_array($callback)) {
                call_user_func_array($callback, array($errors));
            } else {
                echo $callback;
            }
        }
        echo '</script>';

        return $this;
    }

    /**
     * Run \Magento\Connect\Command with html output console style
     *
     * @throws \Magento\Downloader\Exception
     * @param array|string|\Magento\Downloader\Model $runParams command, options, params, comment, success_callback, failure_callback
     * @return bool|\Magento\Connect\Error
     */
    public function runHtmlConsole($runParams)
    {
        if (function_exists('apache_setenv')) {
            apache_setenv('no-gzip', '1');
        }
        @ini_set('zlib.output_compression', 0);
        @ini_set('implicit_flush', 1);
        for ($i = 0; $i < ob_get_level(); $i++) {
            ob_end_flush();
        }
        ob_implicit_flush();

        $fe = $this->getFrontend();
        $oldLogStream = $fe->getLogStream();
        $fe->setLogStream('stdout');

        if ($runParams instanceof \Magento\Downloader\Model) {
            $run = $runParams;
        } elseif (is_array($runParams)) {
            $run = new \Magento\Downloader\Model\Connect\Request($runParams);
        } elseif (is_string($runParams)) {
            $run = new \Magento\Downloader\Model\Connect\Request(array('comment' => $runParams));
        } else {
            throw \Magento\Downloader\Exception("Invalid run parameters");
        }

        if (!$run->get('no-header')) {
            $this->_consoleHeader();
        }
        echo htmlspecialchars($run->get('comment')) . '<br/>';

        if ($command = $run->get('command')) {
            $result = $this->run($command, $run->get('options'), $run->get('params'));

            if ($this->getFrontend()->hasErrors()) {
                echo "<br/>CONNECT ERROR: ";
                foreach ($this->getFrontend()->getErrors(false) as $error) {
                    echo nl2br($error[1]);
                    echo '<br/>';
                }
            }
            echo '<script type="text/javascript">';
            if ($this->getFrontend()->hasErrors()) {
                if ($callback = $run->get('failure_callback')) {
                    if (is_array($callback)) {
                        call_user_func_array($callback, array($result));
                    } else {
                        echo $callback;
                    }
                }
            } else {
                if (!$run->get('no-footer')) {
                    if ($callback = $run->get('success_callback')) {
                        if (is_array($callback)) {
                            call_user_func_array($callback, array($result));
                        } else {
                            echo $callback;
                        }
                    }
                }
            }
            echo '</script>';
        } else {
            $result = false;
        }
        if ($this->getFrontend()->getErrors() || !$run->get('no-footer')) {
            //$this->_consoleFooter();
            $fe->setLogStream($oldLogStream);
        }
        return $result;
    }

    /**
     * Show HTML Console Header
     *
     * @return void
     */
    protected function _consoleHeader()
    {
        if (!$this->_consoleStarted) {
            ?>
            <html><head><style type="text/css">
            body { margin:0px;
                padding:3px;
                background:black;
                color:#2EC029;
                font:normal 11px Lucida Console, Courier New, serif;
                }
            </style></head><body>
            <script type="text/javascript">
            if (parent && parent.disableInputs) {
                parent.disableInputs(true);
            }
            if (typeof auto_scroll=='undefined') {
                var auto_scroll = window.setInterval(console_scroll, 10);
            }
            function console_scroll()
            {
                if (typeof top.$ != 'function') {
                    return;
                }
                if (top.$('connect_iframe_scroll').checked) {
                    document.body.scrollTop+=3;
                }
            }
            function show_message(message, newline)
            {
                var bodyElement = document.getElementsByTagName('body')[0];
                if (typeof newline == 'undefined') {
                    newline = true
                }
                if (newline) {
                    bodyElement.innerHTML += '<br/>';
                }
                bodyElement.innerHTML += message;
            }
            function clear_cache(callbacks)
            {
                if (typeof top.Ajax != 'object') {
                    return;
                }
                var message = 'Exception during cache and session cleaning';
                var url = window.location.href.split('?')[0] + '?A=cleanCache';
                var intervalID = setInterval(function() {show_message('.', false); }, 500);
                var clean = 0;
                var maintenance = 0;
                if (window.location.href.indexOf('clean_sessions') >= 0) {
                    clean = 1;
                }
                if (window.location.href.indexOf('maintenance') >= 0) {
                    maintenance = 1;
                }

                new top.Ajax.Request(url, {
                    method: 'post',
                    parameters: {clean_sessions:clean, maintenance:maintenance},
                    onCreate: function() {
                        show_message('Cleaning cache');
                        show_message('');
                    },
                    onSuccess: function(transport, json) {
                        var result = true;
                        try{
                            var response = eval('(' + transport.responseText + ')');
                            if (typeof response.result != 'undefined') {
                                result = response.result;
                            } else {
                                result = false;
                            }
                            if (typeof response.message != 'undefined') {
                                if (response.message.length > 0) {
                                    message = response.message;
                                } else {
                                    message = 'Cache cleaned successfully';
                                }
                            }
                        } catch (ex){
                            result = false;
                        }
                        if (result) {
                            callbacks.success();
                        } else {
                            callbacks.fail();
                        }
                    },
                    onFailure: function() {
                        callbacks.fail();
                    },
                    onComplete: function(transport) {
                        clearInterval(intervalID);
                        show_message(message);
                    }
                });
            }
            </script>
            <?php
            $this->_consoleStarted = true;
        }
    }

    /**
     * Show HTML Console Footer
     *
     * @return void
     */
    protected function _consoleFooter()
    {
        if ($this->_consoleStarted) {
            ?>
            <script type="text/javascript">
            if (parent && parent.disableInputs) {
                parent.disableInputs(false);
            }
            </script>
            </body></html>
            <?php
            $this->_consoleStarted = false;
        }
    }
}
