<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Controller\Onepage;

use Magento\Checkout\Controller\Onepage\SaveOrder;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class SaveOrderTest
 */
class SaveOrderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SaveOrder
     */
    protected $controller;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $formKeyValidatorMock;

    /**
     * @var \Magento\Framework\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirectFactoryMock;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRawFactoryMock;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultJsonFactoryMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \Magento\Checkout\Model\Type\Onepage|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $onepageMock;

    /**
     * @var \Magento\Checkout\Model\Agreements\AgreementsValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $agreementsValidatorMock;

    /**
     * @var \Magento\Quote\Model\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $helper = new ObjectManager($this);

        $contextMock = $this->getMockBuilder('Magento\Framework\App\Action\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->getMockBuilder('Magento\Framework\App\RequestInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getPost'])
            ->getMockForAbstractClass();
        $this->responseMock = $this->getMockBuilder('Magento\Framework\App\ResponseInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->objectManagerMock = $this->getMockBuilder('Magento\Framework\ObjectManagerInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->formKeyValidatorMock = $this->getMockBuilder('Magento\Framework\Data\Form\FormKey\Validator')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectFactoryMock = $this->getMockBuilder('Magento\Framework\Controller\Result\RedirectFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRawFactoryMock = $this->getMockBuilder('Magento\Framework\Controller\Result\RawFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultJsonFactoryMock = $this->getMockBuilder('Magento\Framework\Controller\Result\JsonFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventManagerMock = $this->getMockBuilder('Magento\Framework\Event\ManagerInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->onepageMock = $this->getMockBuilder('Magento\Checkout\Model\Type\Onepage')
            ->disableOriginalConstructor()
            ->getMock();
        $this->agreementsValidatorMock = $this->getMockBuilder('Magento\Checkout\Model\Agreements\AgreementsValidator')
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteMock = $this->getMockBuilder('Magento\Quote\Model\Quote')
            ->disableOriginalConstructor()
            ->getMock();

        $contextMock->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $contextMock->expects($this->once())
            ->method('getResponse')
            ->willReturn($this->responseMock);
        $contextMock->expects($this->once())
            ->method('getObjectManager')
            ->willReturn($this->objectManagerMock);
        $contextMock->expects($this->once())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactoryMock);
        $contextMock->expects($this->once())
            ->method('getEventManager')
            ->willReturn($this->eventManagerMock);

        $this->controller = $helper->getObject(
            'Magento\Checkout\Controller\Onepage\SaveOrder',
            [
                'context' => $contextMock,
                'formKeyValidator' => $this->formKeyValidatorMock,
                'resultRawFactory' => $this->resultRawFactoryMock,
                'resultJsonFactory' => $this->resultJsonFactoryMock,
            ]
        );
    }

    /**
     * Test method execution _expireAjax (call hasItems === false)
     *
     * @return void
     */
    protected function expireAjaxFlowHasItemsFalse()
    {
        $this->onepageMock->expects($this->atLeastOnce())
            ->method('getQuote')
            ->willReturn($this->quoteMock);

        $this->quoteMock->expects($this->once())
            ->method('hasItems')
            ->willReturn(false);
        $this->quoteMock->expects($this->never())
            ->method('getHasError')
            ->willReturn(true);
        $this->quoteMock->expects($this->never())
            ->method('validateMinimumAmount')
            ->willReturn(false);

        $this->requestMock->expects($this->never())
            ->method('getActionName');
    }

    /**
     * Test for execute method
     *
     * @return void
     */
    public function testExecuteWithSuccessOrderSave()
    {
        $testData = $this->getExecuteWithSuccessOrderSaveTestData();


        $redirectMock = $this->getMockBuilder('Magento\Framework\Controller\Result\Redirect')
            ->disableOriginalConstructor()
            ->getMock();
        $paymentMock = $this->getMockBuilder('Magento\Quote\Model\Quote\Payment')
            ->disableOriginalConstructor()
            ->getMock();
        $checkoutMock = $this->getMockBuilder('Magento\Checkout\Model\Session')
            ->disableOriginalConstructor()
            ->setMethods(['getRedirectUrl'])
            ->getMock();
        $resultJsonMock = $this->getMockBuilder('Magento\Framework\Controller\Result\Json')
            ->disableOriginalConstructor()
            ->getMock();

        $redirectMock->expects($this->never())
            ->method('setPath')
            ->with('*/*/')
            ->willReturn('redirect');

        $this->formKeyValidatorMock->expects($this->once())
            ->method('validate')
            ->with($this->requestMock)
            ->willReturn(true);

        $this->resultRedirectFactoryMock->expects($this->never())
            ->method('create')
            ->willReturn($redirectMock);

        $this->objectManagerMock->expects($this->atLeastOnce())
            ->method('get')
            ->willReturnMap($testData['objectManager.get']);

        // call _expireAjax method
        $this->expireAjaxFlowHasItemsFalse();

        $this->requestMock->expects($this->atLeastOnce())
            ->method('getPost')
            ->willReturnMap($testData['request.getPost']);

        $this->agreementsValidatorMock->expects($this->once())
            ->method('isValid')
            ->with($testData['agreementsValidator.isValid'])
            ->willReturn(true);

        $this->quoteMock->expects($this->atLeastOnce())
            ->method('getPayment')
            ->willReturn($paymentMock);

        $paymentMock->expects($this->once())
            ->method('setQuote')
            ->with($this->quoteMock);
        $paymentMock->expects($this->once())
            ->method('importData')
            ->with($testData['payment.importData']);

        $this->onepageMock->expects($this->once())
            ->method('saveOrder');
        $this->onepageMock->expects($this->once())
            ->method('getCheckout')
            ->willReturn($checkoutMock);

        $checkoutMock->expects($this->once())
            ->method('getRedirectUrl')
            ->willReturn(null);

        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->withConsecutive(
                $this->equalTo('checkout_controller_onepage_saveOrder'),
                $this->countOf(2)
            );

        $this->resultJsonFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultJsonMock);

        $resultJsonMock->expects($this->once())
            ->method('setData')
            ->with($testData['resultJson.setData'])
            ->willReturnSelf();

        $this->assertEquals($resultJsonMock, $this->controller->execute());
    }

    /**
     * Get data for test testExecuteWithSuccessOrderSave
     *
     * @return array
     */
    protected function getExecuteWithSuccessOrderSaveTestData()
    {
        $data = [
            'payment-key-1' => 'payment-value-1',
            'checks' => [
                \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_CHECKOUT,
                \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_COUNTRY,
                \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_CURRENCY,
                \Magento\Payment\Model\Method\AbstractMethod::CHECK_ORDER_TOTAL_MIN_MAX,
                \Magento\Payment\Model\Method\AbstractMethod::CHECK_ZERO_TOTAL,
            ]
        ];
        $testKey = 'test-key-1';

        return [
            'resultJson.setData' => [
                'success' => 1,
                'error' => false
            ],
            'request.getPost' => [
                [
                    'agreement',
                    [],
                    [
                        $testKey => 'test-value-1'
                    ]
                ],
                [
                    'payment',
                    [],
                    $data
                ],
            ],
            'payment.importData' => $data,
            'agreementsValidator.isValid' => [$testKey],
            'objectManager.get' => [
                ['Magento\Checkout\Model\Type\Onepage', $this->onepageMock],
                ['Magento\Checkout\Model\Agreements\AgreementsValidator', $this->agreementsValidatorMock],
            ]
        ];
    }
}
