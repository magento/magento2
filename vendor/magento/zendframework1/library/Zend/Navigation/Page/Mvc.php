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
 * @package    Zend_Navigation
 * @subpackage Page
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Navigation_Page
 */
#require_once 'Zend/Navigation/Page.php';

/**
 * @see Zend_Controller_Action_HelperBroker
 */
#require_once 'Zend/Controller/Action/HelperBroker.php';

/**
 * Used to check if page is active
 *
 * @see Zend_Controller_Front
 */
#require_once 'Zend/Controller/Front.php';

/**
 * Represents a page that is defined using module, controller, action, route
 * name and route params to assemble the href
 *
 * @category   Zend
 * @package    Zend_Navigation
 * @subpackage Page
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Navigation_Page_Mvc extends Zend_Navigation_Page
{
    /**
     * Action name to use when assembling URL
     *
     * @var string
     */
    protected $_action;

    /**
     * Controller name to use when assembling URL
     *
     * @var string
     */
    protected $_controller;

    /**
     * Module name to use when assembling URL
     *
     * @var string
     */
    protected $_module;

    /**
     * Params to use when assembling URL
     *
     * @see getHref()
     * @var array
     */
    protected $_params = array();

    /**
     * Route name to use when assembling URL
     *
     * @see getHref()
     * @var string
     */
    protected $_route;

    /**
     * Whether params should be reset when assembling URL
     *
     * @see getHref()
     * @var bool
     */
    protected $_resetParams = true;

    /**
     * Whether href should be encoded when assembling URL
     *
     * @see getHref()
     * @var bool
     */
    protected $_encodeUrl = true;

    /**
     * Whether this page should be considered active
     *
     * @var bool
     */
    protected $_active = null;

    /**
     * Scheme to use when assembling URL
     *
     * @see getHref()
     * @var string
     */
    protected $_scheme;

    /**
     * Cached href
     *
     * The use of this variable minimizes execution time when getHref() is
     * called more than once during the lifetime of a request. If a property
     * is updated, the cache is invalidated.
     *
     * @var string
     */
    protected $_hrefCache;

    /**
     * Action helper for assembling URLs
     *
     * @see getHref()
     * @var Zend_Controller_Action_Helper_Url
     */
    protected static $_urlHelper = null;

    /**
     * View helper for assembling URLs with schemes
     *
     * @see getHref()
     * @var Zend_View_Helper_ServerUrl
     */
    protected static $_schemeHelper = null;

    // Accessors:

    /**
     * Returns whether page should be considered active or not
     *
     * This method will compare the page properties against the request object
     * that is found in the front controller.
     *
     * @param  bool $recursive  [optional] whether page should be considered
     *                          active if any child pages are active. Default is
     *                          false.
     * @return bool             whether page should be considered active or not
     */
    public function isActive($recursive = false)
    {
        if (null === $this->_active) {
            $front     = Zend_Controller_Front::getInstance();
            $request   = $front->getRequest();
            $reqParams = array();
            if ($request) {
                $reqParams = $request->getParams();
                if (!array_key_exists('module', $reqParams)) {
                    $reqParams['module'] = $front->getDefaultModule();
                }
            }

            $myParams = $this->_params;

            if ($this->_route
                && method_exists($front->getRouter(), 'getRoute')
            ) {
                $route = $front->getRouter()->getRoute($this->_route);
                if (method_exists($route, 'getDefaults')) {
                    $myParams = array_merge($route->getDefaults(), $myParams);
                }
            }

            if (null !== $this->_module) {
                $myParams['module'] = $this->_module;
            } elseif (!array_key_exists('module', $myParams)) {
                $myParams['module'] = $front->getDefaultModule();
            }

            if (null !== $this->_controller) {
                $myParams['controller'] = $this->_controller;
            } elseif (!array_key_exists('controller', $myParams)) {
                $myParams['controller'] = $front->getDefaultControllerName();
            }

            if (null !== $this->_action) {
                $myParams['action'] = $this->_action;
            } elseif (!array_key_exists('action', $myParams)) {
                $myParams['action'] = $front->getDefaultAction();
            }

            foreach ($myParams as $key => $value) {
                if (null === $value) {
                    unset($myParams[$key]);
                }
            }

            if (count(array_intersect_assoc($reqParams, $myParams)) ==
                count($myParams)
            ) {
                $this->_active = true;

                return true;
            }

            $this->_active = false;
        }

        return parent::isActive($recursive);
    }

    /**
     * Returns href for this page
     *
     * This method uses {@link Zend_Controller_Action_Helper_Url} to assemble
     * the href based on the page's properties.
     *
     * @return string  page href
     */
    public function getHref()
    {
        if ($this->_hrefCache) {
            return $this->_hrefCache;
        }

        if (null === self::$_urlHelper) {
            self::$_urlHelper =
                Zend_Controller_Action_HelperBroker::getStaticHelper('Url');
        }

        $params = $this->getParams();

        if ($param = $this->getModule()) {
            $params['module'] = $param;
        }

        if ($param = $this->getController()) {
            $params['controller'] = $param;
        }

        if ($param = $this->getAction()) {
            $params['action'] = $param;
        }

        $url = self::$_urlHelper->url(
            $params,
            $this->getRoute(),
            $this->getResetParams(),
            $this->getEncodeUrl()
        );

        // Use scheme?
        $scheme = $this->getScheme();
        if (null !== $scheme) {
            if (null === self::$_schemeHelper) {
                #require_once 'Zend/View/Helper/ServerUrl.php';
                self::$_schemeHelper = new Zend_View_Helper_ServerUrl();
            }

            $url = self::$_schemeHelper->setScheme($scheme)->serverUrl($url);
        }

        // Add the fragment identifier if it is set
        $fragment = $this->getFragment();
        if (null !== $fragment) {
            $url .= '#' . $fragment;
        }

        return $this->_hrefCache = $url;
    }

    /**
     * Sets action name to use when assembling URL
     *
     * @see getHref()
     *
     * @param  string $action             action name
     * @return Zend_Navigation_Page_Mvc   fluent interface, returns self
     * @throws Zend_Navigation_Exception  if invalid $action is given
     */
    public function setAction($action)
    {
        if (null !== $action && !is_string($action)) {
            #require_once 'Zend/Navigation/Exception.php';
            throw new Zend_Navigation_Exception(
                'Invalid argument: $action must be a string or null'
            );
        }

        $this->_action    = $action;
        $this->_hrefCache = null;

        return $this;
    }

    /**
     * Returns action name to use when assembling URL
     *
     * @see getHref()
     *
     * @return string|null  action name
     */
    public function getAction()
    {
        return $this->_action;
    }

    /**
     * Sets controller name to use when assembling URL
     *
     * @see getHref()
     *
     * @param  string|null $controller    controller name
     * @return Zend_Navigation_Page_Mvc   fluent interface, returns self
     * @throws Zend_Navigation_Exception  if invalid controller name is given
     */
    public function setController($controller)
    {
        if (null !== $controller && !is_string($controller)) {
            #require_once 'Zend/Navigation/Exception.php';
            throw new Zend_Navigation_Exception(
                'Invalid argument: $controller must be a string or null'
            );
        }

        $this->_controller = $controller;
        $this->_hrefCache  = null;

        return $this;
    }

    /**
     * Returns controller name to use when assembling URL
     *
     * @see getHref()
     *
     * @return string|null  controller name or null
     */
    public function getController()
    {
        return $this->_controller;
    }

    /**
     * Sets module name to use when assembling URL
     *
     * @see getHref()
     *
     * @param  string|null $module        module name
     * @return Zend_Navigation_Page_Mvc   fluent interface, returns self
     * @throws Zend_Navigation_Exception  if invalid module name is given
     */
    public function setModule($module)
    {
        if (null !== $module && !is_string($module)) {
            #require_once 'Zend/Navigation/Exception.php';
            throw new Zend_Navigation_Exception(
                'Invalid argument: $module must be a string or null'
            );
        }

        $this->_module    = $module;
        $this->_hrefCache = null;

        return $this;
    }

    /**
     * Returns module name to use when assembling URL
     *
     * @see getHref()
     *
     * @return string|null  module name or null
     */
    public function getModule()
    {
        return $this->_module;
    }

    /**
     * Set multiple parameters (to use when assembling URL) at once
     *
     * URL options passed to the url action helper for assembling URLs.
     * Overwrites any previously set parameters!
     *
     * @see getHref()
     *
     * @param  array|null $params           [optional] paramters as array
     *                                      ('name' => 'value'). Default is null
     *                                      which clears all params.
     * @return Zend_Navigation_Page_Mvc     fluent interface, returns self
     */
    public function setParams(array $params = null)
    {
        $this->clearParams();

        if (is_array($params)) {
            $this->addParams($params);
        }

        return $this;
    }

    /**
     * Set parameter (to use when assembling URL)
     *
     * URL option passed to the url action helper for assembling URLs.
     *
     * @see getHref()
     *
     * @param  string $name                 parameter name
     * @param  mixed $value                 parameter value
     * @return Zend_Navigation_Page_Mvc     fluent interface, returns self
     */
    public function setParam($name, $value)
    {
        $name                 = (string)$name;
        $this->_params[$name] = $value;

        $this->_hrefCache = null;

        return $this;
    }

    /**
     * Add multiple parameters (to use when assembling URL) at once
     *
     * URL options passed to the url action helper for assembling URLs.
     *
     * @see getHref()
     *
     * @param  array $params                paramters as array ('name' => 'value')
     * @return Zend_Navigation_Page_Mvc     fluent interface, returns self
     */
    public function addParams(array $params)
    {
        foreach ($params as $name => $value) {
            $this->setParam($name, $value);
        }

        return $this;
    }

    /**
     * Remove parameter (to use when assembling URL)
     *
     * @see getHref()
     *
     * @param  string $name
     * @return bool
     */
    public function removeParam($name)
    {
        if (array_key_exists($name, $this->_params)) {
            unset($this->_params[$name]);

            $this->_hrefCache = null;
            return true;
        }

        return false;
    }

    /**
     * Clear all parameters (to use when assembling URL)
     *
     * @see getHref()
     *
     * @return Zend_Navigation_Page_Mvc     fluent interface, returns self
     */
    public function clearParams()
    {
        $this->_params = array();

        $this->_hrefCache = null;
        return $this;
    }

    /**
     * Retrieve all parameters (to use when assembling URL)
     *
     * @see getHref()
     *
     * @return array parameters as array ('name' => 'value')
     */
    public function getParams()
    {
        return $this->_params;
    }

    /**
     * Retrieve a single parameter (to use when assembling URL)
     *
     * @see getHref()
     *
     * @param  string $name parameter name
     * @return mixed
     */
    public function getParam($name)
    {
        $name = (string) $name;

        if (!array_key_exists($name, $this->_params)) {
            return null;
        }

        return $this->_params[$name];
    }

    /**
     * Sets route name to use when assembling URL
     *
     * @see getHref()
     *
     * @param  string $route              route name to use when assembling URL
     * @return Zend_Navigation_Page_Mvc   fluent interface, returns self
     * @throws Zend_Navigation_Exception  if invalid $route is given
     */
    public function setRoute($route)
    {
        if (null !== $route && (!is_string($route) || strlen($route) < 1)) {
            #require_once 'Zend/Navigation/Exception.php';
            throw new Zend_Navigation_Exception(
                'Invalid argument: $route must be a non-empty string or null'
            );
        }

        $this->_route     = $route;
        $this->_hrefCache = null;

        return $this;
    }

    /**
     * Returns route name to use when assembling URL
     *
     * @see getHref()
     *
     * @return string  route name
     */
    public function getRoute()
    {
        return $this->_route;
    }

    /**
     * Sets whether params should be reset when assembling URL
     *
     * @see getHref()
     *
     * @param  bool $resetParams         whether params should be reset when
     *                                   assembling URL
     * @return Zend_Navigation_Page_Mvc  fluent interface, returns self
     */
    public function setResetParams($resetParams)
    {
        $this->_resetParams = (bool) $resetParams;
        $this->_hrefCache   = null;

        return $this;
    }

    /**
     * Returns whether params should be reset when assembling URL
     *
     * @see getHref()
     *
     * @return bool  whether params should be reset when assembling URL
     */
    public function getResetParams()
    {
        return $this->_resetParams;
    }

    /**
     * Sets whether href should be encoded when assembling URL
     *
     * @see getHref()
     *
     * @param $encodeUrl
     * @return Zend_Navigation_Page_Mvc fluent interface, returns self
     */
    public function setEncodeUrl($encodeUrl)
    {
        $this->_encodeUrl = (bool) $encodeUrl;
        $this->_hrefCache = null;

        return $this;
    }

    /**
     * Returns whether herf should be encoded when assembling URL
     *
     * @see getHref()
     *
     * @return bool whether herf should be encoded when assembling URL
     */
    public function getEncodeUrl()
    {
        return $this->_encodeUrl;
    }

    /**
     * Sets scheme to use when assembling URL
     *
     * @see getHref()
     *
     * @param  string|null $scheme        scheme
     * @throws Zend_Navigation_Exception
     * @return Zend_Navigation_Page_Mvc   fluent interface, returns self
     */
    public function setScheme($scheme)
    {
        if (null !== $scheme && !is_string($scheme)) {
            #require_once 'Zend/Navigation/Exception.php';
            throw new Zend_Navigation_Exception(
                'Invalid argument: $scheme must be a string or null'
            );
        }

        $this->_scheme = $scheme;
        return $this;
    }

    /**
     * Returns scheme to use when assembling URL
     *
     * @see getHref()
     *
     * @return string|null  scheme or null
     */
    public function getScheme()
    {
        return $this->_scheme;
    }

    /**
     * Sets action helper for assembling URLs
     *
     * @see getHref()
     *
     * @param  Zend_Controller_Action_Helper_Url $uh  URL helper
     * @return void
     */
    public static function setUrlHelper(Zend_Controller_Action_Helper_Url $uh)
    {
        self::$_urlHelper = $uh;
    }

    /**
     * Sets view helper for assembling URLs with schemes
     *
     * @see getHref()
     *
     * @param  Zend_View_Helper_ServerUrl $sh   scheme helper
     * @return void
     */
    public static function setSchemeHelper(Zend_View_Helper_ServerUrl $sh)
    {
        self::$_schemeHelper = $sh;
    }

    // Public methods:

    /**
     * Returns an array representation of the page
     *
     * @return array  associative array containing all page properties
     */
    public function toArray()
    {
        return array_merge(
            parent::toArray(),
            array(
                 'action'       => $this->getAction(),
                 'controller'   => $this->getController(),
                 'module'       => $this->getModule(),
                 'params'       => $this->getParams(),
                 'route'        => $this->getRoute(),
                 'reset_params' => $this->getResetParams(),
                 'encodeUrl'    => $this->getEncodeUrl(),
                 'scheme'       => $this->getScheme(),
            )
        );
    }
}
