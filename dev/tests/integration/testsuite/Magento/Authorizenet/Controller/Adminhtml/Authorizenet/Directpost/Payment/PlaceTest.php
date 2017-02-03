<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Controller\Adminhtml\Authorizenet\Directpost\Payment;

/**
 * Class PlaceTest
 */
class PlaceTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * Test requestToAuthorizenetData returning
     */
    public function testExecuteAuthorizenetDataReturning()
    {
        $requestToAuthorizenetData = ['Authorizenet' => 'data'];

        $this->getRequest()->setParam('payment', ['method' => 'authorizenet_directpost']);
        $this->getRequest()->setParam('controller', 'order_create');
        $orderCreateMock = $this->getOrderCreateMock($requestToAuthorizenetData);
        $directpostMock =  $this->getMockBuilder('Magento\Authorizenet\Model\Directpost')
            ->setMethods(['getCode'])
            ->disableOriginalConstructor()
            ->getMock();
        $directpostMock->expects($this->once())
            ->method('getCode')
            ->willReturn('authorizenet_directpost');
        $jsonHelper = $this->_objectManager->get('Magento\Framework\Json\Helper\Data');
        $objectManagerMock =  $this->getMockBuilder('Magento\Framework\ObjectManagerInterface')
            ->setMethods(['create', 'get'])
            ->getMockForAbstractClass();
        $objectManagerMock->expects($this->atLeastOnce())
            ->method('create')
            ->with('Magento\Authorizenet\Model\Directpost')
            ->willReturn($directpostMock);
        $authorizenetSessionMock = $this->getMockBuilder('Magento\Authorizenet\Model\Directpost')
            ->disableOriginalConstructor()
            ->getMock();
        $urlMock = $this->getMockBuilder('Magento\Backend\Model\UrlInterface')
            ->getMockForAbstractClass();
        $objectManagerMock->expects($this->atLeastOnce())
            ->method('get')
            ->willReturnMap([
                ['Magento\Sales\Model\AdminOrder\Create', $orderCreateMock],
                ['Magento\Framework\Json\Helper\Data', $jsonHelper],
                ['Magento\Authorizenet\Model\Directpost\Session', $authorizenetSessionMock],
                ['Magento\Backend\Model\UrlInterface', $urlMock],
            ]);

        $context = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Backend\App\Action\Context',
            [
                'objectManager' => $objectManagerMock
            ]
        );

        $controller = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Authorizenet\Controller\Adminhtml\Authorizenet\Directpost\Payment\PlaceTesting',
            ['context' => $context]
        );
        $controller->execute();
        $this->assertContains(json_encode($requestToAuthorizenetData), $this->getResponse()->getBody());
    }

    /**
     * @param array $requestToAuthorizenetData
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getOrderCreateMock($requestToAuthorizenetData)
    {
        $methodInstanceMock =  $this->getMockBuilder('Magento\Authorizenet\Model\Directpost')
            ->disableOriginalConstructor()
            ->getMock();
        $directpostRequestMock = $this->getMockBuilder('Magento\Authorizenet\Model\Directpost\Request')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMock();
        $directpostRequestMock->expects($this->once())
            ->method('getData')
            ->willReturn($requestToAuthorizenetData);
        $methodInstanceMock->expects($this->once())
            ->method('generateRequestFromOrder')
            ->willReturn($directpostRequestMock);
        $paymentMock = $this->getMockBuilder('Magento\Quote\Model\Quote\Payment')
            ->setMethods(['getMethod', 'getMethodInstance'])
            ->disableOriginalConstructor()
            ->getMock();
        $paymentMock->expects($this->once())
            ->method('getMethod')
            ->willReturn('authorizenet_directpost');
        $paymentMock->expects($this->once())
            ->method('getMethodInstance')
            ->willReturn($methodInstanceMock);
        $quoteMock = $this->getMockBuilder('Magento\Quote\Model\Quote')
            ->setMethods(['getPayment', 'getStoreId'])
            ->disableOriginalConstructor()
            ->getMock();
        $quoteMock->expects($this->any())
            ->method('getPayment')
            ->willReturn($paymentMock);
        $orderMock = $this->getMockBuilder('Magento\Sales\Model\Order')
            ->setMethods(['getPayment'])
            ->disableOriginalConstructor()
            ->getMock();
        $orderMock->expects($this->any())
            ->method('getPayment')
            ->willReturn($paymentMock);
        $sessionQuoteMock = $this->getMockBuilder('Magento\Backend\Model\Session\Quote')
            ->setMethods(['getOrder'])
            ->disableOriginalConstructor()
            ->getMock();
        $sessionQuoteMock->expects($this->once())
            ->method('getOrder')
            ->willReturn($orderMock);
        $orderCreateMock = $this->getMockBuilder('Magento\Sales\Model\AdminOrder\Create')
            ->setMethods(['getQuote', 'getSession', 'setIsValidate', 'importPostData', 'createOrder', 'setPaymentData'])
            ->disableOriginalConstructor()
            ->getMock();
        $orderCreateMock->expects($this->any())
            ->method('getQuote')
            ->willReturn($quoteMock);
        $orderCreateMock->expects($this->once())
            ->method('getSession')
            ->willReturn($sessionQuoteMock);
        $orderCreateMock->expects($this->once())
            ->method('setIsValidate')
            ->willReturnSelf();
        $orderCreateMock->expects($this->once())
            ->method('importPostData')
            ->willReturnSelf();
        $orderCreateMock->expects($this->once())
            ->method('createOrder')
            ->willReturn($orderMock);

        return $orderCreateMock;
    }
}
