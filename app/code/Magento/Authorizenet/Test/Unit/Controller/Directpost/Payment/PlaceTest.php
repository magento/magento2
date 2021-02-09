<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Model\IframeConfigProvider;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Model\Quote;

/**
 * Test for Place
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PlaceTest extends \PHPUnit\Framework\TestCase
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
     * @var Context|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $contextMock;

    /**
     * @var Registry|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $coreRegistryMock;

    /**
     * @var DataFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $dataFactoryMock;

    /**
     * @var CartManagementInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $cartManagementMock;

    /**
     * @var Onepage|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $onepageCheckout;

    /**
     * @var Data|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $jsonHelperMock;

    /**
     * @var RequestInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $requestMock;

    /**
     * @var Http|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $responseMock;

    /**
     * @var ObjectManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $objectManagerMock;

    /**
     * @var DirectpostSession|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $directpostSessionMock;

    /**
     * @var Quote|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $quoteMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $loggerMock;

    /**
     * @var CheckoutSession|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $checkoutSessionMock;

    protected function setUp(): void
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
            ->willReturn($this->quoteMock);
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
        $this->loggerMock = $this
            ->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->getMock();

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
                'logger' => $this->loggerMock,
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
            ->willReturn($paymentMethod);

        $this->requestMock->expects($this->at(1))
            ->method('getParam')
            ->with('controller')
            ->willReturn($controller);

        $this->quoteMock->expects($this->any())
            ->method('getId')
            ->willReturn($quoteId);

        $this->cartManagementMock->expects($this->any())
            ->method('placeOrder')
            ->willReturn($orderId);

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
     * @param \Exception $exception Exception to check
     * @dataProvider textExecuteFailedPlaceOrderDataProvider
     */
    public function testExecuteFailedPlaceOrder(
        $paymentMethod,
        $controller,
        $quoteId,
        $result,
        $exception
    ) {
        $this->requestMock->expects($this->at(0))
            ->method('getParam')
            ->with('payment')
            ->willReturn($paymentMethod);

        $this->requestMock->expects($this->at(1))
            ->method('getParam')
            ->with('controller')
            ->willReturn($controller);

        $this->quoteMock->expects($this->any())
            ->method('getId')
            ->willReturn($quoteId);

        $this->cartManagementMock->expects($this->once())
            ->method('placeOrder')
            ->willThrowException($exception);

        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with($exception);

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
        $objectFailed1 = new \Magento\Framework\DataObject(
            [
                'error' => true,
                'error_messages' => __(
                    'A server error stopped your order from being placed. Please try to place your order again.'
                )
            ]
        );
        $generalException = new \Exception('Exception logging will save the world!');
        $localizedException = new LocalizedException(__('Electronic payments save the trees.'));
        $objectFailed2 = new \Magento\Framework\DataObject(
            [
                'error' => true,
                'error_messages' => $localizedException->getMessage()
            ]
        );

        return [
            [
                ['method' => 'authorizenet_directpost'],
                IframeConfigProvider::CHECKOUT_IDENTIFIER,
                1,
                $objectFailed1,
                $generalException,
            ],
            [
                ['method' => 'authorizenet_directpost'],
                IframeConfigProvider::CHECKOUT_IDENTIFIER,
                1,
                $objectFailed2,
                $localizedException,
            ],
        ];
    }
}
