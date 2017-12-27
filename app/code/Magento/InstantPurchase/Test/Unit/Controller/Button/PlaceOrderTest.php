<?php

namespace Magento\InstantPurchase\Test\Unit\Controller\Button;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InstantPurchase\Controller\Button\PlaceOrder;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Customer;
use Magento\InstantPurchase\Model\InstantPurchaseOptionLoadingFactory;
use Magento\InstantPurchase\Model\InstantPurchaseOption;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\InstantPurchase\Model\PlaceOrder as PlaceOrderModel;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;

class PlaceOrderTest extends TestCase
{
    const GENERIC_ERROR_MESSAGE ='Something went wrong while processing your order. Please try again later.';
    const KNOWN_REQUEST_PARAMS = [
        'form_key' => 'someFormKey',
        'product' => '1233',
        'instant_purchase_payment_token' => 'paymentTokenString',
        'instant_purchase_shipping_address' => '100',
        'instant_purchase_billing_address' => '101',
    ];

    /**
     * @var PlaceOrder
     */
    private $placeOrder;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var ResultFactory|MockObject
     */
    private $resultFactoryMock;

    /**
     * @var Json|MockObject
     */
    private $resultMock;

    /**
     * @var ManagerInterface|MockObject
     */
    private $messageManagerMock;

    /**
     * @var Validator|MockObject
     */
    private $formKeyValidatorMock;

    /**
     * @var Session|MockObject
     */
    private $sessionMock;

    /**
     * @var Customer|MockObject
     */
    private $customerMock;

    /**
     * @var InstantPurchaseOptionLoadingFactory|MockObject
     */
    private $optionLoadingFactoryMock;

    /**
     * @var InstantPurchaseOption
     */
    private $optionMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var StoreInterface|MockObject
     */
    private $storeMock;

    /**
     * @var ProductRepositoryInterface|MockObject
     */
    private $productRepositoryMock;

    /**
     * @var ProductInterface|MockObject
     */
    private $productMock;

    /**
     * @var PlaceOrderModel|MockObject
     */
    private $placeOrderModelMock;

    /**
     * @var OrderRepositoryInterface|MockObject
     */
    private $orderRepositoryMock;

    /**
     * @var OrderInterface|MockObject
     */
    private $orderMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->getMock();
        $this->resultFactoryMock = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultMock = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->getMock();
        $this->formKeyValidatorMock = $this->getMockBuilder(Validator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerMock = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->optionLoadingFactoryMock = $this->getMockBuilder(InstantPurchaseOptionLoadingFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->optionMock = $this->getMockBuilder(InstantPurchaseOption::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMock();
        $this->storeMock = $this->getMockBuilder(StoreInterface::class)
            ->getMock();
        $this->productRepositoryMock = $this->getMockBuilder(ProductRepositoryInterface::class)
            ->getMock();
        $this->productMock = $this->getMockBuilder(ProductInterface::class)
            ->getMock();
        $this->placeOrderModelMock = $this->getMockBuilder(PlaceOrderModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderRepositoryMock = $this->getMockBuilder(OrderRepositoryInterface::class)
            ->getMock();
        $this->orderMock = $this->getMockBuilder(OrderInterface::class)
            ->getMock();

        $this->resultFactoryMock->expects($this->any())
            ->method('create')
            ->with(ResultFactory::TYPE_JSON)
            ->willReturn($this->resultMock);

        $this->sessionMock->expects($this->any())
            ->method('getCustomer')
            ->willReturn($this->customerMock);

        $this->optionLoadingFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->optionMock);

        $this->placeOrder = $objectManager->getObject(
            PlaceOrder::class,
            [
                '_request' => $this->requestMock,
                'resultFactory' => $this->resultFactoryMock,
                'messageManager' => $this->messageManagerMock,
                'formKeyValidator' => $this->formKeyValidatorMock,
                'customerSession' => $this->sessionMock,
                'instantPurchaseOptionLoadingFactory' => $this->optionLoadingFactoryMock,
                'storeManager' => $this->storeManagerMock,
                'productRepository' => $this->productRepositoryMock,
                'placeOrder' => $this->placeOrderModelMock,
                'orderRepository' => $this->orderRepositoryMock,
            ]
        );
    }

    public function testExecuteWithoutAllKnownParams()
    {
        $this->requestMock->expects($this->once())
            ->method('getParams')
            ->willReturn([]);

        $this->resultMock->expects($this->once())
            ->method('setData')
            ->with(['response' => self::GENERIC_ERROR_MESSAGE]);
        
        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with(self::GENERIC_ERROR_MESSAGE);
        
        $this->assertInstanceOf(
            Json::class,
            $this->placeOrder->execute()
        );
    }

    public function testExecuteWithInvalidFormKey()
    {
        $this->requestMock->expects($this->any())
            ->method('getParams')
            ->willReturn(self::KNOWN_REQUEST_PARAMS);

        $this->formKeyValidatorMock->expects($this->once())
            ->method('validate')
            ->with($this->requestMock)
            ->willReturn(false);

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with(self::GENERIC_ERROR_MESSAGE);

        $this->assertInstanceOf(
            Json::class,
            $this->placeOrder->execute()
        );
    }

    public function testExecuteNoSuchEntityException()
    {
        $this->requestMock->expects($this->once())
            ->method('getParams')
            ->willReturn(self::KNOWN_REQUEST_PARAMS);

        $this->formKeyValidatorMock->expects($this->once())
            ->method('validate')
            ->with($this->requestMock)
            ->willReturn(true);

        $this->customerMock->expects($this->once())
            ->method('getId')
            ->willReturn('1');
        
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->storeMock->expects($this->once())
            ->method('getid')
            ->willReturn(1);
        
        $this->productRepositoryMock->expects($this->once())
            ->method('getById')
            ->with(
                (int)self::KNOWN_REQUEST_PARAMS['product'],
                false,
                1,
                false
            )->willThrowException(new NoSuchEntityException());

        $this->resultMock->expects($this->once())
            ->method('setData')
            ->with(['response' => self::GENERIC_ERROR_MESSAGE]);

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with(self::GENERIC_ERROR_MESSAGE);

        $this->assertInstanceOf(
            Json::class,
            $this->placeOrder->execute()
        );
    }

    public function testExecute()
    {
        $this->requestMock->expects($this->once())
            ->method('getParams')
            ->willReturn(self::KNOWN_REQUEST_PARAMS);

        $this->formKeyValidatorMock->expects($this->once())
            ->method('validate')
            ->with($this->requestMock)
            ->willReturn(true);

        $this->customerMock->expects($this->once())
            ->method('getId')
            ->willReturn('1');

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->storeMock->expects($this->once())
            ->method('getid')
            ->willReturn(1);

        $this->productRepositoryMock->expects($this->once())
            ->method('getById')
            ->with(
                (int)self::KNOWN_REQUEST_PARAMS['product'],
                false,
                1,
                false
            )->willReturn($this->productMock);

        $this->placeOrderModelMock->expects($this->once())
            ->method('placeOrder')
            ->willReturn(1);

        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($this->orderMock);

        $orderIncrementId = '100000001';
        $this->orderMock->expects($this->once())
            ->method('getIncrementId')
            ->willReturn($orderIncrementId);

        $successMessage = sprintf('Your order number is: %s.', $orderIncrementId);
        $this->resultMock->expects($this->once())
            ->method('setData')
            ->with(['response' => $successMessage]);

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with($successMessage);

        $this->assertInstanceOf(
            Json::class,
            $this->placeOrder->execute()
        );
    }
}
