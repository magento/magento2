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
class Mage_Adminhtml_DashboardControllerTest extends PHPUnit_Framework_TestCase
{
    public function testTunnelAction()
    {
        $fixture = uniqid();
        /** @var $request Mage_Core_Controller_Request_Http|PHPUnit_Framework_MockObject_MockObject */
        $request = $this->getMockForAbstractClass('Mage_Core_Controller_Request_Http');
        $request->setParam('ga', urlencode(base64_encode(serialize(array(1)))));
        $request->setParam('h', $fixture);

        $tunnelResponse = new Zend_Http_Response(200, array('Content-Type' => 'test_header'), 'success_msg');
        $httpClient = $this->getMock('Varien_Http_Client', array('request'));
        $httpClient->expects($this->once())->method('request')->will($this->returnValue($tunnelResponse));
        /** @var $helper Mage_Adminhtml_Helper_Dashboard_Data|PHPUnit_Framework_MockObject_MockObject */
        $helper = $this->getMock('Mage_Adminhtml_Helper_Dashboard_Data',
            array('getChartDataHash'), array(), '', false, false
        );
        $helper->expects($this->any())->method('getChartDataHash')->will($this->returnValue($fixture));

        $objectManager = $this->getMock('Magento_ObjectManager');
        $objectManager->expects($this->at(0))
            ->method('get')
            ->with('Mage_Adminhtml_Helper_Dashboard_Data')
            ->will($this->returnValue($helper));
        $objectManager->expects($this->at(1))
            ->method('create')
            ->with('Varien_Http_Client')
            ->will($this->returnValue($httpClient));

        $controller = $this->_factory($request, null, $objectManager);
        $controller->tunnelAction();
        $this->assertEquals('success_msg', $controller->getResponse()->getBody());
    }

    public function testTunnelAction400()
    {
        $controller = $this->_factory($this->getMockForAbstractClass('Mage_Core_Controller_Request_Http'));
        $controller->tunnelAction();
        $this->assertEquals(400, $controller->getResponse()->getHttpResponseCode());
    }

    public function testTunnelAction503()
    {
        $fixture = uniqid();
        /** @var $request Mage_Core_Controller_Request_Http|PHPUnit_Framework_MockObject_MockObject */
        $request = $this->getMockForAbstractClass('Mage_Core_Controller_Request_Http');
        $request->setParam('ga', urlencode(base64_encode(serialize(array(1)))));
        $request->setParam('h', $fixture);

        /** @var $helper Mage_Adminhtml_Helper_Dashboard_Data|PHPUnit_Framework_MockObject_MockObject */
        $helper = $this->getMock('Mage_Adminhtml_Helper_Dashboard_Data',
            array('getChartDataHash'), array(), '', false, false
        );
        $helper->expects($this->any())->method('getChartDataHash')->will($this->returnValue($fixture));

        $objectManager = $this->getMock('Magento_ObjectManager');
        $objectManager->expects($this->at(0))
            ->method('get')
            ->with('Mage_Adminhtml_Helper_Dashboard_Data')
            ->will($this->returnValue($helper));
        $exceptionMock = new Exception();
        $objectManager->expects($this->at(1))
            ->method('create')
            ->with('Varien_Http_Client')
            ->will($this->throwException($exceptionMock));
        $loggerMock = $this->getMock('Mage_Core_Model_Logger', array('logException'), array(), '', false);
        $loggerMock->expects($this->once())->method('logException')->with($exceptionMock);
        $objectManager->expects($this->at(2))
            ->method('get')
            ->with('Mage_Core_Model_Logger')
            ->will($this->returnValue($loggerMock));

        $controller = $this->_factory($request, null, $objectManager);
        $controller->tunnelAction();
        $this->assertEquals(503, $controller->getResponse()->getHttpResponseCode());
    }

    /**
     * Create the tested object
     *
     * @param Mage_Core_Controller_Request_Http $request
     * @param Mage_Core_Controller_Response_Http|null $response
     * @param Magento_ObjectManager|null $objectManager
     * @return Mage_Adminhtml_DashboardController|PHPUnit_Framework_MockObject_MockObject
     */
    protected function _factory($request, $response = null, $objectManager = null)
    {
        if (!$response) {
            /** @var $response Mage_Core_Controller_Response_Http|PHPUnit_Framework_MockObject_MockObject */
            $response = $this->getMockForAbstractClass('Mage_Core_Controller_Response_Http');
            $response->headersSentThrowsException = false;
        }
        if (!$objectManager) {
            $objectManager = new Magento_ObjectManager_ObjectManager();
        }

        $routerFactory  = $this->getMock('Mage_Core_Controller_Varien_Router_Factory', array(), array(), '', false);
        $rewriteFactory = $this->getMock('Mage_Core_Model_Url_RewriteFactory', array(), array(), '', false);
        $varienFront = new Mage_Core_Controller_Varien_Front($routerFactory, $rewriteFactory);
        $layoutFactory = $this->getMock('Mage_Core_Model_Layout_Factory', array(), array(), '', false);

        return $this->getMock('Mage_Adminhtml_DashboardController', array('__'), array(
            $request, $response, $objectManager,
            $varienFront, $layoutFactory, null,
            array('helper' => 1, 'session' => 1, 'translator' => 1)
        ));
    }
}

require_once __DIR__ . '/../../../../../../app/code/Mage/Adminhtml/controllers/DashboardController.php';
