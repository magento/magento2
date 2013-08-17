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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Core_Controller_Varien_Action_Context implements Magento_ObjectManager_ContextInterface
{
    /**
     * @var Mage_Core_Controller_Request_Http
     */
    protected $_request;

    /**
     * @var Mage_Core_Controller_Response_Http
     */
    protected $_response;

    /**
     * @var Magento_ObjectManager
     */
    protected $_objectManager;

    /**
     * @var Mage_Core_Controller_Varien_Front
     */
    protected $_frontController = null;

    /**
     * @var Mage_Core_Model_Layout_Factory
     */
    protected $_layoutFactory;

    /**
     * @var Mage_Core_Model_Event_Manager
     */
    protected $_eventManager;

    /**
     * @param Mage_Core_Controller_Request_Http $request
     * @param Mage_Core_Controller_Response_Http $response
     * @param Magento_ObjectManager $objectManager
     * @param Mage_Core_Controller_Varien_Front $frontController
     * @param Mage_Core_Model_Layout_Factory $layoutFactory
     * @param Mage_Core_Model_Event_Manager $eventManager
     */
    public function __construct(
        Mage_Core_Controller_Request_Http $request,
        Mage_Core_Controller_Response_Http $response,
        Magento_ObjectManager $objectManager,
        Mage_Core_Controller_Varien_Front $frontController,
        Mage_Core_Model_Layout_Factory $layoutFactory,
        Mage_Core_Model_Event_Manager $eventManager
    ) {
        $this->_request         = $request;
        $this->_response        = $response;
        $this->_objectManager   = $objectManager;
        $this->_frontController = $frontController;
        $this->_layoutFactory   = $layoutFactory;
        $this->_eventManager    = $eventManager;
    }

    /**
     * @return \Mage_Core_Controller_Varien_Front
     */
    public function getFrontController()
    {
        return $this->_frontController;
    }

    /**
     * @return \Mage_Core_Model_Layout_Factory
     */
    public function getLayoutFactory()
    {
        return $this->_layoutFactory;
    }

    /**
     * @return \Magento_ObjectManager
     */
    public function getObjectManager()
    {
        return $this->_objectManager;
    }

    /**
     * @return \Mage_Core_Controller_Request_Http
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * @return \Mage_Core_Controller_Response_Http
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * @return \Mage_Core_Model_Event_Manager
     */
    public function getEventManager()
    {
        return $this->_eventManager;
    }
}