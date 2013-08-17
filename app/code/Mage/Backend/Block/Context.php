<?php
/**
 * Backend block context
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Mage_Backend_Block_Context extends Mage_Core_Block_Context
{
    /**
     * @var Magento_AuthorizationInterface
     */
    protected $_authorization;

    /**
     * @param Mage_Core_Controller_Request_Http $request
     * @param Mage_Core_Model_Layout $layout
     * @param Mage_Core_Model_Event_Manager $eventManager
     * @param Mage_Core_Model_UrlInterface $urlBuilder
     * @param Mage_Core_Model_Translate $translator
     * @param Mage_Core_Model_CacheInterface $cache
     * @param Mage_Core_Model_View_DesignInterface $design
     * @param Mage_Core_Model_Session_Abstract $session
     * @param Mage_Core_Model_Store_Config $storeConfig
     * @param Mage_Core_Controller_Varien_Front $frontController
     * @param Mage_Core_Model_Factory_Helper $helperFactory
     * @param Mage_Core_Model_View_Url $viewUrl
     * @param Mage_Core_Model_View_Config $viewConfig
     * @param Magento_AuthorizationInterface $authorization
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Mage_Core_Controller_Request_Http $request,
        Mage_Core_Model_Layout $layout,
        Mage_Core_Model_Event_Manager $eventManager,
        Mage_Core_Model_UrlInterface $urlBuilder,
        Mage_Core_Model_Translate $translator,
        Mage_Core_Model_CacheInterface $cache,
        Mage_Core_Model_View_DesignInterface $design,
        Mage_Core_Model_Session_Abstract $session,
        Mage_Core_Model_Store_Config $storeConfig,
        Mage_Core_Controller_Varien_Front $frontController,
        Mage_Core_Model_Factory_Helper $helperFactory,
        Mage_Core_Model_View_Url $viewUrl,
        Mage_Core_Model_View_Config $viewConfig,
        Magento_AuthorizationInterface $authorization,
        array $data = array()
    ) {
        $this->_authorization = $authorization;
        parent::__construct($request, $layout, $eventManager, $urlBuilder, $translator, $cache, $design,
            $session, $storeConfig, $frontController, $helperFactory, $viewUrl, $viewConfig, $data
        );
    }

    /**
     * Retrieve Authorization
     *
     * @return \Magento_AuthorizationInterface
     */
    public function getAuthorization()
    {
        return $this->_authorization;
    }
}
