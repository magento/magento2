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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Mvc.php 20096 2010-01-06 02:05:09Z bkarwin $
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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
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
        if (!$this->_active) {
            $front = Zend_Controller_Front::getInstance();
            $reqParams = $front->getRequest()->getParams();

            if (!array_key_exists('module', $reqParams)) {
                $reqParams['module'] = $front->getDefaultModule();
            }

            $myParams = $this->_params;

            if (null !== $this->_module) {
                $myParams['module'] = $this->_module;
            } else {
                $myParams['module'] = $front->getDefaultModule();
            }

            if (null !== $this->_controller) {
                $myParams['controller'] = $this->_controller;
            } else {
                $myParams['controller'] = $front->getDefaultControllerName();
            }

            if (null !== $this->_action) {
                $myParams['action'] = $this->_action;
            } else {
                $myParams['action'] = $front->getDefaultAction();
            }

            if (count(array_intersect_assoc($reqParams, $myParams)) ==
                count($myParams)) {
                $this->_active = true;
                return true;
            }
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

        $url = self::$_urlHelper->url($params,
                                      $this->getRoute(),
                                      $this->getResetParams());

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
                    'Invalid argument: $action must be a string or null');
        }

        $this->_action = $action;
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
                    'Invalid argument: $controller must be a string or null');
        }

        $this->_controller = $controller;
        $this->_hrefCache = null;
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
                    'Invalid argument: $module must be a string or null');
        }

        $this->_module = $module;
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
     * Sets params to use when assembling URL
     *
     * @see getHref()
     *
     * @param  array|null $params        [optional] page params. Default is null
     *                                   which sets no params.
     * @return Zend_Navigation_Page_Mvc  fluent interface, returns self
     */
    public function setParams(array $params = null)
    {
        if (null === $params) {
            $this->_params = array();
        } else {
            // TODO: do this more intelligently?
            $this->_params = $params;
        }

        $this->_hrefCache = null;
        return $this;
    }

    /**
     * Returns params to use when assembling URL
     *
     * @see getHref()
     *
     * @return array  page params
     */
    public function getParams()
    {
        return $this->_params;
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
                 'Invalid argument: $route must be a non-empty string or null');
        }

        $this->_route = $route;
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
        $this->_hrefCache = null;
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
                'reset_params' => $this->getResetParams()
            ));
    }
}
