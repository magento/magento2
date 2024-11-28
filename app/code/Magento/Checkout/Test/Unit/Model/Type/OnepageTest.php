<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Model\Type;

use Magento\Checkout\Helper\Data;
use Magento\Checkout\Model\Session;
use Magento\Checkout\Model\Type\Onepage;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\AddressFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\FormFactory;
use Magento\Customer\Model\Url;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\App\Request\Http;
use Magento\Framework\DataObject\Copy;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Math\Random;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\TotalsCollector;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\OrderFactory;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OnepageTest extends TestCase
{
    /** @var Onepage */
    protected $onepage;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var ManagerInterface|MockObject */
    protected $eventManagerMock;

    /** @var Data|MockObject */
    protected $checkoutHelperMock;

    /** @var Url|MockObject */
    protected $customerUrlMock;

    /** @var LoggerInterface|MockObject */
    protected $loggerMock;

    /** @var Session|MockObject */
    protected $checkoutSessionMock;

    /** @var \Magento\Customer\Model\Session|MockObject */
    protected $customerSessionMock;

    /** @var StoreManagerInterface|MockObject */
    protected $storeManagerMock;

    /** @var Http|MockObject */
    protected $requestMock;

    /** @var MockObject */
    protected $addressFactoryMock;

    /** @var FormFactory|MockObject */
    protected $customerFormFactoryMock;

    /** @var MockObject */
    protected $customerFactoryMock;

    /** @var MockObject */
    protected $quoteManagementMock;

    /** @var MockObject */
    protected $orderFactoryMock;

    /** @var Copy|MockObject */
    protected $copyMock;

    /** @var \Magento\Framework\Message\ManagerInterface|MockObject */
    protected $messageManagerMock;

    /** @var \Magento\Customer\Model\Metadata\FormFactory|MockObject */
    protected $formFactoryMock;

    /** @var CustomerInterfaceFactory|MockObject */
    protected $customerDataFactoryMock;

    /** @var Random|MockObject */
    protected $randomMock;

    /** @var EncryptorInterface|MockObject */
    protected $encryptorMock;

    /** @var AddressRepositoryInterface|MockObject */
    protected $addressRepositoryMock;

    /** @var CustomerRepositoryInterface|MockObject */
    protected $customerRepositoryMock;

    /** @var CartRepositoryInterface|MockObject */
    protected $quoteRepositoryMock;

    /**
     * @var AccountManagementInterface|MockObject
     */
    protected $accountManagementMock;

    /** @var ExtensibleDataObjectConverter|MockObject */
    protected $extensibleDataObjectConverterMock;

    /** @var MockObject */
    protected $totalsCollectorMock;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $this->addressRepositoryMock = $this->getMockForAbstractClass(
            AddressRepositoryInterface::class,
            ['get'],
            '',
            false
        );
        $this->accountManagementMock = $this->getMockForAbstractClass(
            AccountManagementInterface::class,
            [],
            '',
            false
        );
        $this->eventManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->checkoutHelperMock = $this->createMock(Data::class);
        $this->customerUrlMock = $this->createMock(Url::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->checkoutSessionMock = $this->getMockBuilder(Session::class)
            ->addMethods(['getLastOrderId'])
            ->onlyMethods(['getQuote', 'setStepData', 'getStepData'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerSessionMock = $this->createPartialMock(
            \Magento\Customer\Model\Session::class,
            ['getCustomerDataObject', 'isLoggedIn']
        );
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->requestMock = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->addressFactoryMock = $this->createMock(AddressFactory::class);
        $this->formFactoryMock = $this->createMock(\Magento\Customer\Model\Metadata\FormFactory::class);
        $this->customerFactoryMock = $this->createMock(CustomerFactory::class);
        $this->quoteManagementMock = $this->getMockForAbstractClass(CartManagementInterface::class);
        $this->orderFactoryMock = $this->createPartialMock(OrderFactory::class, ['create']);
        $this->copyMock = $this->createMock(Copy::class);
        $this->messageManagerMock = $this->createMock(\Magento\Framework\Message\ManagerInterface::class);

        $this->customerFormFactoryMock = $this->createPartialMock(
            FormFactory::class,
            ['create']
        );

        $this->customerDataFactoryMock = $this->createMock(CustomerInterfaceFactory::class);

        $this->randomMock = $this->createMock(Random::class);
        $this->encryptorMock = $this->getMockForAbstractClass(EncryptorInterface::class);

        $this->customerRepositoryMock = $this->getMockForAbstractClass(
            CustomerRepositoryInterface::class,
            [],
            '',
            false
        );

        $orderSenderMock = $this->createMock(OrderSender::class);

        $this->quoteRepositoryMock = $this->getMockForAbstractClass(CartRepositoryInterface::class);

        $this->extensibleDataObjectConverterMock = $this->getMockBuilder(
            ExtensibleDataObjectConverter::class
        )->onlyMethods(['toFlatArray'])->disableOriginalConstructor()
            ->getMock();

        $this->extensibleDataObjectConverterMock
            ->expects($this->any())
            ->method('toFlatArray')
            ->willReturn([]);
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->totalsCollectorMock = $this->createMock(TotalsCollector::class);
        $this->onepage = $this->objectManagerHelper->getObject(
            Onepage::class,
            [
                'eventManager' => $this->eventManagerMock,
                'helper' => $this->checkoutHelperMock,
                'customerUrl' => $this->customerUrlMock,
                'logger' => $this->loggerMock,
                'checkoutSession' => $this->checkoutSessionMock,
                'customerSession' => $this->customerSessionMock,
                'storeManager' => $this->storeManagerMock,
                'request' => $this->requestMock,
                'customrAddrFactory' => $this->addressFactoryMock,
                'customerFormFactory' => $this->customerFormFactoryMock,
                'customerFactory' => $this->customerFactoryMock,
                'orderFactory' => $this->orderFactoryMock,
                'objectCopyService' => $this->copyMock,
                'messageManager' => $this->messageManagerMock,
                'formFactory' => $this->formFactoryMock,
                'customerDataFactory' => $this->customerDataFactoryMock,
                'mathRandom' => $this->randomMock,
                'encryptor' => $this->encryptorMock,
                'addressRepository' => $this->addressRepositoryMock,
                'accountManagement' => $this->accountManagementMock,
                'orderSenderMock' => $orderSenderMock,
                'customerRepository' => $this->customerRepositoryMock,
                'extensibleDataObjectConverter' => $this->extensibleDataObjectConverterMock,
                'quoteRepository' => $this->quoteRepositoryMock,
                'quoteManagement' => $this->quoteManagementMock,
                'totalsCollector' => $this->totalsCollectorMock
            ]
        );
    }

    public function testGetQuote()
    {
        $returnValue = 'ababagalamaga';
        $this->checkoutSessionMock->expects($this->once())->method('getQuote')->willReturn($returnValue);
        $this->assertEquals($returnValue, $this->onepage->getQuote());
    }

    public function testSetQuote()
    {
        /** @var Quote $quoteMock */
        $quoteMock = $this->createMock(Quote::class);
        $this->onepage->setQuote($quoteMock);
        $this->assertEquals($quoteMock, $this->onepage->getQuote());
    }

    /**
     * @dataProvider initCheckoutDataProvider
     */
    public function testInitCheckout($stepData, $isLoggedIn, $isSetStepDataCalled)
    {
        $customer = $this->getMockForAbstractClass(
            CustomerInterface::class,
            [],
            '',
            false
        );
        /** @var Quote|MockObject $quoteMock */
        $quoteMock = $this->getMockBuilder(Quote::class)
            ->addMethods(['getCustomerId', 'setPasswordHash', 'getCustomerData'])
            ->onlyMethods(
                [
                    'isMultipleShippingAddresses',
                    'removeAllAddresses',
                    'save',
                    'assignCustomer',
                    'getData',
                    'getBillingAddress',
                    'getCheckoutMethod',
                    'isVirtual',
                    'getShippingAddress',
                    'collectTotals'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $quoteMock->expects($this->once())->method('isMultipleShippingAddresses')->willReturn(true);
        $quoteMock->expects($this->once())->method('removeAllAddresses');
        $quoteMock->expects($this->once())->method('assignCustomer')->with($customer);

        $this->quoteRepositoryMock->expects($this->once())->method('save')->with($quoteMock);

        $this->customerSessionMock
            ->expects($this->once())
            ->method('getCustomerDataObject')
            ->willReturn($customer);
        $this->customerSessionMock->expects($this->any())->method('isLoggedIn')->willReturn($isLoggedIn);
        $this->checkoutSessionMock->expects($this->once())->method('getQuote')->willReturn($quoteMock);
        $this->checkoutSessionMock->expects($this->any())->method('getStepData')->willReturn($stepData);

        if ($isSetStepDataCalled) {
            $this->checkoutSessionMock->expects($this->once())
                ->method('setStepData')
                ->with(key($stepData), 'allow', false);
        } else {
            $this->checkoutSessionMock->expects($this->never())->method('setStepData');
        }

        $this->onepage->initCheckout();
    }

    /**
     * @return array
     */
    public static function initCheckoutDataProvider()
    {
        return [
            [['login' => ''], false, false],
            [['someStep' => ''], true, true],
            [['billing' => ''], true, false],
        ];
    }

    /**
     * @dataProvider getCheckoutMethodDataProvider
     */
    public function testGetCheckoutMethod($isLoggedIn, $quoteCheckoutMethod, $isAllowedGuestCheckout, $expected)
    {
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->willReturn($isLoggedIn);
        /** @var Quote|MockObject $quoteMock */
        $quoteMock = $this->createMock(Quote::class);
        $quoteMock->expects($this->any())->method('setCheckoutMethod')->with($expected);

        $quoteMock->expects($this->any())
            ->method('getCheckoutMethod')
            ->willReturn($quoteCheckoutMethod);

        $this->checkoutHelperMock
            ->expects($this->any())
            ->method('isAllowedGuestCheckout')
            ->willReturn($isAllowedGuestCheckout);

        $this->onepage->setQuote($quoteMock);
        $this->assertEquals($expected, $this->onepage->getCheckoutMethod());
    }

    /**
     * @return array
     */
    public static function getCheckoutMethodDataProvider()
    {
        return [
            // isLoggedIn(), getQuote()->getCheckoutMethod(), isAllowedGuestCheckout(), expected
            [true, null, false, Onepage::METHOD_CUSTOMER],
            [false, 'something else', false, 'something else'],
            [false, Onepage::METHOD_GUEST, true, Onepage::METHOD_GUEST],
            [false, Onepage::METHOD_REGISTER, false, Onepage::METHOD_REGISTER],
        ];
    }

    public function testSaveCheckoutMethod()
    {
        $this->assertEquals(['error' => -1, 'message' => 'Invalid data'], $this->onepage->saveCheckoutMethod(null));
        /** @var Quote|MockObject $quoteMock */
        $quoteMock = $this->createPartialMock(Quote::class, ['setCheckoutMethod']);
        $quoteMock->expects($this->once())->method('setCheckoutMethod')->with('someMethod')->willReturnSelf();
        $this->quoteRepositoryMock->expects($this->once())->method('save')->with($quoteMock);
        $this->checkoutSessionMock->expects($this->once())->method('setStepData')->with('billing', 'allow', true);
        $this->onepage->setQuote($quoteMock);
        $this->assertEquals([], $this->onepage->saveCheckoutMethod('someMethod'));
    }

    public function testGetLastOrderId()
    {
        $orderIncrementId = 100001;
        $orderId = 1;
        $this->checkoutSessionMock->expects($this->once())->method('getLastOrderId')
            ->willReturn($orderId);
        $orderMock = $this->createPartialMock(
            Order::class,
            ['load', 'getIncrementId']
        );
        $orderMock->expects($this->once())->method('load')->with($orderId)->willReturnSelf();
        $orderMock->expects($this->once())->method('getIncrementId')->willReturn($orderIncrementId);
        $this->orderFactoryMock->expects($this->once())->method('create')->willReturn($orderMock);
        $this->assertEquals($orderIncrementId, $this->onepage->getLastOrderId());
    }
}
