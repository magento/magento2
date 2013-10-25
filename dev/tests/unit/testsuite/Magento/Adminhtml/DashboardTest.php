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
namespace Magento\Adminhtml;

class DashboardTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_request;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_response;

    protected function setUp()
    {
        $this->_request = $this->getMock('Magento\App\Request\Http', array(), array(), '', false);
        $this->_response = $this->getMock('Magento\App\Response\Http', array(), array(), '', false);
    }

    protected function tearDown()
    {
        $this->_request = null;
        $this->_response = null;
    }

    public function testTunnelAction()
    {
        $fixture = uniqid();
        $this->_request->expects($this->at(0))
            ->method('getParam')->with('ga')
            ->will($this->returnValue(urlencode(base64_encode(json_encode(array(1))))));
        $this->_request->expects($this->at(1))->method('getParam')->with('h')->will($this->returnValue($fixture));
        $tunnelResponse = $this->getMock('Magento\App\Response\Http', array(), array(), '', false);
        $httpClient = $this->getMock('Magento\HTTP\ZendClient',
            array('setUri', 'setParameterGet', 'setConfig', 'request', 'getHeaders')
        );
        /** @var $helper \Magento\Adminhtml\Helper\Dashboard\Data|PHPUnit_Framework_MockObject_MockObject */
        $helper = $this->getMock('Magento\Adminhtml\Helper\Dashboard\Data',
            array('getChartDataHash'), array(), '', false, false
        );
        $helper->expects($this->any())->method('getChartDataHash')->will($this->returnValue($fixture));

        $objectManager = $this->getMock('Magento\ObjectManager');
        $objectManager->expects($this->at(0))
            ->method('get')
            ->with('Magento\Adminhtml\Helper\Dashboard\Data')
            ->will($this->returnValue($helper));
        $objectManager->expects($this->at(1))
            ->method('create')
            ->with('Magento\HTTP\ZendClient')
            ->will($this->returnValue($httpClient));
        $httpClient->expects($this->once())->method('setUri')->will($this->returnValue($httpClient));
        $httpClient->expects($this->once())->method('setParameterGet')->will(($this->returnValue($httpClient)));
        $httpClient->expects($this->once())->method('setConfig')->will(($this->returnValue($httpClient)));
        $httpClient->expects($this->once())->method('request')->with('GET')->will($this->returnValue($tunnelResponse));
        $tunnelResponse->expects(
            $this->any())->method('getHeaders')->will($this->returnValue(array('Content-type' => 'test_header'))
            );
        $this->_response->expects($this->any())->method('setHeader')->will($this->returnValue($this->_response));
        $tunnelResponse->expects($this->any())->method('getBody')->will($this->returnValue('success_msg'));
        $this->_response->expects(
            $this->once())->method('setBody')->with('success_msg')->will($this->returnValue($this->_response));
        $this->_response->expects($this->any())->method('getBody')->will($this->returnValue('success_msg'));
        $controller = $this->_factory($this->_request, $this->_response, $objectManager);
        $controller->tunnelAction();
        $this->assertEquals('success_msg', $controller->getResponse()->getBody());
    }

    public function testTunnelAction400()
    {
        $this->_response->expects($this->once())->method('setBody')
            ->with('Service unavailable: invalid request')
            ->will($this->returnValue($this->_response));
        $this->_response->expects($this->any())->method('setHeader')->will($this->returnValue($this->_response));
        $this->_response->expects(
            $this->once())->method('setHttpResponseCode')->with(400)->will($this->returnValue($this->_response)
            );
        $this->_response->expects($this->once())->method('getHttpResponseCode')->will($this->returnValue(400));
        $controller = $this->_factory($this->_request, $this->_response);
        $controller->tunnelAction();
        $this->assertEquals(400, $controller->getResponse()->getHttpResponseCode());
    }

    public function testTunnelAction503()
    {
        $fixture = uniqid();
        $this->_request->expects($this->at(0))
            ->method('getParam')->with('ga')
            ->will($this->returnValue(urlencode(base64_encode(json_encode(array(1))))));
        $this->_request->expects($this->at(1))->method('getParam')->with('h')->will($this->returnValue($fixture));
        /** @var $helper \Magento\Adminhtml\Helper\Dashboard\Data|PHPUnit_Framework_MockObject_MockObject */
        $helper = $this->getMock('Magento\Adminhtml\Helper\Dashboard\Data',
            array('getChartDataHash'), array(), '', false, false
        );
        $helper->expects($this->any())->method('getChartDataHash')->will($this->returnValue($fixture));

        $objectManager = $this->getMock('Magento\ObjectManager');
        $objectManager->expects($this->at(0))
            ->method('get')
            ->with('Magento\Adminhtml\Helper\Dashboard\Data')
            ->will($this->returnValue($helper));
        $exceptionMock = new \Exception();
        $objectManager->expects($this->at(1))
            ->method('create')
            ->with('Magento\HTTP\ZendClient')
            ->will($this->throwException($exceptionMock));
        $loggerMock = $this->getMock('Magento\Core\Model\Logger', array('logException'), array(), '', false);
        $loggerMock->expects($this->once())->method('logException')->with($exceptionMock);
        $objectManager->expects($this->at(2))
            ->method('get')
            ->with('Magento\Core\Model\Logger')
            ->will($this->returnValue($loggerMock));

        $this->_response->expects($this->once())
            ->method('setBody')
            ->with('Service unavailable: see error log for details')
            ->will($this->returnValue($this->_response));
        $this->_response->expects($this->any())->method('setHeader')->will($this->returnValue($this->_response));
        $this->_response->expects($this->once())
            ->method('setHttpResponseCode')
            ->with(503)
            ->will($this->returnValue($this->_response));
        $this->_response->expects($this->once())->method('getHttpResponseCode')->will($this->returnValue(503));
        $controller = $this->_factory($this->_request, $this->_response, $objectManager);
        $controller->tunnelAction();
        $this->assertEquals(503, $controller->getResponse()->getHttpResponseCode());
    }

    /**
     * Create the tested object
     *
     * @param Magento\App\Request\Http $request
     * @param \Magento\App\Response\Http|null $response
     * @param \Magento\ObjectManager|null $objectManager
     * @return \Magento\Adminhtml\Controller\Dashboard|PHPUnit_Framework_MockObject_MockObject
     */
    protected function _factory($request, $response = null, $objectManager = null)
    {
        if (!$response) {
            /** @var $response \Magento\App\ResponseInterface|PHPUnit_Framework_MockObject_MockObject */
            $response = $this->getMock('Magento\App\Response\Http', array(), array(), '', false);
            $response->headersSentThrowsException = false;
        }
        if (!$objectManager) {
            $objectManager = new \Magento\ObjectManager\ObjectManager();
        }
        $rewriteFactory = $this->getMock('Magento\Core\Model\Url\RewriteFactory', array('create'), array(), '', false);
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $varienFront = $helper->getObject('Magento\App\FrontController',
            array('rewriteFactory' => $rewriteFactory)
        );

        $arguments = array(
            'request' => $request,
            'response' => $response,
            'objectManager' => $objectManager,
            'frontController' => $varienFront,
        );
        $context = $helper->getObject('Magento\Backend\Controller\Context', $arguments);
        return new \Magento\Adminhtml\Controller\Dashboard($context);
    }
}

