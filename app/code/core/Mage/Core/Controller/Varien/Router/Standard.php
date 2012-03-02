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

class Mage_Core_Controller_Varien_Router_Standard extends Mage_Core_Controller_Varien_Router_Abstract
{
    protected $_modules = array();
    protected $_routes = array();
    protected $_dispatchData = array();

    public function collectRoutes($configArea, $useRouterName)
    {
        $routers = array();
        $routersConfigNode = Mage::getConfig()->getNode($configArea.'/routers');
        if($routersConfigNode) {
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
     * Match the request
     *
     * @param Zend_Controller_Request_Http $request
     * @return boolean
     */
    public function match(Zend_Controller_Request_Http $request)
    {
        //checking before even try to find out that current module
        //should use this router
        if (!$this->_beforeModuleMatch()) {
            return false;
        }

        $this->fetchDefault();

        $front = $this->getFront();
        $path = trim($request->getPathInfo(), '/');

        if ($path) {
            $p = explode('/', $path);
        } else {
            $p = explode('/', $this->_getDefaultPath());
        }

        // get module name
        if ($request->getModuleName()) {
            $module = $request->getModuleName();
        } else {
            if (!empty($p[0])) {
                $module = $p[0];
            } else {
                $module = $this->getFront()->getDefault('module');
                $request->setAlias(Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS, '');
            }
        }
        if (!$module) {
            if (Mage::app()->getStore()->isAdmin()) {
                $module = 'admin';
            } else {
                return false;
            }
        }

        /**
         * Searching router args by module name from route using it as key
         */
        $modules = $this->getModuleByFrontName($module);

        if ($modules === false) {
            return false;
        }

        //checkings after we foundout that this router should be used for current module
        if (!$this->_afterModuleMatch()) {
            return false;
        }

        /**
         * Going through modules to find appropriate controller
         */
        $found = false;
        foreach ($modules as $realModule) {
            $request->setRouteName($this->getRouteByFrontName($module));

            // get controller name
            if ($request->getControllerName()) {
                $controller = $request->getControllerName();
            } else {
                if (!empty($p[1])) {
                    $controller = $p[1];
                } else {
                    $controller = $front->getDefault('controller');
                    $request->setAlias(
                        Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS,
                        ltrim($request->getOriginalPathInfo(), '/')
                    );
                }
            }

            // get action name
            if (empty($action)) {
                if ($request->getActionName()) {
                    $action = $request->getActionName();
                } else {
                    $action = !empty($p[2]) ? $p[2] : $front->getDefault('action');
                }
            }

            //checking if this place should be secure
            $this->_checkShouldBeSecure($request, '/'.$module.'/'.$controller.'/'.$action);

            $controllerClassName = $this->_validateControllerClassName($realModule, $controller);
            if (!$controllerClassName) {
                continue;
            }

            // instantiate controller class
            $controllerInstance = Mage::getControllerInstance($controllerClassName, $request, $front->getResponse());

            if (!$controllerInstance->hasAction($action)) {
                continue;
            }

            $found = true;
            break;
        }

        /**
         * if we did not found any siutibul
         */
        if (!$found) {
            if ($this->_noRouteShouldBeApplied()) {
                $controller = 'index';
                $action = 'noroute';

                $controllerClassName = $this->_validateControllerClassName($realModule, $controller);
                if (!$controllerClassName) {
                    return false;
                }

                // instantiate controller class
                $controllerInstance = Mage::getControllerInstance($controllerClassName, $request,
                    $front->getResponse());

                if (!$controllerInstance->hasAction($action)) {
                    return false;
                }
            } else {
                return false;
            }
        }

        // set values only after all the checks are done
        $request->setModuleName($module);
        $request->setControllerName($controller);
        $request->setActionName($action);
        $request->setControllerModule($realModule);

        // set parameters from pathinfo
        for ($i = 3, $l = sizeof($p); $i < $l; $i += 2) {
            $request->setParam($p[$i], isset($p[$i+1]) ? urldecode($p[$i+1]) : '');
        }

        // dispatch action
        $request->setDispatched(true);
        $controllerInstance->dispatch($action);

        return true;
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
     * class and if evrything ok do include if needed and return of class name
     *
     * @return mixed
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
     * @param string $controllerFileName
     * @param string $controllerClassName
     * @return bool
     */
    protected function _includeControllerClass($controllerFileName, $controllerClassName)
    {
        if (!class_exists($controllerClassName, false)) {
            if (!file_exists($controllerFileName)) {
                return false;
            }
            include $controllerFileName;

            if (!class_exists($controllerClassName, false)) {
                throw Mage::exception('Mage_Core', Mage::helper('Mage_Core_Helper_Data')->__('Controller file was loaded but class does not exist'));
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

    public function getModuleByFrontName($frontName)
    {
        if (isset($this->_modules[$frontName])) {
            return $this->_modules[$frontName];
        }
        return false;
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
#echo "<pre>".print_r($p,1)."</pre>";
        return $p;
    }

    /**
     * Check if request URL should be secure
     *
     * Function redirects user to correct URL if needed
     *
     * @param Mage_Core_Controller_Request_Http $request
     * @param string $path
     * @return null
     */
    protected function _checkShouldBeSecure($request, $path = '')
    {
        if (!Mage::isInstalled() || $request->getPost()) {
            return;
        }

        if ($this->_shouldBeSecure($path) && !$request->isSecure()) {
            $url = $this->_getCurrentSecureUrl($request);

            Mage::app()->getFrontController()->getResponse()
                ->setRedirect($url)
                ->sendResponse();
            exit();
        }
    }

    protected function _getCurrentSecureUrl($request)
    {
        if ($alias = $request->getAlias(Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS)) {
            return Mage::getBaseUrl('link', true).ltrim($alias, '/');
        }

        return Mage::getBaseUrl('link', true).ltrim($request->getPathInfo(), '/');
    }

    protected function _shouldBeSecure($path)
    {
        return substr(Mage::getStoreConfig('web/unsecure/base_url'),0,5)==='https'
            || Mage::getStoreConfigFlag('web/secure/use_in_frontend')
            && substr(Mage::getStoreConfig('web/secure/base_url'),0,5)=='https'
            && Mage::getConfig()->shouldUrlBeSecure($path);
    }
}
