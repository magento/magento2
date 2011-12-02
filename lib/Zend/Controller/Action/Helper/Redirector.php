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
 * @package    Zend_Controller
 * @subpackage Zend_Controller_Action_Helper
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Redirector.php 23248 2010-10-26 12:45:52Z matthew $
 */

/**
 * @see Zend_Controller_Action_Helper_Abstract
 */
#require_once 'Zend/Controller/Action/Helper/Abstract.php';

/**
 * @category   Zend
 * @package    Zend_Controller
 * @subpackage Zend_Controller_Action_Helper
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Controller_Action_Helper_Redirector extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * HTTP status code for redirects
     * @var int
     */
    protected $_code = 302;

    /**
     * Whether or not calls to _redirect() should exit script execution
     * @var boolean
     */
    protected $_exit = true;

    /**
     * Whether or not _redirect() should attempt to prepend the base URL to the
     * passed URL (if it's a relative URL)
     * @var boolean
     */
    protected $_prependBase = true;

    /**
     * Url to which to redirect
     * @var string
     */
    protected $_redirectUrl = null;

    /**
     * Whether or not to use an absolute URI when redirecting
     * @var boolean
     */
    protected $_useAbsoluteUri = false;

    /**
     * Whether or not to close the session before exiting
     * @var boolean
     */
    protected $_closeSessionOnExit = true;

    /**
     * Retrieve HTTP status code to emit on {@link _redirect()} call
     *
     * @return int
     */
    public function getCode()
    {
        return $this->_code;
    }

    /**
     * Validate HTTP status redirect code
     *
     * @param  int $code
     * @throws Zend_Controller_Action_Exception on invalid HTTP status code
     * @return true
     */
    protected function _checkCode($code)
    {
        $code = (int)$code;
        if ((300 > $code) || (307 < $code) || (304 == $code) || (306 == $code)) {
            #require_once 'Zend/Controller/Action/Exception.php';
            throw new Zend_Controller_Action_Exception('Invalid redirect HTTP status code (' . $code  . ')');
        }

        return true;
    }

    /**
     * Retrieve HTTP status code for {@link _redirect()} behaviour
     *
     * @param  int $code
     * @return Zend_Controller_Action_Helper_Redirector Provides a fluent interface
     */
    public function setCode($code)
    {
        $this->_checkCode($code);
        $this->_code = $code;
        return $this;
    }

    /**
     * Retrieve flag for whether or not {@link _redirect()} will exit when finished.
     *
     * @return boolean
     */
    public function getExit()
    {
        return $this->_exit;
    }

    /**
     * Retrieve exit flag for {@link _redirect()} behaviour
     *
     * @param  boolean $flag
     * @return Zend_Controller_Action_Helper_Redirector Provides a fluent interface
     */
    public function setExit($flag)
    {
        $this->_exit = ($flag) ? true : false;
        return $this;
    }

    /**
     * Retrieve flag for whether or not {@link _redirect()} will prepend the
     * base URL on relative URLs
     *
     * @return boolean
     */
    public function getPrependBase()
    {
        return $this->_prependBase;
    }

    /**
     * Retrieve 'prepend base' flag for {@link _redirect()} behaviour
     *
     * @param  boolean $flag
     * @return Zend_Controller_Action_Helper_Redirector Provides a fluent interface
     */
    public function setPrependBase($flag)
    {
        $this->_prependBase = ($flag) ? true : false;
        return $this;
    }

    /**
     * Retrieve flag for whether or not {@link redirectAndExit()} shall close the session before
     * exiting.
     *
     * @return boolean
     */
    public function getCloseSessionOnExit()
    {
        return $this->_closeSessionOnExit;
    }

    /**
     * Set flag for whether or not {@link redirectAndExit()} shall close the session before exiting.
     *
     * @param  boolean $flag
     * @return Zend_Controller_Action_Helper_Redirector Provides a fluent interface
     */
    public function setCloseSessionOnExit($flag)
    {
        $this->_closeSessionOnExit = ($flag) ? true : false;
        return $this;
    }

    /**
     * Return use absolute URI flag
     *
     * @return boolean
     */
    public function getUseAbsoluteUri()
    {
        return $this->_useAbsoluteUri;
    }

    /**
     * Set use absolute URI flag
     *
     * @param  boolean $flag
     * @return Zend_Controller_Action_Helper_Redirector Provides a fluent interface
     */
    public function setUseAbsoluteUri($flag = true)
    {
        $this->_useAbsoluteUri = ($flag) ? true : false;
        return $this;
    }

    /**
     * Set redirect in response object
     *
     * @return void
     */
    protected function _redirect($url)
    {
        if ($this->getUseAbsoluteUri() && !preg_match('#^(https?|ftp)://#', $url)) {
            $host  = (isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'');
            $proto = (isset($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=="off") ? 'https' : 'http';
            $port  = (isset($_SERVER['SERVER_PORT'])?$_SERVER['SERVER_PORT']:80);
            $uri   = $proto . '://' . $host;
            if ((('http' == $proto) && (80 != $port)) || (('https' == $proto) && (443 != $port))) {
                // do not append if HTTP_HOST already contains port
                if (strrchr($host, ':') === false) {
                    $uri .= ':' . $port;
                }
            }
            $url = $uri . '/' . ltrim($url, '/');
        }
        $this->_redirectUrl = $url;
        $this->getResponse()->setRedirect($url, $this->getCode());
    }

    /**
     * Retrieve currently set URL for redirect
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        return $this->_redirectUrl;
    }

    /**
     * Determine if the baseUrl should be prepended, and prepend if necessary
     *
     * @param  string $url
     * @return string
     */
    protected function _prependBase($url)
    {
        if ($this->getPrependBase()) {
            $request = $this->getRequest();
            if ($request instanceof Zend_Controller_Request_Http) {
                $base = rtrim($request->getBaseUrl(), '/');
                if (!empty($base) && ('/' != $base)) {
                    $url = $base . '/' . ltrim($url, '/');
                } else {
                    $url = '/' . ltrim($url, '/');
                }
            }
        }

        return $url;
    }

    /**
     * Set a redirect URL of the form /module/controller/action/params
     *
     * @param  string $action
     * @param  string $controller
     * @param  string $module
     * @param  array  $params
     * @return void
     */
    public function setGotoSimple($action, $controller = null, $module = null, array $params = array())
    {
        $dispatcher = $this->getFrontController()->getDispatcher();
        $request    = $this->getRequest();
        $curModule  = $request->getModuleName();
        $useDefaultController = false;

        if (null === $controller && null !== $module) {
            $useDefaultController = true;
        }

        if (null === $module) {
            $module = $curModule;
        }

        if ($module == $dispatcher->getDefaultModule()) {
            $module = '';
        }

        if (null === $controller && !$useDefaultController) {
            $controller = $request->getControllerName();
            if (empty($controller)) {
                $controller = $dispatcher->getDefaultControllerName();
            }
        }

        $params['module']     = $module;
        $params['controller'] = $controller;
        $params['action']     = $action;

        $router = $this->getFrontController()->getRouter();
        $url    = $router->assemble($params, 'default', true);

        $this->_redirect($url);
    }

    /**
     * Build a URL based on a route
     *
     * @param  array   $urlOptions
     * @param  string  $name Route name
     * @param  boolean $reset
     * @param  boolean $encode
     * @return void
     */
    public function setGotoRoute(array $urlOptions = array(), $name = null, $reset = false, $encode = true)
    {
        $router = $this->getFrontController()->getRouter();
        $url    = $router->assemble($urlOptions, $name, $reset, $encode);

        $this->_redirect($url);
    }

    /**
     * Set a redirect URL string
     *
     * By default, emits a 302 HTTP status header, prepends base URL as defined
     * in request object if url is relative, and halts script execution by
     * calling exit().
     *
     * $options is an optional associative array that can be used to control
     * redirect behaviour. The available option keys are:
     * - exit: boolean flag indicating whether or not to halt script execution when done
     * - prependBase: boolean flag indicating whether or not to prepend the base URL when a relative URL is provided
     * - code: integer HTTP status code to use with redirect. Should be between 300 and 307.
     *
     * _redirect() sets the Location header in the response object. If you set
     * the exit flag to false, you can override this header later in code
     * execution.
     *
     * If the exit flag is true (true by default), _redirect() will write and
     * close the current session, if any.
     *
     * @param  string $url
     * @param  array  $options
     * @return void
     */
    public function setGotoUrl($url, array $options = array())
    {
        // prevent header injections
        $url = str_replace(array("\n", "\r"), '', $url);

        if (null !== $options) {
            if (isset($options['exit'])) {
                $this->setExit(($options['exit']) ? true : false);
            }
            if (isset($options['prependBase'])) {
                $this->setPrependBase(($options['prependBase']) ? true : false);
            }
            if (isset($options['code'])) {
                $this->setCode($options['code']);
            }
        }

        // If relative URL, decide if we should prepend base URL
        if (!preg_match('|^[a-z]+://|', $url)) {
            $url = $this->_prependBase($url);
        }

        $this->_redirect($url);
    }

    /**
     * Perform a redirect to an action/controller/module with params
     *
     * @param  string $action
     * @param  string $controller
     * @param  string $module
     * @param  array  $params
     * @return void
     */
    public function gotoSimple($action, $controller = null, $module = null, array $params = array())
    {
        $this->setGotoSimple($action, $controller, $module, $params);

        if ($this->getExit()) {
            $this->redirectAndExit();
        }
    }

    /**
     * Perform a redirect to an action/controller/module with params, forcing an immdiate exit
     *
     * @param  mixed $action
     * @param  mixed $controller
     * @param  mixed $module
     * @param  array $params
     * @return void
     */
    public function gotoSimpleAndExit($action, $controller = null, $module = null, array $params = array())
    {
        $this->setGotoSimple($action, $controller, $module, $params);
        $this->redirectAndExit();
    }

    /**
     * Redirect to a route-based URL
     *
     * Uses route's assemble method tobuild the URL; route is specified by $name;
     * default route is used if none provided.
     *
     * @param  array   $urlOptions Array of key/value pairs used to assemble URL
     * @param  string  $name
     * @param  boolean $reset
     * @param  boolean $encode
     * @return void
     */
    public function gotoRoute(array $urlOptions = array(), $name = null, $reset = false, $encode = true)
    {
        $this->setGotoRoute($urlOptions, $name, $reset, $encode);

        if ($this->getExit()) {
            $this->redirectAndExit();
        }
    }

    /**
     * Redirect to a route-based URL, and immediately exit
     *
     * Uses route's assemble method tobuild the URL; route is specified by $name;
     * default route is used if none provided.
     *
     * @param  array   $urlOptions Array of key/value pairs used to assemble URL
     * @param  string  $name
     * @param  boolean $reset
     * @return void
     */
    public function gotoRouteAndExit(array $urlOptions = array(), $name = null, $reset = false)
    {
        $this->setGotoRoute($urlOptions, $name, $reset);
        $this->redirectAndExit();
    }

    /**
     * Perform a redirect to a url
     *
     * @param  string $url
     * @param  array  $options
     * @return void
     */
    public function gotoUrl($url, array $options = array())
    {
        $this->setGotoUrl($url, $options);

        if ($this->getExit()) {
            $this->redirectAndExit();
        }
    }

    /**
     * Set a URL string for a redirect, perform redirect, and immediately exit
     *
     * @param  string $url
     * @param  array  $options
     * @return void
     */
    public function gotoUrlAndExit($url, array $options = array())
    {
        $this->setGotoUrl($url, $options);
        $this->redirectAndExit();
    }

    /**
     * exit(): Perform exit for redirector
     *
     * @return void
     */
    public function redirectAndExit()
    {
        if ($this->getCloseSessionOnExit()) {
            // Close session, if started
            if (class_exists('Zend_Session', false) && Zend_Session::isStarted()) {
                Zend_Session::writeClose();
            } elseif (isset($_SESSION)) {
                session_write_close();
            }
        }

        $this->getResponse()->sendHeaders();
        exit();
    }

    /**
     * direct(): Perform helper when called as
     * $this->_helper->redirector($action, $controller, $module, $params)
     *
     * @param  string $action
     * @param  string $controller
     * @param  string $module
     * @param  array  $params
     * @return void
     */
    public function direct($action, $controller = null, $module = null, array $params = array())
    {
        $this->gotoSimple($action, $controller, $module, $params);
    }

    /**
     * Overloading
     *
     * Overloading for old 'goto', 'setGoto', and 'gotoAndExit' methods
     *
     * @param  string $method
     * @param  array $args
     * @return mixed
     * @throws Zend_Controller_Action_Exception for invalid methods
     */
    public function __call($method, $args)
    {
        $method = strtolower($method);
        if ('goto' == $method) {
            return call_user_func_array(array($this, 'gotoSimple'), $args);
        }
        if ('setgoto' == $method) {
            return call_user_func_array(array($this, 'setGotoSimple'), $args);
        }
        if ('gotoandexit' == $method) {
            return call_user_func_array(array($this, 'gotoSimpleAndExit'), $args);
        }

        #require_once 'Zend/Controller/Action/Exception.php';
        throw new Zend_Controller_Action_Exception(sprintf('Invalid method "%s" called on redirector', $method));
    }
}
