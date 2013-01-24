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
 * @package     Mage_DesignEditor
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_DesignEditor_Controller_Varien_Router_Standard extends Mage_Core_Controller_Varien_Router_Base
{
    /**
     * @var Mage_Backend_Model_Auth_Session
     */
    protected $_backendSession;

    /**
     * @var Mage_DesignEditor_Helper_Data
     */
    protected $_helper;

    /**
     * Routers that must not been matched
     *
     * @var array
     */
    protected $_excludedRouters = array('admin', 'vde');

    /**
     * Layout factory
     *
     * @var Mage_DesignEditor_Model_State
     */
    protected $_editorState;

    /**
     * Configuration model
     *
     * @var Mage_Core_Model_Config
     */
    protected $_configuration;

    /**
     * @param Mage_Core_Controller_Varien_Action_Factory $controllerFactory
     * @param Magento_Filesystem $filesystem
     * @param Mage_Core_Model_App $app
     * @param string $areaCode
     * @param string $baseController
     * @param Mage_Backend_Model_Auth_Session $backendSession
     * @param Mage_DesignEditor_Helper_Data $helper
     * @param Mage_DesignEditor_Model_State $editorState
     * @param Mage_Core_Model_Config $configuration
     */
    public function __construct(
        Mage_Core_Controller_Varien_Action_Factory $controllerFactory,
        Magento_Filesystem $filesystem,
        Mage_Core_Model_App $app,
        $areaCode,
        $baseController,
        Mage_Backend_Model_Auth_Session $backendSession,
        Mage_DesignEditor_Helper_Data $helper,
        Mage_DesignEditor_Model_State $editorState,
        Mage_Core_Model_Config $configuration
    ) {
        parent::__construct($controllerFactory, $filesystem, $app, $areaCode, $baseController);

        $this->_backendSession = $backendSession;
        $this->_helper         = $helper;
        $this->_editorState    = $editorState;
        $this->_configuration  = $configuration;
    }

    /**
     * Match provided request and if matched - return corresponding controller
     *
     * @param Mage_Core_Controller_Request_Http $request
     * @return Mage_Core_Controller_Front_Action|null
     */
    public function match(Mage_Core_Controller_Request_Http $request)
    {
        // if URL has VDE prefix
        if (!$this->_isVdeRequest($request)) {
            return null;
        }

        // user must be logged in admin area
        if (!$this->_backendSession->isLoggedIn()) {
            return null;
        }

        // override VDE configuration
        $this->_overrideConfiguration();

        // prepare request to imitate
        $this->_prepareVdeRequest($request);

        // apply rewrites
        $this->getFront()->applyRewrites($request);

        // match routers
        $controller = null;
        $routers = $this->_getMatchedRouters();
        /** @var $router Mage_Core_Controller_Varien_Router_Abstract */
        foreach ($routers as $router) {
            /** @var $controller Mage_Core_Controller_Varien_ActionAbstract */
            $controller = $router->match($request);
            if ($controller) {
                $this->_editorState->update($this->_areaCode, $request, $controller);
                break;
            }
        }

        return $controller;
    }

    /**
     * Check if URL has vde prefix
     *
     * @param Mage_Core_Controller_Request_Http $request
     * @return bool
     */
    protected function _isVdeRequest(Mage_Core_Controller_Request_Http $request)
    {
        $url = trim($request->getOriginalPathInfo(), '/');
        $vdeFrontName = $this->_helper->getFrontName();
        return $url == $vdeFrontName || strpos($url, $vdeFrontName . '/') === 0;
    }

    /**
     * Modify request path to imitate basic request
     *
     * @param Mage_Core_Controller_Request_Http $request
     * @return Mage_DesignEditor_Controller_Varien_Router_Standard
     */
    protected function _prepareVdeRequest(Mage_Core_Controller_Request_Http $request)
    {
        $vdeFrontName = $this->_helper->getFrontName();
        $noVdePath = substr($request->getPathInfo(), strlen($vdeFrontName) + 1) ?: '/';
        $request->setPathInfo($noVdePath);
        return $this;
    }

    /**
     * Returns list of routers that must been tried to match
     *
     * @return array
     */
    protected function _getMatchedRouters()
    {
        $routers = $this->getFront()->getRouters();
        foreach (array_keys($routers) as $name) {
            if (in_array($name, $this->_excludedRouters)) {
                unset($routers[$name]);
            }
        }
        return $routers;
    }

    /**
     * Override frontend configuration with VDE area data
     */
    protected function _overrideConfiguration()
    {
        $vdeNode = $this->_configuration->getNode(Mage_DesignEditor_Model_Area::AREA_VDE);
        if ($vdeNode) {
            $this->_configuration->getNode(Mage_Core_Model_App_Area::AREA_FRONTEND)
                ->extend($vdeNode, true);
        }
    }
}
