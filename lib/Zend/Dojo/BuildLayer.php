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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: BuildLayer.php 22280 2010-05-24 20:39:45Z matthew $
 */

/**
 * Dojo module layer and custom build profile generation support
 *
 * @package    Zend_Dojo
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Dojo_BuildLayer
{
    /**
     * Flag: whether or not to consume JS aggregated in the dojo() view
     * helper when generate the module layer contents
     * @var bool
     */
    protected $_consumeJavascript = false;

    /**
     * Flag: whether or not to consume dojo.addOnLoad events registered
     * with the dojo() view helper when generating the module layer file
     * contents
     * @var bool
     */
    protected $_consumeOnLoad = false;

    /**
     * Dojo view helper reference
     * @var Zend_Dojo_View_Helper_Dojo_Container
     */
    protected $_dojo;

    /**
     * Name of the custom layer to generate
     * @var string
     */
    protected $_layerName;

    /**
     * Path to the custom layer script relative to dojo.js (used when
     * creating the build profile)
     * @var string
     */
    protected $_layerScriptPath;

    /**
     * Build profile options
     * @var array
     */
    protected $_profileOptions = array(
        'action'        => 'release',
        'optimize'      => 'shrinksafe',
        'layerOptimize' => 'shrinksafe',
        'copyTests'     => false,
        'loader'        => 'default',
        'cssOptimize'   => 'comments',
    );

    /**
     * Associative array of module/path pairs for the build profile
     * @var array
     */
    protected $_profilePrefixes = array();

    /**
     * Zend_View reference
     * @var Zend_View_Interface
     */
    protected $_view;

    /**
     * Constructor
     *
     * @param  array|Zend_Config $options
     * @return void
     * @throws Zend_Dojo_Exception for invalid option argument
     */
    public function __construct($options = null)
    {
        if (null !== $options) {
            if ($options instanceof Zend_Config) {
                $options = $options->toArray();
            } elseif (!is_array($options)) {
                #require_once 'Zend/Dojo/Exception.php';
                throw new Zend_Dojo_Exception('Invalid options provided to constructor');
            }
            $this->setOptions($options);
        }
    }

    /**
     * Set options
     *
     * Proxies to any setter that matches an option key.
     *
     * @param  array $options
     * @return Zend_Dojo_BuildLayer
     */
    public function setOptions(array $options)
    {
        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (in_array($method, $methods)) {
                $this->$method($value);
            }
        }
        return $this;
    }

    /**
     * Set View object
     *
     * @param  Zend_View_Interface $view
     * @return Zend_Dojo_BuildLayer
     */
    public function setView(Zend_View_Interface $view)
    {
        $this->_view = $view;
        return $this;
    }

    /**
     * Retrieve view object
     *
     * @return Zend_View_Interface|null
     */
    public function getView()
    {
        return $this->_view;
    }

    /**
     * Set dojo() view helper instance
     *
     * @param  Zend_Dojo_View_Helper_Dojo_Container $helper
     * @return Zend_Dojo_BuildLayer
     */
    public function setDojoHelper(Zend_Dojo_View_Helper_Dojo_Container $helper)
    {
        $this->_dojo = $helper;
        return $this;
    }

    /**
     * Retrieve dojo() view helper instance
     *
     * Will retrieve it from the view object if not registered.
     *
     * @return Zend_Dojo_View_Helper_Dojo_Container
     * @throws Zend_Dojo_Exception if not registered and no view object found
     */
    public function getDojoHelper()
    {
        if (null === $this->_dojo) {
            if (null === ($view = $this->getView())) {
                #require_once 'Zend/Dojo/Exception.php';
                throw new Zend_Dojo_Exception('View object not registered; cannot retrieve dojo helper');
            }
            $helper = $view->getHelper('dojo');
            $this->setDojoHelper($view->dojo());
        }
        return $this->_dojo;
    }

    /**
     * Set custom layer name; e.g. "custom.main"
     *
     * @param  string $name
     * @return Zend_Dojo_BuildLayer
     */
    public function setLayerName($name)
    {
        if (!preg_match('/^[a-z][a-z0-9_]*(\.[a-z][a-z0-9_]*)+$/i', $name)) {
            #require_once 'Zend/Dojo/Exception.php';
            throw new Zend_Dojo_Exception('Invalid layer name provided; must be of form[a-z][a-z0-9_](\.[a-z][a-z0-9_])+');
        }
        $this->_layerName = $name;
        return $this;
    }

    /**
     * Retrieve custom layer name
     *
     * @return string|null
     */
    public function getLayerName()
    {
        return $this->_layerName;
    }

    /**
     * Set the path to the custom layer script
     *
     * Should be a path relative to dojo.js
     *
     * @param  string $path
     * @return Zend_Dojo_BuildLayer
     */
    public function setLayerScriptPath($path)
    {
        $this->_layerScriptPath = (string) $path;
        return $this;
    }

    /**
     * Get custom layer script path
     *
     * @return string|null
     */
    public function getLayerScriptPath()
    {
        return $this->_layerScriptPath;
    }

    /**
     * Set flag indicating whether or not to consume JS aggregated in dojo()
     * view helper
     *
     * @param  bool $flag
     * @return Zend_Dojo_BuildLayer
     */
    public function setConsumeJavascript($flag)
    {
        $this->_consumeJavascript = (bool) $flag;
        return $this;
    }

    /**
     * Get flag indicating whether or not to consume JS aggregated in dojo()
     * view helper
     *
     * @return bool
     */
    public function consumeJavascript()
    {
        return $this->_consumeJavascript;
    }

    /**
     * Set flag indicating whether or not to consume dojo.addOnLoad events
     * aggregated in dojo() view helper
     *
     * @param  bool $flag
     * @return Zend_Dojo_BuildLayer
     */
    public function setConsumeOnLoad($flag)
    {
        $this->_consumeOnLoad = (bool) $flag;
        return $this;
    }

    /**
     * Get flag indicating whether or not to consume dojo.addOnLoad events aggregated in dojo() view helper
     *
     * @return bool
     */
    public function consumeOnLoad()
    {
        return $this->_consumeOnLoad;
    }

    /**
     * Set many build profile options at once
     *
     * @param  array $options
     * @return Zend_Dojo_BuildLayer
     */
    public function setProfileOptions(array $options)
    {
        $this->_profileOptions += $options;
        return $this;
    }

    /**
     * Add many build profile options at once
     *
     * @param  array $options
     * @return Zend_Dojo_BuildLayer
     */
    public function addProfileOptions(array $options)
    {
        $this->_profileOptions = $this->_profileOptions + $options;
        return $this;
    }

    /**
     * Add a single build profile option
     *
     * @param  string $key
     * @param  value $value
     * @return Zend_Dojo_BuildLayer
     */
    public function addProfileOption($key, $value)
    {
        $this->_profileOptions[(string) $key] = $value;
        return $this;
    }

    /**
     * Is a given build profile option set?
     *
     * @param  string $key
     * @return bool
     */
    public function hasProfileOption($key)
    {
        return array_key_exists((string) $key, $this->_profileOptions);
    }

    /**
     * Retrieve a single build profile option
     *
     * Returns null if profile option does not exist.
     *
     * @param  string $key
     * @return mixed
     */
    public function getProfileOption($key)
    {
        if ($this->hasProfileOption($key)) {
            return $this->_profileOptions[(string) $key];
        }
        return null;
    }

    /**
     * Get all build profile options
     *
     * @return array
     */
    public function getProfileOptions()
    {
        return $this->_profileOptions;
    }

    /**
     * Remove a build profile option
     *
     * @param  string $name
     * @return Zend_Dojo_BuildLayer
     */
    public function removeProfileOption($name)
    {
        if ($this->hasProfileOption($name)) {
            unset($this->_profileOptions[(string) $name]);
        }
        return $this;
    }

    /**
     * Remove all build profile options
     *
     * @return Zend_Dojo_BuildLayer
     */
    public function clearProfileOptions()
    {
        $this->_profileOptions = array();
        return $this;
    }

    /**
     * Add a build profile dependency prefix
     *
     * If just the prefix is passed, sets path to "../$prefix".
     *
     * @param  string $prefix
     * @param  null|string $path
     * @return Zend_Dojo_BuildLayer
     */
    public function addProfilePrefix($prefix, $path = null)
    {
        if (null === $path) {
            $path = '../' . $prefix;
        }
        $this->_profilePrefixes[$prefix] = array($prefix, $path);
        return $this;
    }

    /**
     * Set multiple dependency prefixes for bulid profile
     *
     * @param  array $prefixes
     * @return Zend_Dojo_BuildLayer
     */
    public function setProfilePrefixes(array $prefixes)
    {
        foreach ($prefixes as $prefix => $path) {
            $this->addProfilePrefix($prefix, $path);
        }
        return $this;
    }

    /**
     * Get build profile dependency prefixes
     *
     * @return array
     */
    public function getProfilePrefixes()
    {
        $layerName = $this->getLayerName();
        if (null !== $layerName) {
            $prefix    = $this->_getPrefix($layerName);
            if (!array_key_exists($prefix, $this->_profilePrefixes)) {
                $this->addProfilePrefix($prefix);
            }
        }
        $view = $this->getView();
        if (!empty($view)) {
            $helper = $this->getDojoHelper();
            if ($helper) {
                $modules = $helper->getModules();
                foreach ($modules as $module) {
                    $prefix = $this->_getPrefix($module);
                    if (!array_key_exists($prefix, $this->_profilePrefixes)) {
                        $this->addProfilePrefix($prefix);
                    }
                }
            }
        }
        return $this->_profilePrefixes;
    }

    /**
     * Generate module layer script
     *
     * @return string
     */
    public function generateLayerScript()
    {
        $helper        = $this->getDojoHelper();
        $layerName     = $this->getLayerName();
        $modulePaths   = $helper->getModulePaths();
        $modules       = $helper->getModules();
        $onLoadActions = $helper->getOnLoadActions();
        $javascript    = $helper->getJavascript();

        $content = 'dojo.provide("' . $layerName . '");' . "\n\n(function(){\n";

        foreach ($modulePaths as $module => $path) {
            $content .= sprintf("dojo.registerModulePath(\"%s\", \"%s\");\n", $module, $path);
        }
        foreach ($modules as $module) {
            $content .= sprintf("dojo.require(\"%s\");\n", $module);
        }

        if ($this->consumeOnLoad()) {
            foreach ($helper->getOnLoadActions() as $callback) {
                $content .= sprintf("dojo.addOnLoad(%s);\n", $callback);
            }
        }
        if ($this->consumeJavascript()) {
            $javascript = implode("\n", $helper->getJavascript());
            if (!empty($javascript)) {
                $content .= "\n" . $javascript . "\n";
            }
        }

        $content .= "})();";

        return $content;
    }

    /**
     * Generate build profile
     *
     * @return string
     */
    public function generateBuildProfile()
    {
        $profileOptions  = $this->getProfileOptions();
        $layerName       = $this->getLayerName();
        $layerScriptPath = $this->getLayerScriptPath();
        $profilePrefixes = $this->getProfilePrefixes();

        if (!array_key_exists('releaseName', $profileOptions)) {
            $profileOptions['releaseName'] = substr($layerName, 0, strpos($layerName, '.'));
        }

        $profile = $profileOptions;
        $profile['layers'] = array(array(
            'name'              => $layerScriptPath,
            'layerDependencies' => array(),
            'dependencies'      => array($layerName),
        ));
        $profile['prefixes'] = array_values($profilePrefixes);

        return 'dependencies = ' . $this->_filterJsonProfileToJavascript($profile) . ';';
    }

    /**
     * Retrieve module prefix
     *
     * @param  string $module
     * @return void
     */
    protected function _getPrefix($module)
    {
        $segments  = explode('.', $module, 2);
        return $segments[0];
    }

    /**
     * Filter a JSON build profile to JavaScript
     *
     * @param  string $profile
     * @return string
     */
    protected function _filterJsonProfileToJavascript($profile)
    {
        #require_once 'Zend/Json.php';
        $profile = Zend_Json::encode($profile);
        $profile = trim($profile, '"');
        $profile = preg_replace('/' . preg_quote('\\') . '/', '', $profile);
        return $profile;
    }
}
