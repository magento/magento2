<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Test\Unit\Controller\Directpost\Payment;

use Magento\Authorizenet\Controller\Directpost\Payment\Place;
use Magento\Authorizenet\Helper\DataFactory;
use Magento\Authorizenet\Model\Directpost\Session as DirectpostSession;
use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
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
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
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
     * @var CheckoutSession|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutSessionMock;

    /**
     * @var CustomerSession|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSessionMock;

    /**
     * @var Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    /**
     * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $jsonHelperMock;

    /**
     * @var Cart|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cartMock;

    public function setUp()
    {
        $this->cartMock = $this
            ->getMockBuilder('Magento\Checkout\Model\Cart')
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this
            ->getMockBuilder('Magento\Framework\App\RequestInterface')
            ->getMockForAbstractClass();
        $this->responseMock = $this
            ->getMockBuilder('Magento\Framework\App\ResponseInterface')
            ->setMethods(['representJson'])
            ->getMockForAbstractClass();
        $this->responseMock->expects($this->any())
            ->method('representJson');
        $this->jsonHelperMock = $this
            ->getMockBuilder('Magento\Framework\Json\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();
        $this->directpostSessionMock = $this
            ->getMockBuilder('Magento\Authorizenet\Model\Directpost\Session')
            ->setMethods(['setQuoteId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->directpostSessionMock->expects($this->any())
            ->method('setQuoteId');
        $this->quoteMock = $this
            ->getMockBuilder('Magento\Quote\Model\Quote')
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerSessionMock = $this
            ->getMockBuilder('Magento\Customer\Model\Session')
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventManagerMock = $this
            ->getMockBuilder('Magento\Framework\Event\ManagerInterface')
            ->getMockForAbstractClass();
        $this->eventManagerMock->expects($this->any())
            ->method('dispatch')
            ->with('checkout_directpost_placeOrder');
        $this->checkoutSessionMock = $this
            ->getMockBuilder('Magento\Checkout\Model\Session')
            ->disableOriginalConstructor()
            ->getMock();
        $this->checkoutSessionMock->expects($this->any())
            ->method('getQuote')
            ->will($this->returnValue($this->quoteMock));
        $this->objectManagerMock = $this
            ->getMockBuilder('Magento\Framework\ObjectManagerInterface')
            ->getMockForAbstractClass();
        $this->objectManagerMock->expects($this->any())
            ->method('get')
            ->willReturnMap([
                ['Magento\Authorizenet\Model\Directpost\Session', $this->directpostSessionMock],
                ['Magento\Checkout\Model\Session', $this->checkoutSessionMock],
                ['Magento\Framework\Json\Helper\Data', $this->jsonHelperMock],
                ['Magento\Checkout\Model\Cart', $this->cartMock],
            ]);
        $this->contextMock = $this
            ->getMockBuilder('Magento\Framework\App\Action\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($this->requestMock));
        $this->contextMock->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($this->responseMock));
        $this->contextMock->expects($this->any())
            ->method('getObjectManager')
            ->will($this->returnValue($this->objectManagerMock));
        $this->contextMock->expects($this->any())
            ->method('getEventManager')
            ->will($this->returnValue($this->eventManagerMock));
        $this->coreRegistryMock = $this
            ->getMockBuilder('Magento\Framework\Registry')
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataFactoryMock = $this
            ->getMockBuilder('Magento\Authorizenet\Helper\DataFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->cartManagementMock = $this
            ->getMockBuilder('Magento\Quote\Api\CartManagementInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->placeOrderController = new Place(
            $this->contextMock,
            $this->coreRegistryMock,
            $this->dataFactoryMock,
            $this->cartManagementMock
        );
    }

    /**
     * @param $paymentMethod
     * @param $controller
     * @param $quoteId
     * @param $isLoggedIn
     * @param $orderId
     * @param $result
     * @dataProvider textExecuteDataProvider
     */
    public function testExecute(
        $paymentMethod,
        $controller,
        $quoteId,
        $isLoggedIn,
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

        $this->cartMock->expects($this->any())
            ->method('getCustomerSession')
            ->will($this->returnValue($this->customerSessionMock));

        $this->customerSessionMock->expects($this->any())
            ->method('isLoggedIn')
            ->will($this->returnValue($isLoggedIn));

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
     * @param $isLoggedIn
     * @param $result
     * @dataProvider textExecuteFailedPlaceOrderDataProvider
     */
    public function testExecuteFailePlaceOrder(
        $paymentMethod,
        $controller,
        $quoteId,
        $isLoggedIn,
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

        $this->cartMock->expects($this->any())
            ->method('getCustomerSession')
            ->will($this->returnValue($this->customerSessionMock));

        $this->customerSessionMock->expects($this->any())
            ->method('isLoggedIn')
            ->will($this->returnValue($isLoggedIn));

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
        $objectSuccess = new \Magento\Framework\Object();
        $objectSuccess->setData('success', true);

        return [
            [
                ['method' => null],
                IframeConfigProvider::CHECKOUT_IDENTIFIER,
                1,
                true,
                1,
                ['error_messages' => __('Please choose a payment method.'), 'goto_section' => 'payment']
            ],
            [
                ['method' => 'authorizenet_directpost'],
                IframeConfigProvider::CHECKOUT_IDENTIFIER,
                1,
                true,
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
        $objectFailed = new \Magento\Framework\Object();
        $objectFailed->setData('error', true);
        $objectFailed->setData('error_messages', __('Cannot place order.'));

        return [
            [
                ['method' => 'authorizenet_directpost'],
                IframeConfigProvider::CHECKOUT_IDENTIFIER,
                1,
                true,
                $objectFailed
            ],
        ];
    }
}
