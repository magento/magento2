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
 * @category    Mage
 * @package     Mage_Core
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Core_Controller_Varien_Router_Base extends Mage_Core_Controller_Varien_Router_Abstract
{
    protected $_modules = array();
    protected $_routes = array();
    protected $_dispatchData = array();

    /**
     * List of required request parameters
     * Order sensitive
     * @var array
     */
    protected $_requiredParams = array(
        'moduleFrontName',
        'controllerName',
        'actionName',
    );

    /**
     * Current router belongs to the area
     *
     * @var string
     */
    protected $_area;

    /**
     * Base controller that belongs to area
     *
     * @var string
     */
    protected $_baseController = null;

    /**
     * @var Magento_ObjectManager
     */
    protected $_objectManager;

    /**
     * @param Magento_ObjectManager $objectManager
     * @param array $options
     */
    public function __construct(Magento_ObjectManager $objectManager, array $options = array())
    {
        $this->_objectManager = $objectManager;
        $this->_area           = isset($options['area']) ? $options['area'] : null;
        $this->_baseController = isset($options['base_controller']) ? $options['base_controller'] : null;

        if (is_null($this->_area) || is_null($this->_baseController)) {
            Mage::throwException("Not enough options to initialize router.");
        }
    }

    public function collectRoutes($configArea, $useRouterName)
    {
        $routers = array();
        $routersConfigNode = Mage::getConfig()->getNode($configArea.'/routers');
        if ($routersConfigNode) {
            $routers = $routersConfigNode->children();
        }
        foreach ($routers as $routerName=>$routerConfig) {
            $use = (string)$routerConfig->use;
            if ($use == $useRouterName) {
                $modules = array((string)$routerConfig->args->module);
                if ($routerConfig->args->modules) {
                    foreach ($routerConfig->args->modules->children() as $customModule) {
                        if ($customModule) {
                            if ($before = $customModule->getAttribute('before')) {
                                $position = array_search($before, $modules);
                                if ($position === false) {
                                    $position = 0;
                                }
                                array_splice($modules, $position, 0, (string)$customModule);
                            } elseif ($after = $customModule->getAttribute('after')) {
                                $position = array_search($after, $modules);
                                if ($position === false) {
                                    $position = count($modules);
                                }
                                array_splice($modules, $position+1, 0, (string)$customModule);
                            } else {
                                $modules[] = (string)$customModule;
                            }
                        }
                    }
                }

                $frontName = (string)$routerConfig->args->frontName;
                $this->addModule($frontName, $modules, $routerName);
            }
        }
    }

    public function fetchDefault()
    {
        $this->getFront()->setDefault(array(
            'module' => 'core',
            'controller' => 'index',
            'action' => 'index'
        ));
    }

    /**
     * checking if this admin if yes then we don't use this router
     *
     * @return bool
     */
    protected function _beforeModuleMatch()
    {
        if (Mage::app()->getStore()->isAdmin()) {
            return false;
        }
        return true;
    }

    /**
     * dummy call to pass through checking
     *
     * @return bool
     */
    protected function _afterModuleMatch()
    {
        return true;
    }

    /**
     * Match provided request and if matched - return corresponding controller
     *
     * @param Zend_Controller_Request_Http $request
     * @return Mage_Core_Controller_Front_Action|null
     */
    public function match(Zend_Controller_Request_Http $request)
    {
        //checking before even try to find out that current module
        //should use this router
        if (!$this->_beforeModuleMatch()) {
            return null;
        }

        $params = $this->_parseRequest($request);

        if (false == $this->_canProcess($params)) {
            return null;
        }

        $this->_objectManager->loadAreaConfiguration($this->_area);

        return $this->_matchController($request, $params);
    }

    /**
     * Check if router can process provided request
     *
     * @param array $params
     * @return bool
     */
    protected function _canProcess(array $params)
    {
        return true;
    }

    /**
     * Parse request URL params
     *
     * @param Zend_Controller_Request_Http $request
     * @return array
     */
    protected function _parseRequest(Zend_Controller_Request_Http $request)
    {
        $output = array();

        $path = trim($request->getPathInfo(), '/');

        $params = explode('/', ($path ? $path : $this->_getDefaultPath()));
        foreach ($this->_requiredParams as $paramName) {
            $output[$paramName] = array_shift($params);
        }

        for ($i = 0, $l = sizeof($params); $i < $l; $i += 2) {
            $output['variables'][$params[$i]] = isset($params[$i+1]) ? urldecode($params[$i+1]) : '';
        }
        return $output;
    }

    /**
     * Match module front name
     *
     * @param Zend_Controller_Request_Http $request
     * @param string $param
     * @return string|null
     */
    protected function _matchModuleFrontName(Zend_Controller_Request_Http $request, $param)
    {
        // get module name
        if ($request->getModuleName()) {
            $moduleFrontName = $request->getModuleName();
        } else {
            if (!empty($param)) {
                $moduleFrontName = $param;
            } else {
                $moduleFrontName = $this->getFront()->getDefault('module');
                $request->setAlias(Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS, '');
            }
        }
        if (!$moduleFrontName) {
            return null;
        }
        return $moduleFrontName;
    }

    /**
     * Match controller name
     *
     * @param Zend_Controller_Request_Http $request
     * @param string $param
     * @return string
     */
    protected function _matchControllerName(Zend_Controller_Request_Http $request,  $param)
    {
        if ($request->getControllerName()) {
            $controller = $request->getControllerName();
        } else {
            if (!empty($param)) {
                $controller = $param;
            } else {
                $controller = $this->getFront()->getDefault('controller');
                $request->setAlias(
                    Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS,
                    ltrim($request->getOriginalPathInfo(), '/')
                );
            }
        }
        return $controller;
    }

    /**
     * Match controller name
     *
     * @param Zend_Controller_Request_Http $request
     * @param string $param
     * @return string
     */
    protected function _matchActionName(Zend_Controller_Request_Http $request, $param)
    {
        if (empty($action)) {
            if ($request->getActionName()) {
                $action = $request->getActionName();
            } else {
                $action = !empty($param) ? $param : $this->getFront()->getDefault('action');
            }
        } else {
            $action = $param;
        }

        return $action;
    }

    /**
     * Get new controller instance
     *
     * @param $controllerClassName
     * @param Zend_Controller_Request_Http $request
     * @return Mage_Core_Controller_Varien_Action
     */
    protected function _getControllerInstance($controllerClassName, Zend_Controller_Request_Http $request)
    {
        return Mage::getControllerInstance($controllerClassName,
            $request,
            $this->getFront()->getResponse(),
            array('areaCode' => $this->_area)
        );
    }

    /**
     * Get not found controller instance
     *
     * @param $currentModuleName
     * @param Zend_Controller_Request_Http $request
     * @return Mage_Core_Controller_Varien_Action|null
     */
    protected function _getNotFoundControllerInstance($currentModuleName, Zend_Controller_Request_Http $request)
    {
        $controllerInstance = null;

        if ($this->_noRouteShouldBeApplied()) {
            $controller = 'index';
            $action = 'noroute';

            $controllerClassName = $this->_validateControllerClassName($currentModuleName, $controller);
            if (false == $controllerClassName) {
                return null;
            }

            if (false == $this->_validateControllerAction($controllerClassName, $action)) {
                return null;
            }

            // instantiate controller class
            $controllerInstance = $this->_getControllerInstance($controllerClassName, $request);
        } else {
            return null;
        }

        return $controllerInstance;
    }

    /**
     * Check whether action handler exists for provided handler
     *
     * @param string $controllerClassName
     * @param string $action
     * @return bool
     */
    protected function _validateControllerAction($controllerClassName, $action)
    {
        return method_exists($controllerClassName, $action . 'Action');
    }

    /**
     * Create matched controller instance
     *
     * @param Zend_Controller_Request_Http $request
     * @param array $params
     * @return Mage_Core_Controller_Front_Action|null
     */
    protected function _matchController(Zend_Controller_Request_Http $request, array $params)
    {
        $this->fetchDefault();

        $moduleFrontName = $this->_matchModuleFrontName($request, $params['moduleFrontName']);
        if (empty($moduleFrontName)) {
            return null;
        }

        /**
         * Searching router args by module name from route using it as key
         */
        $modules = $this->getModulesByFrontName($moduleFrontName);

        if (empty($modules) === true) {
            return null;
        }

        // checks after we found out that this router should be used for current module
        if (!$this->_afterModuleMatch()) {
            return null;
        }

        /**
         * Going through modules to find appropriate controller
         */
        $found = false;
        $currentModuleName = null;
        $controller = null;
        $action = null;
        $controllerInstance = null;

        foreach ($modules as $moduleName) {
            $currentModuleName = $moduleName;

            $request->setRouteName($this->getRouteByFrontName($moduleFrontName));

            $controller = $this->_matchControllerName($request, $params['controllerName']);

            $action = $this->_matchActionName($request, $params['actionName']);

            //checking if this place should be secure
            $this->_checkShouldBeSecure($request, '/'.$moduleFrontName.'/'.$controller.'/'.$action);

            $controllerClassName = $this->_validateControllerClassName($moduleName, $controller);
            if (false == $controllerClassName) {
                continue;
            }

            if (false === $this->_validateControllerAction($controllerClassName, $action)) {
                continue;
            }

            Mage::getConfig()->setCurrentAreaCode($this->_area);
            // instantiate controller class
            $controllerInstance = $this->_getControllerInstance($controllerClassName, $request);

            $found = true;
            break;
        }

        /**
         * if we did not found any suitable
         */
        if (false == $found) {
            $controllerInstance = $this->_getNotFoundControllerInstance($currentModuleName, $request);
            if (is_null($controllerInstance)) {
                return null;
            }
        }

        // set values only after all the checks are done
        $request->setModuleName($moduleFrontName);
        $request->setControllerName($controller);
        $request->setActionName($action);
        $request->setControllerModule($currentModuleName);
        if (isset($params['variables'])) {
            $request->setParams($params['variables']);
        }
        return $controllerInstance;
    }

    /**
     * Get router default request path
     * @return string
     */
    protected function _getDefaultPath()
    {
        return Mage::getStoreConfig('web/default/front');
    }

    /**
     * Allow to control if we need to enable no route functionality in current router
     *
     * @return bool
     */
    protected function _noRouteShouldBeApplied()
    {
        return false;
    }

    /**
     * Generating and validating class file name,
     * class and if everything ok do include if needed and return of class name
     *
     * @param $realModule
     * @param $controller
     * @return bool|string
     */
    protected function _validateControllerClassName($realModule, $controller)
    {
        $controllerFileName = $this->getControllerFileName($realModule, $controller);
        if (!$this->validateControllerFileName($controllerFileName)) {
            return false;
        }

        $controllerClassName = $this->getControllerClassName($realModule, $controller);
        if (!$controllerClassName) {
            return false;
        }

        // include controller file if needed
        if (!$this->_includeControllerClass($controllerFileName, $controllerClassName)) {
            return false;
        }

        return $controllerClassName;
    }

    /**
     * Include the file containing controller class if this class is not defined yet
     *
     * @param $controllerFileName
     * @param $controllerClassName
     * @return bool
     * @throws Mage_Core_Exception
     */
    protected function _includeControllerClass($controllerFileName, $controllerClassName)
    {
        if (!class_exists($controllerClassName, false)) {
            if (!file_exists($controllerFileName)) {
                return false;
            }
            include $controllerFileName;

            if (!class_exists($controllerClassName, false)) {
                throw Mage::exception('Mage_Core',
                    Mage::helper('Mage_Core_Helper_Data')->__('Controller file was loaded but class does not exist')
                );
            }
        }
        return true;
    }

    public function addModule($frontName, $moduleName, $routeName)
    {
        $this->_modules[$frontName] = $moduleName;
        $this->_routes[$routeName] = $frontName;
        return $this;
    }

    /**
     * Retrieve list of modules subscribed to given frontName
     *
     * @param string $frontName
     * @return array
     */
    public function getModulesByFrontName($frontName)
    {
        $modules = array();
        if (isset($this->_modules[$frontName])) {
            if (false === is_array($this->_modules[$frontName])) {
                $modules = array($this->_modules[$frontName]);
            } else {
                $modules = $this->_modules[$frontName];
            }
        }
        return $modules;
    }

    public function getModuleByName($moduleName, $modules)
    {
        foreach ($modules as $module) {
            if ($moduleName === $module || (is_array($module)
                    && $this->getModuleByName($moduleName, $module))) {
                return true;
            }
        }
        return false;
    }

    public function getFrontNameByRoute($routeName)
    {
        if (isset($this->_routes[$routeName])) {
            return $this->_routes[$routeName];
        }
        return false;
    }

    public function getRouteByFrontName($frontName)
    {
        return array_search($frontName, $this->_routes);
    }

    public function getControllerFileName($realModule, $controller)
    {
        $parts = explode('_', $realModule);
        $realModule = implode('_', array_splice($parts, 0, 2));
        $file = Mage::getModuleDir('controllers', $realModule);
        if (count($parts)) {
            $file .= DS . implode(DS, $parts);
        }
        $file .= DS.uc_words($controller, DS).'Controller.php';
        return $file;
    }

    public function validateControllerFileName($fileName)
    {
        if ($fileName && is_readable($fileName) && false===strpos($fileName, '//')) {
            return true;
        }
        return false;
    }

    public function getControllerClassName($realModule, $controller)
    {
        $class = $realModule.'_'.uc_words($controller).'Controller';
        return $class;
    }

    public function rewrite(array $p)
    {
        $rewrite = Mage::getConfig()->getNode('global/rewrite');
        if ($module = $rewrite->{$p[0]}) {
            if (!$module->children()) {
                $p[0] = trim((string)$module);
            }
        }
        if (isset($p[1]) && ($controller = $rewrite->{$p[0]}->{$p[1]})) {
            if (!$controller->children()) {
                $p[1] = trim((string)$controller);
            }
        }
        if (isset($p[2]) && ($action = $rewrite->{$p[0]}->{$p[1]}->{$p[2]})) {
            if (!$action->children()) {
                $p[2] = trim((string)$action);
            }
        }

        return $p;
    }

    /**
     * Check that request uses https protocol if it should.
     * Function redirects user to correct URL if needed.
     *
     * @param Mage_Core_Controller_Request_Http $request
     * @param string $path
     * @return void
     */
    protected function _checkShouldBeSecure($request, $path = '')
    {
        if (!Mage::isInstalled() || $request->getPost()) {
            return;
        }

        if ($this->_shouldBeSecure($path) && !$request->isSecure()) {
            $url = $this->_getCurrentSecureUrl($request);
            if ($this->_shouldRedirectToSecure()) {
                $url = Mage::getSingleton('Mage_Core_Model_Url')->getRedirectUrl($url);
            }

            Mage::app()->getFrontController()->getResponse()
                ->setRedirect($url)
                ->sendResponse();
            exit;
        }
    }

    /**
     * Check whether redirect url should be used for secure routes
     *
     * @return bool
     */
    protected function _shouldRedirectToSecure()
    {
        return Mage::app()->getUseSessionInUrl();
    }

    protected function _getCurrentSecureUrl($request)
    {
        if ($alias = $request->getAlias(Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS)) {
            return Mage::getBaseUrl('link', true).ltrim($alias, '/');
        }

        return Mage::getBaseUrl('link', true).ltrim($request->getPathInfo(), '/');
    }

    /**
     * Check whether URL for corresponding path should use https protocol
     *
     * @param string $path
     * @return bool
     */
    protected function _shouldBeSecure($path)
    {
        return substr(Mage::getStoreConfig('web/unsecure/base_url'), 0, 5) === 'https'
            || Mage::getStoreConfigFlag('web/secure/use_in_frontend')
                && substr(Mage::getStoreConfig('web/secure/base_url'), 0, 5) == 'https'
                && Mage::getConfig()->shouldUrlBeSecure($path);
    }
}
