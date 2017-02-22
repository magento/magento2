<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Test\Unit\Observer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class AddFieldsToResponseObserverTest
 */
class AddFieldsToResponseObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $coreRegistryMock;

    /**
     * @var \Magento\Authorizenet\Model\Directpost|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentMock;

    /**
     * @var \Magento\Authorizenet\Model\Directpost\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Checkout\Controller\Onepage\SaveOrder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $actionMock;

    /**
     * @var \Magento\Framework\DataObject|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultMock;

    /**
     * @var \Magento\Authorizenet\Observer\AddFieldsToResponseObserver
     */
    protected $addFieldsToResponseObserver;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $helper = new ObjectManager($this);

        $this->coreRegistryMock = $this->getMockBuilder('Magento\Framework\Registry')
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentMock = $this->getMockBuilder('Magento\Authorizenet\Model\Directpost')
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionMock = $this->getMockBuilder('Magento\Authorizenet\Model\Directpost\Session')
            ->disableOriginalConstructor()
            ->setMethods(['setLastOrderIncrementId', 'addCheckoutOrderIncrementId'])
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder('Magento\Store\Model\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->actionMock = $this->getMockBuilder('Magento\Checkout\Controller\Onepage\SaveOrder')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultMock = $this->getMockBuilder('Magento\Framework\DataObject')
            ->disableOriginalConstructor()
            ->getMock();

        $this->addFieldsToResponseObserver = $helper->getObject(
            'Magento\Authorizenet\Observer\AddFieldsToResponseObserver',
            [
                'coreRegistry' => $this->coreRegistryMock,
                'payment' => $this->paymentMock,
                'session' => $this->sessionMock,
                'storeManager' => $this->storeManagerMock,
            ]
        );
    }

    /**
     * Test for addFieldsToResponse method
     *
     * @return void
     */
    public function testAddFieldsToResponseSuccess()
    {
        $testData = $this->getAddFieldsToResponseSuccessTestData();

        $observerMock = $this->getMockBuilder('Magento\Framework\Event\Observer')
            ->disableOriginalConstructor()
            ->getMock();
        $orderMock = $this->getMockBuilder('Magento\Sales\Model\Order')
            ->disableOriginalConstructor()
            ->getMock();
        $orderPaymentMock = $this->getMockBuilder('Magento\Sales\Model\Order\Payment')
            ->disableOriginalConstructor()
            ->getMock();
        $instanceMock = $this->getMockBuilder('Magento\Authorizenet\Model\Directpost')
            ->disableOriginalConstructor()
            ->getMock();
        $requestToAuthorizenetMock = $this->getMockBuilder('Magento\Authorizenet\Model\Directpost\Request')
            ->disableOriginalConstructor()
            ->setMethods(['setControllerActionName', 'setIsSecure', 'getData'])
            ->getMock();
        $requestMock = $this->getMockBuilder('Magento\Framework\App\RequestInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getControllerName'])
            ->getMockForAbstractClass();
        $storeMock = $this->getMockBuilder('Magento\Store\Model\Store')
            ->disableOriginalConstructor()
            ->getMock();

        $this->coreRegistryMock->expects($this->once())
            ->method('registry')
            ->with('directpost_order')
            ->willReturn($orderMock);
        $orderMock->expects($this->once())
            ->method('getId')
            ->willReturn($testData['order.getId']);
        $orderMock->expects($this->once())
            ->method('getPayment')
            ->willReturn($orderPaymentMock);
        $orderPaymentMock->expects($this->once())
            ->method('getMethod')
            ->willReturn($testData['orderPayment.getMethod']);
        $this->paymentMock->expects($this->exactly(2))
            ->method('getCode')
            ->willReturn($testData['payment.getCode']);
        $observerMock->expects($this->atLeastOnce())
            ->method('getData')
            ->willReturnMap($testData['observer.getData']);
        $this->resultMock->expects($this->once())
            ->method('getData')
            ->willReturn($testData['result.getData']);
        $orderMock->expects($this->atLeastOnce())
            ->method('getIncrementId')
            ->willReturn($testData['order.getIncrementId']);
        $this->sessionMock->expects($this->once())
            ->method('addCheckoutOrderIncrementId')
            ->with($testData['session.addCheckoutOrderIncrementId']);
        $this->sessionMock->expects($this->once())
            ->method('setLastOrderIncrementId')
            ->with($testData['session.setLastOrderIncrementId']);
        $orderPaymentMock->expects($this->once())
            ->method('getMethodInstance')
            ->willReturn($instanceMock);
        $instanceMock->expects($this->once())
            ->method('generateRequestFromOrder')
            ->with($orderMock)
            ->willReturn($requestToAuthorizenetMock);
        $this->actionMock->expects($this->once())
            ->method('getRequest')
            ->willReturn($requestMock);
        $requestMock->expects($this->once())
            ->method('getControllerName')
            ->willReturn($testData['request.getControllerName']);
        $requestToAuthorizenetMock->expects($this->once())
            ->method('setControllerActionName')
            ->with($testData['requestToAuthorizenet.setControllerActionName']);
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);
        $storeMock->expects($this->once())
            ->method('isCurrentlySecure')
            ->willReturn($testData['store.isCurrentlySecure']);
        $requestToAuthorizenetMock->expects($this->once())
            ->method('setIsSecure')
            ->with($testData['requestToAuthorizenet.setIsSecure']);
        $requestToAuthorizenetMock->expects($this->once())
            ->method('getData')
            ->willReturn($testData['requestToAuthorizenet.getData']);
        $this->resultMock->expects($this->once())
            ->method('setData')
            ->with($testData['result.setData']);

        $this->addFieldsToResponseObserver->execute($observerMock);
    }

    /**
     * Get data for test testAddFieldsToResponseSuccess
     *
     * @return array
     */
    protected function getAddFieldsToResponseSuccessTestData()
    {
        $requestFields = [
            'field-1' => 'field-value-1',
            'field-2' => 'field-value-2',
            'field-3' => 'field-value-3',
        ];
        $secure = 'test-currently-secure';
        $controllerName = 'test-controller-name';
        $incrementId = '0000000001';
        $paymentCode = 'test-payment-code';

        return [
            'order.getId' => 77,
            'orderPayment.getMethod' => $paymentCode,
            'payment.getCode' => $paymentCode,
            'observer.getData' => [
                ['action', null, $this->actionMock],
                ['result', null, $this->resultMock],
            ],
            'result.getData' => [
                'error' => false
            ],
            'order.getIncrementId' => $incrementId,
            'session.addCheckoutOrderIncrementId' => $incrementId,
            'session.setLastOrderIncrementId' => $incrementId,
            'request.getControllerName' => $controllerName,
            'requestToAuthorizenet.setControllerActionName' => $controllerName,
            'store.isCurrentlySecure' => $secure,
            'requestToAuthorizenet.setIsSecure' => $secure,
            'requestToAuthorizenet.getData' => $requestFields,
            'result.setData' => [
                'error' => false,
                'test-payment-code' => [
                    'fields' => $requestFields
                ]
            ]
        ];
    }
}
