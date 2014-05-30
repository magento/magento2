<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Dojo
 * @subpackage View
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @version    $Id: Container.php 23368 2010-11-18 19:56:30Z bittarman $
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Zend_Dojo */
#require_once 'Zend/Dojo.php';

/**
 * Container for  Dojo View Helper
 *
 *
 * @package    Zend_Dojo
 * @subpackage View
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Dojo_View_Helper_Dojo_Container
{
    /**
     * @var Zend_View_Interface
     */
    public $view;

    /**
     * addOnLoad capture lock
     * @var bool
     */
    protected $_captureLock = false;

    /**
     * addOnLoad object on which to apply lambda
     * @var string
     */
    protected $_captureObj;

    /**
     * Base CDN url to utilize
     * @var string
     */
    protected $_cdnBase = Zend_Dojo::CDN_BASE_GOOGLE;

    /**
     * Path segment following version string of CDN path
     * @var string
     */
    protected $_cdnDojoPath = Zend_Dojo::CDN_DOJO_PATH_GOOGLE;

    /**
     * Dojo version to use from CDN
     * @var string
     */
    protected $_cdnVersion = '1.5.0';

    /**
     * Has the dijit loader been registered?
     * @var bool
     */
    protected $_dijitLoaderRegistered = false;

    /**
     * Registered programmatic dijits
     * @var array
     */
    protected $_dijits = array();

    /**
     * Dojo configuration
     * @var array
     */
    protected $_djConfig = array();

    /**
     * Whether or not dojo is enabled
     * @var bool
     */
    protected $_enabled = false;

    /**
     * Are we rendering as XHTML?
     * @var bool
     */
    protected $_isXhtml = false;

    /**
     * Arbitrary javascript to include in dojo script
     * @var array
     */
    protected $_javascriptStatements = array();

    /**
     * Dojo layers (custom builds) to use
     * @var array
     */
    protected $_layers = array();

    /**
     * Relative path to dojo
     * @var string
     */
    protected $_localPath = null;

    /**
     * Root of dojo where all dojo files are installed
     * @var string
     */
    protected $_localRelativePath = null;

    /**
     * Modules to require
     * @var array
     */
    protected $_modules = array();

    /**
     * Registered module paths
     * @var array
     */
    protected $_modulePaths = array();

    /**
     * Actions to perform on window load
     * @var array
     */
    protected $_onLoadActions = array();

    /**
     * Register the Dojo stylesheet?
     * @var bool
     */
    protected $_registerDojoStylesheet = false;

    /**
     * Style sheet modules to load
     * @var array
     */
    protected $_stylesheetModules = array();

    /**
     * Local stylesheets
     * @var array
     */
    protected $_stylesheets = array();

    /**
     * Array of onLoad events specific to Zend_Dojo integration operations
     * @var array
     */
    protected $_zendLoadActions = array();

    /**
     * Set view object
     *
     * @param  Zend_Dojo_View_Interface $view
     * @return void
     */
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

    /**
     * Enable dojo
     *
     * @return Zend_Dojo_View_Helper_Dojo_Container
     */
    public function enable()
    {
        $this->_enabled = true;
        return $this;
    }

    /**
     * Disable dojo
     *
     * @return Zend_Dojo_View_Helper_Dojo_Container
     */
    public function disable()
    {
        $this->_enabled = false;
        return $this;
    }

    /**
     * Is dojo enabled?
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->_enabled;
    }

    /**
     * Add options for the Dojo Container to use
     *
     * @param array|Zend_Config Array or Zend_Config object with options to use
     * @return Zend_Dojo_View_Helper_Dojo_Container
     */
    public function setOptions($options)
    {
        if($options instanceof Zend_Config) {
            $options = $options->toArray();
        }

        foreach($options as $key => $value) {
            $key = strtolower($key);
            switch($key) {
                case 'requiremodules':
                    $this->requireModule($value);
                    break;
                case 'modulepaths':
                    foreach($value as $module => $path) {
                        $this->registerModulePath($module, $path);
                    }
                    break;
                case 'layers':
                    $value = (array) $value;
                    foreach($value as $layer) {
                        $this->addLayer($layer);
                    }
                    break;
                case 'cdnbase':
                    $this->setCdnBase($value);
                    break;
                case 'cdnversion':
                    $this->setCdnVersion($value);
                    break;
                case 'cdndojopath':
                    $this->setCdnDojoPath($value);
                    break;
                case 'localpath':
                    $this->setLocalPath($value);
                    break;
                case 'djconfig':
                    $this->setDjConfig($value);
                    break;
                case 'stylesheetmodules':
                    $value = (array) $value;
                    foreach($value as $module) {
                        $this->addStylesheetModule($module);
                    }
                    break;
                case 'stylesheets':
                    $value = (array) $value;
                    foreach($value as $stylesheet) {
                        $this->addStylesheet($stylesheet);
                    }
                    break;
                case 'registerdojostylesheet':
                    $this->registerDojoStylesheet($value);
                    break;
                case 'enable':
                    if($value) {
                        $this->enable();
                    } else {
                        $this->disable();
                    }
            }
        }

        return $this;
    }

    /**
     * Specify one or multiple modules to require
     *
     * @param  string|array $modules
     * @return Zend_Dojo_View_Helper_Dojo_Container
     */
    public function requireModule($modules)
    {
        if (!is_string($modules) && !is_array($modules)) {
            #require_once 'Zend/Dojo/View/Exception.php';
            throw new Zend_Dojo_View_Exception('Invalid module name specified; must be a string or an array of strings');
        }

        $modules = (array) $modules;

        foreach ($modules as $mod) {
            if (!preg_match('/^[a-z][a-z0-9._-]+$/i', $mod)) {
                #require_once 'Zend/Dojo/View/Exception.php';
                throw new Zend_Dojo_View_Exception(sprintf('Module name specified, "%s", contains invalid characters', (string) $mod));
            }

            if (!in_array($mod, $this->_modules)) {
                $this->_modules[] = $mod;
            }
        }

        return $this;
    }

    /**
     * Retrieve list of modules to require
     *
     * @return array
     */
    public function getModules()
    {
        return $this->_modules;
    }

    /**
     * Register a module path
     *
     * @param  string $module The module to register a path for
     * @param  string $path The path to register for the module
     * @return Zend_Dojo_View_Helper_Dojo_Container
     */
    public function registerModulePath($module, $path)
    {
        $path = (string) $path;
        if (!in_array($module, $this->_modulePaths)) {
            $this->_modulePaths[$module] = $path;
        }

        return $this;
    }

    /**
     * List registered module paths
     *
     * @return array
     */
    public function getModulePaths()
    {
        return $this->_modulePaths;
    }

    /**
     * Add layer (custom build) path
     *
     * @param  string $path
     * @return Zend_Dojo_View_Helper_Dojo_Container
     */
    public function addLayer($path)
    {
        $path = (string) $path;
        if (!in_array($path, $this->_layers)) {
            $this->_layers[] = $path;
        }

        return $this;
    }

    /**
     * Get registered layers
     *
     * @return array
     */
    public function getLayers()
    {
        return $this->_layers;
    }

    /**
     * Remove a registered layer
     *
     * @param  string $path
     * @return Zend_Dojo_View_Helper_Dojo_Container
     */
    public function removeLayer($path)
    {
        $path = (string) $path;
        $layers = array_flip($this->_layers);
        if (array_key_exists($path, $layers)) {
            unset($layers[$path]);
            $this->_layers = array_keys($layers);
        }
        return $this;
    }

    /**
     * Clear all registered layers
     *
     * @return Zend_Dojo_View_Helper_Dojo_Container
     */
    public function clearLayers()
    {
        $this->_layers = array();
        return $this;
    }

    /**
     * Set CDN base path
     *
     * @param  string $url
     * @return Zend_Dojo_View_Helper_Dojo_Container
     */
    public function setCdnBase($url)
    {
        $this->_cdnBase = (string) $url;
        return $this;
    }

    /**
     * Return CDN base URL
     *
     * @return string
     */
    public function getCdnBase()
    {
        return $this->_cdnBase;
    }

    /**
     * Use CDN, using version specified
     *
     * @param  string $version
     * @return Zend_Dojo_View_Helper_Dojo_Container
     */
    public function setCdnVersion($version = null)
    {
        $this->enable();
        if (preg_match('/^[1-9]\.[0-9](\.[0-9])?$/', $version)) {
            $this->_cdnVersion = $version;
        }
        return $this;
    }

    /**
     * Get CDN version
     *
     * @return string
     */
    public function getCdnVersion()
    {
        return $this->_cdnVersion;
    }

    /**
     * Set CDN path to dojo (relative to CDN base + version)
     *
     * @param  string $path
     * @return Zend_Dojo_View_Helper_Dojo_Container
     */
    public function setCdnDojoPath($path)
    {
        $this->_cdnDojoPath = (string) $path;
        return $this;
    }

    /**
     * Get CDN path to dojo (relative to CDN base + version)
     *
     * @return string
     */
    public function getCdnDojoPath()
    {
        return $this->_cdnDojoPath;
    }

    /**
     * Are we using the CDN?
     *
     * @return bool
     */
    public function useCdn()
    {
        return !$this->useLocalPath();
    }

    /**
     * Set path to local dojo
     *
     * @param  string $path
     * @return Zend_Dojo_View_Helper_Dojo_Container
     */
    public function setLocalPath($path)
    {
        $this->enable();
        $this->_localPath = (string) $path;
        return $this;
    }

    /**
     * Get local path to dojo
     *
     * @return string
     */
    public function getLocalPath()
    {
        return $this->_localPath;
    }

    /**
     * Are we using a local path?
     *
     * @return bool
     */
    public function useLocalPath()
    {
        return (null === $this->_localPath) ? false : true;
    }

    /**
     * Set Dojo configuration
     *
     * @param  string $option
     * @param  mixed $value
     * @return Zend_Dojo_View_Helper_Dojo_Container
     */
    public function setDjConfig(array $config)
    {
        $this->_djConfig = $config;
        return $this;
    }

    /**
     * Set Dojo configuration option
     *
     * @param  string $option
     * @param  mixed $value
     * @return Zend_Dojo_View_Helper_Dojo_Container
     */
    public function setDjConfigOption($option, $value)
    {
        $option = (string) $option;
        $this->_djConfig[$option] = $value;
        return $this;
    }

    /**
     * Retrieve dojo configuration values
     *
     * @return array
     */
    public function getDjConfig()
    {
        return $this->_djConfig;
    }

    /**
     * Get dojo configuration value
     *
     * @param  string $option
     * @param  mixed $default
     * @return mixed
     */
    public function getDjConfigOption($option, $default = null)
    {
        $option = (string) $option;
        if (array_key_exists($option, $this->_djConfig)) {
            return $this->_djConfig[$option];
        }
        return $default;
    }

    /**
     * Add a stylesheet by module name
     *
     * @param  string $module
     * @return Zend_Dojo_View_Helper_Dojo_Container
     */
    public function addStylesheetModule($module)
    {
        if (!preg_match('/^[a-z0-9]+\.[a-z0-9_-]+(\.[a-z0-9_-]+)*$/i', $module)) {
            #require_once 'Zend/Dojo/View/Exception.php';
            throw new Zend_Dojo_View_Exception('Invalid stylesheet module specified');
        }
        if (!in_array($module, $this->_stylesheetModules)) {
            $this->_stylesheetModules[] = $module;
        }
        return $this;
    }

    /**
     * Get all stylesheet modules currently registered
     *
     * @return array
     */
    public function getStylesheetModules()
    {
        return $this->_stylesheetModules;
    }

    /**
     * Add a stylesheet
     *
     * @param  string $path
     * @return Zend_Dojo_View_Helper_Dojo_Container
     */
    public function addStylesheet($path)
    {
        $path = (string) $path;
        if (!in_array($path, $this->_stylesheets)) {
            $this->_stylesheets[] = (string) $path;
        }
        return $this;
    }

    /**
     * Register the dojo.css stylesheet?
     *
     * With no arguments, returns the status of the flag; with arguments, sets
     * the flag and returns the object.
     *
     * @param  null|bool $flag
     * @return Zend_Dojo_View_Helper_Dojo_Container|bool
     */
    public function registerDojoStylesheet($flag = null)
    {
        if (null === $flag) {
             return $this->_registerDojoStylesheet;
        }

        $this->_registerDojoStylesheet = (bool) $flag;
        return $this;
    }

    /**
     * Retrieve registered stylesheets
     *
     * @return array
     */
    public function getStylesheets()
    {
        return $this->_stylesheets;
    }

    /**
     * Add a script to execute onLoad
     *
     * dojo.addOnLoad accepts:
     * - function name
     * - lambda
     *
     * @param  string $callback Lambda
     * @return Zend_Dojo_View_Helper_Dojo_Container
     */
    public function addOnLoad($callback)
    {
        if (!in_array($callback, $this->_onLoadActions, true)) {
            $this->_onLoadActions[] = $callback;
        }
        return $this;
    }

    /**
     * Prepend an onLoad event to the list of onLoad actions
     *
     * @param  string $callback Lambda
     * @return Zend_Dojo_View_Helper_Dojo_Container
     */
    public function prependOnLoad($callback)
    {
        if (!in_array($callback, $this->_onLoadActions, true)) {
            array_unshift($this->_onLoadActions, $callback);
        }
        return $this;
    }

    /**
     * Retrieve all registered onLoad actions
     *
     * @return array
     */
    public function getOnLoadActions()
    {
        return $this->_onLoadActions;
    }

    /**
     * Start capturing routines to run onLoad
     *
     * @return bool
     */
    public function onLoadCaptureStart()
    {
        if ($this->_captureLock) {
            #require_once 'Zend/Dojo/View/Exception.php';
            throw new Zend_Dojo_View_Exception('Cannot nest onLoad captures');
        }

        $this->_captureLock = true;
        ob_start();
        return;
    }

    /**
     * Stop capturing routines to run onLoad
     *
     * @return bool
     */
    public function onLoadCaptureEnd()
    {
        $data               = ob_get_clean();
        $this->_captureLock = false;

        $this->addOnLoad($data);
        return true;
    }

    /**
     * Add a programmatic dijit
     *
     * @param  string $id
     * @param  array $params
     * @return Zend_Dojo_View_Helper_Dojo_Container
     */
    public function addDijit($id, array $params)
    {
        if (array_key_exists($id, $this->_dijits)) {
            #require_once 'Zend/Dojo/View/Exception.php';
            throw new Zend_Dojo_View_Exception(sprintf('Duplicate dijit with id "%s" already registered', $id));
        }

        $this->_dijits[$id] = array(
            'id'     => $id,
            'params' => $params,
        );

        return $this;
    }

    /**
     * Set a programmatic dijit (overwrites)
     *
     * @param  string $id
     * @param  array $params
     * @return Zend_Dojo_View_Helper_Dojo_Container
     */
    public function setDijit($id, array $params)
    {
        $this->removeDijit($id);
        return $this->addDijit($id, $params);
    }

    /**
     * Add multiple dijits at once
     *
     * Expects an array of id => array $params pairs
     *
     * @param  array $dijits
     * @return Zend_Dojo_View_Helper_Dojo_Container
     */
    public function addDijits(array $dijits)
    {
        foreach ($dijits as $id => $params) {
            $this->addDijit($id, $params);
        }
        return $this;
    }

    /**
     * Set multiple dijits at once (overwrites)
     *
     * Expects an array of id => array $params pairs
     *
     * @param  array $dijits
     * @return Zend_Dojo_View_Helper_Dojo_Container
     */
    public function setDijits(array $dijits)
    {
        $this->clearDijits();
        return $this->addDijits($dijits);
    }

    /**
     * Is the given programmatic dijit already registered?
     *
     * @param  string $id
     * @return bool
     */
    public function hasDijit($id)
    {
        return array_key_exists($id, $this->_dijits);
    }

    /**
     * Retrieve a dijit by id
     *
     * @param  string $id
     * @return array|null
     */
    public function getDijit($id)
    {
        if ($this->hasDijit($id)) {
            return $this->_dijits[$id]['params'];
        }
        return null;
    }

    /**
     * Retrieve all dijits
     *
     * Returns dijits as an array of assoc arrays
     *
     * @return array
     */
    public function getDijits()
    {
        return array_values($this->_dijits);
    }

    /**
     * Remove a programmatic dijit if it exists
     *
     * @param  string $id
     * @return Zend_Dojo_View_Helper_Dojo_Container
     */
    public function removeDijit($id)
    {
        if (array_key_exists($id, $this->_dijits)) {
            unset($this->_dijits[$id]);
        }

        return $this;
    }

    /**
     * Clear all dijits
     *
     * @return Zend_Dojo_View_Helper_Dojo_Container
     */
    public function clearDijits()
    {
        $this->_dijits = array();
        return $this;
    }

    /**
     * Render dijits as JSON structure
     *
     * @return string
     */
    public function dijitsToJson()
    {
        #require_once 'Zend/Json.php';
        return Zend_Json::encode($this->getDijits(), false, array('enableJsonExprFinder' => true));
    }

    /**
     * Create dijit loader functionality
     *
     * @return void
     */
    public function registerDijitLoader()
    {
        if (!$this->_dijitLoaderRegistered) {
            $js =<<<EOJ
function() {
    dojo.forEach(zendDijits, function(info) {
        var n = dojo.byId(info.id);
        if (null != n) {
            dojo.attr(n, dojo.mixin({ id: info.id }, info.params));
        }
    });
    dojo.parser.parse();
}
EOJ;
            $this->requireModule('dojo.parser');
            $this->_addZendLoad($js);
            $this->addJavascript('var zendDijits = ' . $this->dijitsToJson() . ';');
            $this->_dijitLoaderRegistered = true;
        }
    }

    /**
     * Add arbitrary javascript to execute in dojo JS container
     *
     * @param  string $js
     * @return Zend_Dojo_View_Helper_Dojo_Container
     */
    public function addJavascript($js)
    {
        $js = preg_replace('/^\s*(.*?)\s*$/s', '$1', $js);
        if (!in_array(substr($js, -1), array(';', '}'))) {
            $js .= ';';
        }

        if (in_array($js, $this->_javascriptStatements)) {
            return $this;
        }

        $this->_javascriptStatements[] = $js;
        return $this;
    }

    /**
     * Return all registered javascript statements
     *
     * @return array
     */
    public function getJavascript()
    {
        return $this->_javascriptStatements;
    }

    /**
     * Clear arbitrary javascript stack
     *
     * @return Zend_Dojo_View_Helper_Dojo_Container
     */
    public function clearJavascript()
    {
        $this->_javascriptStatements = array();
        return $this;
    }

    /**
     * Capture arbitrary javascript to include in dojo script
     *
     * @return void
     */
    public function javascriptCaptureStart()
    {
        if ($this->_captureLock) {
            #require_once 'Zend/Dojo/View/Exception.php';
            throw new Zend_Dojo_View_Exception('Cannot nest captures');
        }

        $this->_captureLock = true;
        ob_start();
        return;
    }

    /**
     * Finish capturing arbitrary javascript to include in dojo script
     *
     * @return true
     */
    public function javascriptCaptureEnd()
    {
        $data               = ob_get_clean();
        $this->_captureLock = false;

        $this->addJavascript($data);
        return true;
    }

    /**
     * String representation of dojo environment
     *
     * @return string
     */
    public function __toString()
    {
        if (!$this->isEnabled()) {
            return '';
        }

        $this->_isXhtml = $this->view->doctype()->isXhtml();

        if (Zend_Dojo_View_Helper_Dojo::useDeclarative()) {
            if (null === $this->getDjConfigOption('parseOnLoad')) {
                $this->setDjConfigOption('parseOnLoad', true);
            }
        }

        if (!empty($this->_dijits)) {
            $this->registerDijitLoader();
        }

        $html  = $this->_renderStylesheets() . PHP_EOL
               . $this->_renderDjConfig() . PHP_EOL
               . $this->_renderDojoScriptTag() . PHP_EOL
               . $this->_renderLayers() . PHP_EOL
               . $this->_renderExtras();
        return $html;
    }

    /**
     * Retrieve local path to dojo resources for building relative paths
     *
     * @return string
     */
    protected function _getLocalRelativePath()
    {
        if (null === $this->_localRelativePath) {
            $localPath = $this->getLocalPath();
            $localPath = preg_replace('|[/\\\\]dojo[/\\\\]dojo.js[^/\\\\]*$|i', '', $localPath);
            $this->_localRelativePath = $localPath;
        }
        return $this->_localRelativePath;
    }

    /**
     * Render dojo stylesheets
     *
     * @return string
     */
    protected function _renderStylesheets()
    {
        if ($this->useCdn()) {
            $base = $this->getCdnBase()
                  . $this->getCdnVersion();
        } else {
            $base = $this->_getLocalRelativePath();
        }

        $registeredStylesheets = $this->getStylesheetModules();
        foreach ($registeredStylesheets as $stylesheet) {
            $themeName     = substr($stylesheet, strrpos($stylesheet, '.') + 1);
            $stylesheet    = str_replace('.', '/', $stylesheet);
            $stylesheets[] = $base . '/' . $stylesheet . '/' . $themeName . '.css';
        }

        foreach ($this->getStylesheets() as $stylesheet) {
            $stylesheets[] = $stylesheet;
        }

        if ($this->_registerDojoStylesheet) {
            $stylesheets[] = $base . '/dojo/resources/dojo.css';
        }

        if (empty($stylesheets)) {
            return '';
        }

        array_reverse($stylesheets);
        $style = '<style type="text/css">' . PHP_EOL
               . (($this->_isXhtml) ? '<!--' : '<!--') . PHP_EOL;
        foreach ($stylesheets as $stylesheet) {
            $style .= '    @import "' . $stylesheet . '";' . PHP_EOL;
        }
        $style .= (($this->_isXhtml) ? '-->' : '-->') . PHP_EOL
                . '</style>';

        return $style;
    }

    /**
     * Render DjConfig values
     *
     * @return string
     */
    protected function _renderDjConfig()
    {
        $djConfigValues = $this->getDjConfig();
        if (empty($djConfigValues)) {
            return '';
        }

        #require_once 'Zend/Json.php';
        $scriptTag = '<script type="text/javascript">' . PHP_EOL
                   . (($this->_isXhtml) ? '//<![CDATA[' : '//<!--') . PHP_EOL
                   . '    var djConfig = ' . Zend_Json::encode($djConfigValues) . ';' . PHP_EOL
                   . (($this->_isXhtml) ? '//]]>' : '//-->') . PHP_EOL
                   . '</script>';

        return $scriptTag;
    }

    /**
     * Render dojo script tag
     *
     * Renders Dojo script tag by utilizing either local path provided or the
     * CDN. If any djConfig values were set, they will be serialized and passed
     * with that attribute.
     *
     * @return string
     */
    protected function _renderDojoScriptTag()
    {
        if ($this->useCdn()) {
            $source = $this->getCdnBase()
                    . $this->getCdnVersion()
                    . $this->getCdnDojoPath();
        } else {
            $source = $this->getLocalPath();
        }

        $scriptTag = '<script type="text/javascript" src="' . $source . '"></script>';
        return $scriptTag;
    }

    /**
     * Render layers (custom builds) as script tags
     *
     * @return string
     */
    protected function _renderLayers()
    {
        $layers = $this->getLayers();
        if (empty($layers)) {
            return '';
        }

        $enc = 'UTF-8';
        if ($this->view instanceof Zend_View_Interface
            && method_exists($this->view, 'getEncoding')
        ) {
            $enc = $this->view->getEncoding();
        }

        $html = array();
        foreach ($layers as $path) {
            $html[] = sprintf(
                '<script type="text/javascript" src="%s"></script>',
                htmlspecialchars($path, ENT_QUOTES, $enc)
            );
        }

        return implode("\n", $html);
    }

    /**
     * Render dojo module paths and requires
     *
     * @return string
     */
    protected function _renderExtras()
    {
        $js = array();
        $modulePaths = $this->getModulePaths();
        if (!empty($modulePaths)) {
            foreach ($modulePaths as $module => $path) {
                $js[] =  'dojo.registerModulePath("' . $this->view->escape($module) . '", "' . $this->view->escape($path) . '");';
            }
        }

        $modules = $this->getModules();
        if (!empty($modules)) {
            foreach ($modules as $module) {
                $js[] = 'dojo.require("' . $this->view->escape($module) . '");';
            }
        }

        $onLoadActions = array();
        // Get Zend specific onLoad actions; these will always be first to 
        // ensure that dijits are created in the correct order
        foreach ($this->_getZendLoadActions() as $callback) {
            $onLoadActions[] = 'dojo.addOnLoad(' . $callback . ');';
        }

        // Get all other onLoad actions
        foreach ($this->getOnLoadActions() as $callback) {
            $onLoadActions[] = 'dojo.addOnLoad(' . $callback . ');';
        }

        $javascript = implode("\n    ", $this->getJavascript());

        $content = '';
        if (!empty($js)) {
            $content .= implode("\n    ", $js) . "\n";
        }

        if (!empty($onLoadActions)) {
            $content .= implode("\n    ", $onLoadActions) . "\n";
        }

        if (!empty($javascript)) {
            $content .= $javascript . "\n";
        }

        if (preg_match('/^\s*$/s', $content)) {
            return '';
        }

        $html = '<script type="text/javascript">' . PHP_EOL
              . (($this->_isXhtml) ? '//<![CDATA[' : '//<!--') . PHP_EOL
              . $content
              . (($this->_isXhtml) ? '//]]>' : '//-->') . PHP_EOL
              . PHP_EOL . '</script>';
        return $html;
    }

    /**
     * Add an onLoad action related to ZF dijit creation
     *
     * This method is public, but prefixed with an underscore to indicate that 
     * it should not normally be called by userland code. It is pertinent to
     * ensuring that the correct order of operations occurs during dijit 
     * creation.
     * 
     * @param  string $callback 
     * @return Zend_Dojo_View_Helper_Dojo_Container
     */
    public function _addZendLoad($callback)
    {
        if (!in_array($callback, $this->_zendLoadActions, true)) {
            $this->_zendLoadActions[] = $callback;
        }
        return $this;
    }

    /**
     * Retrieve all ZF dijit callbacks
     * 
     * @return array
     */
    public function _getZendLoadActions()
    {
        return $this->_zendLoadActions;
    }
}
