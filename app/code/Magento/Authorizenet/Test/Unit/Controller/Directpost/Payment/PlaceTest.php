<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Test\Unit\Controller\Directpost\Payment;

use Magento\Authorizenet\Controller\Directpost\Payment\Place;
use Magento\Authorizenet\Helper\DataFactory;
use Magento\Authorizenet\Model\Directpost\Session as DirectpostSession;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Checkout\Model\Type\Onepage;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Model\IframeConfigProvider;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Model\Quote;

/**
 * Class PlaceTest
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PlaceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var Place
     */
    protected $placeOrderController;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $coreRegistryMock;

    /**
     * @var DataFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataFactoryMock;

    /**
     * @var CartManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cartManagementMock;

    /**
     * @var Onepage|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $onepageCheckout;

    /**
     * @var Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $jsonHelperMock;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var DirectpostSession|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $directpostSessionMock;

    /**
     * @var Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    /**
     * @var CheckoutSession|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutSessionMock;

    protected function setUp()
    {
        $this->directpostSessionMock = $this
            ->getMockBuilder(\Magento\Authorizenet\Model\Directpost\Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteMock = $this
            ->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->checkoutSessionMock = $this
            ->getMockBuilder(\Magento\Checkout\Model\Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->checkoutSessionMock->expects($this->any())
            ->method('getQuote')
            ->will($this->returnValue($this->quoteMock));
        $this->objectManagerMock = $this
            ->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)
            ->getMockForAbstractClass();
        $this->objectManagerMock->expects($this->any())
            ->method('get')
            ->willReturnMap([
                [\Magento\Authorizenet\Model\Directpost\Session::class, $this->directpostSessionMock],
                [\Magento\Checkout\Model\Session::class, $this->checkoutSessionMock],
            ]);
        $this->coreRegistryMock = $this
            ->getMockBuilder(\Magento\Framework\Registry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataFactoryMock = $this
            ->getMockBuilder(\Magento\Authorizenet\Helper\DataFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cartManagementMock = $this
            ->getMockBuilder(\Magento\Quote\Api\CartManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->onepageCheckout = $this
            ->getMockBuilder(\Magento\Checkout\Model\Type\Onepage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->jsonHelperMock = $this
            ->getMockBuilder(\Magento\Framework\Json\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this
            ->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->getMockForAbstractClass();
        $this->responseMock = $this
            ->getMockBuilder(\Magento\Framework\App\Response\Http::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->objectManager = new ObjectManager($this);
        $this->placeOrderController = $this->objectManager->getObject(
            \Magento\Authorizenet\Controller\Directpost\Payment\Place::class,
            [
                'request' => $this->requestMock,
                'response' => $this->responseMock,
                'objectManager' => $this->objectManagerMock,
                'coreRegistry' => $this->coreRegistryMock,
                'dataFactory' => $this->dataFactoryMock,
                'cartManagement' => $this->cartManagementMock,
                'onepageCheckout' => $this->onepageCheckout,
                'jsonHelper' => $this->jsonHelperMock,
            ]
        );
    }

    /**
     * @param $paymentMethod
     * @param $controller
     * @param $quoteId
     * @param $orderId
     * @param $result
     * @dataProvider textExecuteDataProvider
     */
    public function testExecute(
        $paymentMethod,
        $controller,
        $quoteId,
        $orderId,
        $result
    ) {
        $this->requestMock->expects($this->at(0))
            ->method('getParam')
            ->with('payment')
            ->will($this->returnValue($paymentMethod));

        $this->requestMock->expects($this->at(1))
            ->method('getParam')
            ->with('controller')
            ->will($this->returnValue($controller));

        $this->quoteMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($quoteId));

        $this->cartManagementMock->expects($this->any())
            ->method('placeOrder')
            ->will($this->returnValue($orderId));

        $this->jsonHelperMock->expects($this->any())
            ->method('jsonEncode')
            ->with($result);

        $this->placeOrderController->execute();
    }

    /**
     * @param $paymentMethod
     * @param $controller
     * @param $quoteId
     * @param $result
     * @dataProvider textExecuteFailedPlaceOrderDataProvider
     */
    public function testExecuteFailedPlaceOrder(
        $paymentMethod,
        $controller,
        $quoteId,
        $result
    ) {
        $this->requestMock->expects($this->at(0))
            ->method('getParam')
            ->with('payment')
            ->will($this->returnValue($paymentMethod));

        $this->requestMock->expects($this->at(1))
            ->method('getParam')
            ->with('controller')
            ->will($this->returnValue($controller));

        $this->quoteMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($quoteId));

        $this->cartManagementMock->expects($this->once())
            ->method('placeOrder')
            ->willThrowException(new \Exception());

        $this->jsonHelperMock->expects($this->any())
            ->method('jsonEncode')
            ->with($result);

        $this->placeOrderController->execute();
    }

    /**
     * @return array
     */
    public function textExecuteDataProvider()
    {
        $objectSuccess = new \Magento\Framework\DataObject();
        $objectSuccess->setData('success', true);

        return [
            [
                ['method' => null],
                IframeConfigProvider::CHECKOUT_IDENTIFIER,
                1,
                1,
                ['error_messages' => __('Please choose a payment method.'), 'goto_section' => 'payment']
            ],
            [
                ['method' => 'authorizenet_directpost'],
                IframeConfigProvider::CHECKOUT_IDENTIFIER,
                1,
                1,
                $objectSuccess
            ],
        ];
    }

    /**
     * @return array
     */
    public function textExecuteFailedPlaceOrderDataProvider()
    {
        $objectFailed = new \Magento\Framework\DataObject();
        $objectFailed->setData('error', true);
        $objectFailed->setData(
            'error_messages',
            __('An error occurred on the server. Please try to place the order again.')
        );

        return [
            [
                ['method' => 'authorizenet_directpost'],
                IframeConfigProvider::CHECKOUT_IDENTIFIER,
                1,
                $objectFailed
            ],
        ];
    }
}
