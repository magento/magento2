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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require_once __DIR__ . '/../../../../../../../app/code/Mage/Adminhtml/controllers/CacheController.php';

class Mage_Adminhtml_CacheControllerTest extends PHPUnit_Framework_TestCase
{
    public function testCleanMediaAction()
    {
        // Wire object with mocks
        $context = $this->getMock('Mage_Backend_Controller_Context', array(), array(), '', false);

        $request = $this->getMock('Mage_Core_Controller_Request_Http', array(), array(), '', false);
        $context->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $response = $this->getMock('Mage_Core_Controller_Response_Http', array(), array(), '', false);
        $context->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($response));

        $objectManager = $this->getMock('Magento_ObjectManager');
        $context->expects($this->any())
            ->method('getObjectManager')
            ->will($this->returnValue($objectManager));

        $frontController = $this->getMock('Mage_Core_Controller_Varien_Front', array(), array(), '', false);
        $context->expects($this->any())
            ->method('getFrontController')
            ->will($this->returnValue($frontController));

        $eventManager = $this->getMock('Mage_Core_Model_Event_Manager', array(), array(), '', false);
        $eventManager->expects($this->once())
            ->method('dispatch')
            ->with('clean_media_cache_after');
        $context->expects($this->any())
            ->method('getEventManager')
            ->will($this->returnValue($eventManager));

        $backendHelper = $this->getMock('Mage_Backend_Helper_Data', array(), array(), '', false);
        $context->expects($this->any())
            ->method('getHelper')
            ->will($this->returnValue($backendHelper));

        $cache = $this->getMock('Mage_Core_Model_Cache', array(), array(), '', false);
        $cacheTypes = $this->getMock('Mage_Core_Model_Cache_Types', array(), array(), '', false);
        $cacheFrontendPool = $this->getMock('Mage_Core_Model_Cache_Frontend_Pool', array(), array(), '', false);

        $controller = new Mage_Adminhtml_CacheController(
            $context,
            $cache,
            $cacheTypes,
            $cacheFrontendPool,
            null
        );

        // Setup expectations
        $mergeService = $this->getMock('Mage_Core_Model_Page_Asset_MergeService', array(), array(), '', false);
        $mergeService->expects($this->once())
            ->method('cleanMergedJsCss');
        $helper = $this->getMock('Mage_Adminhtml_Helper_Data', array(), array(), '', false);
        $helper->expects($this->once())
            ->method('__')
            ->with('The JavaScript/CSS cache has been cleaned.')
            ->will($this->returnValue('Translated value'));
        $session = $this->getMock('Mage_Adminhtml_Model_Session', array(), array(), '', false);
        $session->expects($this->once())
            ->method('addSuccess')
            ->with('Translated value');

        $valueMap = array(
            array('Mage_Core_Model_Page_Asset_MergeService', $mergeService),
            array('Mage_Adminhtml_Helper_Data', $helper),
            array('Mage_Adminhtml_Model_Session', $session),
        );
        $objectManager->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap($valueMap));

        $backendHelper->expects($this->once())
            ->method('getUrl')
            ->with('*/*')
            ->will($this->returnValue('redirect_url'));
        $response->expects($this->once())
            ->method('setRedirect')
            ->with('redirect_url');

        // Run
        $controller->cleanMediaAction();
    }
}
