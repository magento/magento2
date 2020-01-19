<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Authorizenet\Controller\Adminhtml\Authorizenet\Directpost\Payment;

use Magento\Authorizenet\Model\Directpost;
use Magento\Backend\App\Action\Context as BackendActionContext;
use Magento\Backend\Model\Session\Quote as SessionQuote;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Payment;
use Magento\Sales\Model\AdminOrder\Create as AdminOrderCreate;
use Magento\Sales\Model\Order;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractBackendController;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Verify AuthorizeNet Controller for PlaceOrder
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PlaceTest extends AbstractBackendController
{
    /**
     * Test requestToAuthorizenetData returning
     * @magentoAppArea adminhtml
     */
    public function testExecuteAuthorizenetDataReturning()
    {
        $requestToAuthorizenetData = ['Authorizenet' => 'data'];

        $this->getRequest()->setParam('payment', ['method' => 'authorizenet_directpost']);
        $this->getRequest()->setParam('controller', 'order_create');
        $orderCreateMock = $this->getOrderCreateMock($requestToAuthorizenetData);
        $directpostMock = $this->getMockBuilder(Directpost::class)
            ->setMethods(['getCode'])
            ->disableOriginalConstructor()
            ->getMock();
        $directpostMock->expects($this->once())
            ->method('getCode')
            ->willReturn('authorizenet_directpost');
        $jsonHelper = $this->_objectManager->get(JsonHelper::class);
        $objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->setMethods(['create', 'get'])
            ->getMockForAbstractClass();
        $objectManagerMock->expects($this->atLeastOnce())
            ->method('create')
            ->with(Directpost::class)
            ->willReturn($directpostMock);
        $authorizenetSessionMock = $this->getMockBuilder(Directpost::class)
            ->disableOriginalConstructor()
            ->getMock();
        $urlMock = $this->getMockBuilder(UrlInterface::class)
            ->getMockForAbstractClass();
        $objectManagerMock->expects($this->atLeastOnce())
            ->method('get')
            ->willReturnMap([
                [AdminOrderCreate::class, $orderCreateMock],
                [JsonHelper::class, $jsonHelper],
                [Directpost\Session::class, $authorizenetSessionMock],
                [UrlInterface::class, $urlMock],
            ]);

        $context = $this->getObjectManager()->create(
            BackendActionContext::class,
            [
                'objectManager' => $objectManagerMock
            ]
        );

        $controller = $this->getObjectManager()->create(
            PlaceTesting::class,
            ['context' => $context]
        );
        $controller->execute();
        $this->assertContains(json_encode($requestToAuthorizenetData), $this->getResponse()->getBody());
    }

    /**
     * @param array $requestToAuthorizenetData
     * @return AdminOrderCreate|MockObject
     */
    private function getOrderCreateMock($requestToAuthorizenetData)
    {
        $methodInstanceMock = $this->getMockBuilder(Directpost::class)
            ->disableOriginalConstructor()
            ->getMock();
        $directpostRequestMock = $this->getMockBuilder(Directpost\Request::class)
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMock();
        $directpostRequestMock->expects($this->once())
            ->method('getData')
            ->willReturn($requestToAuthorizenetData);
        $methodInstanceMock->expects($this->once())
            ->method('generateRequestFromOrder')
            ->willReturn($directpostRequestMock);
        $paymentMock = $this->getMockBuilder(Payment::class)
            ->setMethods(['getMethod', 'getMethodInstance'])
            ->disableOriginalConstructor()
            ->getMock();
        $paymentMock->expects($this->once())
            ->method('getMethod')
            ->willReturn('authorizenet_directpost');
        $paymentMock->expects($this->once())
            ->method('getMethodInstance')
            ->willReturn($methodInstanceMock);
        $quoteMock = $this->getMockBuilder(Quote::class)
            ->setMethods(['getPayment', 'getStoreId'])
            ->disableOriginalConstructor()
            ->getMock();
        $quoteMock->expects($this->any())
            ->method('getPayment')
            ->willReturn($paymentMock);
        $orderMock = $this->getMockBuilder(Order::class)
            ->setMethods(['getPayment'])
            ->disableOriginalConstructor()
            ->getMock();
        $orderMock->expects($this->any())
            ->method('getPayment')
            ->willReturn($paymentMock);
        $sessionQuoteMock = $this->getMockBuilder(SessionQuote::class)
            ->setMethods(['getOrder'])
            ->disableOriginalConstructor()
            ->getMock();
        $sessionQuoteMock->expects($this->once())
            ->method('getOrder')
            ->willReturn($orderMock);
        $orderCreateMock = $this->getMockBuilder(AdminOrderCreate::class)
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

    /**
     * @return ObjectManagerInterface
     */
    private function getObjectManager(): ObjectManagerInterface
    {
        return Bootstrap::getObjectManager();
    }
}
