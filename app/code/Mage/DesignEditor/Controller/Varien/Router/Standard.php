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
     * @var Magento_ObjectManager
     */
    protected $_objectManager;

    /**
     * Routers that must not been matched
     *
     * @var array
     */
    protected $_excludedRouters = array('admin', 'vde');

    /**
     * @param Mage_Core_Controller_Varien_Action_Factory $controllerFactory
     * @param Magento_ObjectManager $objectManager
     * @param Magento_Filesystem $filesystem
     * @param Mage_Core_Model_App $app
     * @param string $areaCode
     * @param $baseController
     */
    public function __construct(
        Mage_Core_Controller_Varien_Action_Factory $controllerFactory,
        Magento_ObjectManager $objectManager,
        Magento_Filesystem $filesystem,
        Mage_Core_Model_App $app,
        $areaCode,
        $baseController
    ) {
        parent::__construct($controllerFactory, $filesystem, $app, $areaCode, $baseController);
        $this->_objectManager = $objectManager;
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
        if (!$this->_objectManager->get('Mage_DesignEditor_Helper_Data')->isVdeRequest($request)) {
            return null;
        }

        // user must be logged in admin area
        if (!$this->_objectManager->get('Mage_Backend_Model_Auth_Session')->isLoggedIn()) {
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
                $this->_objectManager->get('Mage_DesignEditor_Model_State')
                    ->update($this->_areaCode, $request, $controller);
                break;
            }
        }

        // set inline translation mode
        $this->_objectManager->get('Mage_DesignEditor_Helper_Data')->setTranslationMode($request);

        return $controller;
    }

    /**
     * Modify request path to imitate basic request
     *
     * @param Mage_Core_Controller_Request_Http $request
     * @return Mage_DesignEditor_Controller_Varien_Router_Standard
     */
    protected function _prepareVdeRequest(Mage_Core_Controller_Request_Http $request)
    {
        list($vdeFrontName, $designMode, $themeId) = explode('/', trim($request->getPathInfo(), '/'));
        $request->setAlias('editorMode', $designMode);
        $request->setAlias('themeId', (int)$themeId);
        $vdePath = implode('/', array($vdeFrontName, $designMode, $themeId));
        $noVdePath = substr($request->getPathInfo(), strlen($vdePath) + 1) ?: '/';
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
        $vdeNode = $this->_objectManager->get('Mage_Core_Model_Config')
            ->getNode(Mage_DesignEditor_Model_Area::AREA_VDE);
        if ($vdeNode) {
            $this->_objectManager->get('Mage_Core_Model_Config')->getNode(Mage_Core_Model_App_Area::AREA_FRONTEND)
                ->extend($vdeNode, true);
        }
    }
}
