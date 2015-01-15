<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Model\Type;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OnepageTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Checkout\Model\Type\Onepage */
    protected $onepage;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $eventManagerMock;

    /** @var \Magento\Checkout\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $checkoutHelperMock;

    /** @var \Magento\Customer\Model\Url|\PHPUnit_Framework_MockObject_MockObject */
    protected $customerUrlMock;

    /** @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $loggerMock;

    /** @var \Magento\Checkout\Model\Session|\PHPUnit_Framework_MockObject_MockObject */
    protected $checkoutSessionMock;

    /** @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject */
    protected $customerSessionMock;

    /** @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $storeManagerMock;

    /** @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $requestMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $addressFactoryMock;

    /** @var \Magento\Customer\Model\FormFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $customerFormFactoryMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $customerFactoryMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $quoteFactoryMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $orderFactoryMock;

    /** @var \Magento\Framework\Object\Copy|\PHPUnit_Framework_MockObject_MockObject */
    protected $copyMock;

    /** @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $messageManagerMock;

    /** @var \Magento\Customer\Model\Metadata\FormFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $formFactoryMock;

    /** @var \Magento\Customer\Api\Data\CustomerDataBuilder|\PHPUnit_Framework_MockObject_MockObject */
    protected $customerBuilderMock;

    /** @var \Magento\Customer\Api\Data\AddressDataBuilder|\PHPUnit_Framework_MockObject_MockObject */
    protected $addressBuilderMock;

    /** @var \Magento\Framework\Math\Random|\PHPUnit_Framework_MockObject_MockObject */
    protected $randomMock;

    /** @var \Magento\Framework\Encryption\EncryptorInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $encryptorMock;

    /** @var \Magento\Customer\Api\AddressRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $addressRepositoryMock;

    /** @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $customerRepositoryMock;

    /** @var \Magento\Sales\Model\QuoteRepository|\PHPUnit_Framework_MockObject_MockObject */
    protected $quoteRepositoryMock;

    /**
     * @var \Magento\Customer\Api\AccountManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $accountManagementMock;

    /** @var \Magento\Framework\Api\ExtensibleDataObjectConverter|\PHPUnit_Framework_MockObject_MockObject */
    protected $extensibleDataObjectConverterMock;

    protected function setUp()
    {
        $this->addressRepositoryMock = $this->getMockForAbstractClass(
            'Magento\Customer\Api\AddressRepositoryInterface',
            ['get'],
            '',
            false
        );
        $this->accountManagementMock = $this->getMockForAbstractClass(
            'Magento\Customer\Api\AccountManagementInterface',
            [],
            '',
            false
        );
        $this->eventManagerMock = $this->getMock('Magento\Framework\Event\ManagerInterface');
        $this->checkoutHelperMock = $this->getMock('Magento\Checkout\Helper\Data', [], [], '', false);
        $this->customerUrlMock = $this->getMock('Magento\Customer\Model\Url', [], [], '', false);
        $this->loggerMock = $this->getMock('Psr\Log\LoggerInterface');
        $this->checkoutSessionMock = $this->getMock(
            'Magento\Checkout\Model\Session',
            ['getLastOrderId', 'getQuote', 'setStepData', 'getStepData'],
            [],
            '',
            false
        );
        $this->customerSessionMock = $this->getMock(
            'Magento\Customer\Model\Session',
            ['getCustomerDataObject', 'isLoggedIn'],
            [],
            '',
            false
        );
        $this->storeManagerMock = $this->getMock('Magento\Store\Model\StoreManagerInterface');
        $this->requestMock = $this->getMock(
            'Magento\Framework\App\RequestInterface',
            ['isAjax', 'getModuleName', 'setModuleName', 'getActionName', 'setActionName', 'getParam', 'getCookie']
        );
        $this->addressFactoryMock = $this->getMock('Magento\Customer\Model\AddressFactory', [], [], '', false);
        $this->formFactoryMock = $this->getMock('Magento\Customer\Model\Metadata\FormFactory', [], [], '', false);
        $this->customerFactoryMock = $this->getMock('Magento\Customer\Model\CustomerFactory', [], [], '', false);
        $this->quoteFactoryMock = $this->getMock('Magento\Sales\Model\Service\QuoteFactory', [], [], '', false);
        $this->orderFactoryMock = $this->getMock('Magento\Sales\Model\OrderFactory', ['create'], [], '', false);
        $this->copyMock = $this->getMock('Magento\Framework\Object\Copy', [], [], '', false);
        $this->messageManagerMock = $this->getMock('Magento\Framework\Message\ManagerInterface');

        $this->customerFormFactoryMock = $this->getMock(
            'Magento\Customer\Model\FormFactory',
            ['create'],
            [],
            '',
            false
        );

        $this->customerBuilderMock = $this->getMock(
            'Magento\Customer\Api\Data\CustomerDataBuilder',
            [],
            [],
            '',
            false
        );

        $this->addressBuilderMock = $this->getMock(
            'Magento\Customer\Api\Data\AddressDataBuilder',
            [],
            [],
            '',
            false
        );

        $this->randomMock = $this->getMock('Magento\Framework\Math\Random');
        $this->encryptorMock = $this->getMock('Magento\Framework\Encryption\EncryptorInterface');

        $this->customerRepositoryMock = $this->getMockForAbstractClass(
            '\Magento\Customer\Api\CustomerRepositoryInterface',
            [],
            '',
            false
        );

        $orderSenderMock = $this->getMock(
            '\Magento\Sales\Model\Order\Email\Sender\OrderSender',
            [],
            [],
            '',
            false
        );

        $this->quoteRepositoryMock = $this->getMock(
            'Magento\Sales\Model\QuoteRepository',
            [],
            [],
            '',
            false
        );

        $this->extensibleDataObjectConverterMock = $this->getMockBuilder(
            'Magento\Framework\Api\ExtensibleDataObjectConverter'
        )->setMethods(['toFlatArray'])->disableOriginalConstructor()->getMock();

        $this->extensibleDataObjectConverterMock
            ->expects($this->any())
            ->method('toFlatArray')
            ->will($this->returnValue([]));
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->onepage = $this->objectManagerHelper->getObject(
            'Magento\Checkout\Model\Type\Onepage',
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
                'serviceQuoteFactory' => $this->quoteFactoryMock,
                'orderFactory' => $this->orderFactoryMock,
                'objectCopyService' => $this->copyMock,
                'messageManager' => $this->messageManagerMock,
                'formFactory' => $this->formFactoryMock,
                'customerBuilder' => $this->customerBuilderMock,
                'addressBuilder' => $this->addressBuilderMock,
                'mathRandom' => $this->randomMock,
                'encryptor' => $this->encryptorMock,
                'addressRepository' => $this->addressRepositoryMock,
                'accountManagement' => $this->accountManagementMock,
                'orderSenderMock' => $orderSenderMock,
                'customerRepository' => $this->customerRepositoryMock,
                'extensibleDataObjectConverter' => $this->extensibleDataObjectConverterMock,
                'quoteRepository' => $this->quoteRepositoryMock
            ]
        );
    }

    public function testGetQuote()
    {
        $returnValue = 'ababagalamaga';
        $this->checkoutSessionMock->expects($this->once())->method('getQuote')->will($this->returnValue($returnValue));
        $this->assertEquals($returnValue, $this->onepage->getQuote());
    }

    public function testSetQuote()
    {
        /** @var \Magento\Sales\Model\Quote $quoteMock */
        $quoteMock = $this->getMock('Magento\Sales\Model\Quote', [], [], '', false);
        $this->onepage->setQuote($quoteMock);
        $this->assertEquals($quoteMock, $this->onepage->getQuote());
    }

    /**
     * @dataProvider initCheckoutDataProvider
     */
    public function testInitCheckout($stepData, $isLoggedIn, $isSetStepDataCalled)
    {
        $customer = $this->getMockForAbstractClass(
            'Magento\Customer\Api\Data\CustomerInterface',
            [],
            '',
            false
        );
        /** @var \Magento\Sales\Model\Quote|\PHPUnit_Framework_MockObject_MockObject $quoteMock */
        $quoteMock = $this->getMock(
            'Magento\Sales\Model\Quote',
            [
                'isMultipleShippingAddresses',
                'removeAllAddresses',
                'save',
                'assignCustomer',
                'getData',
                'getCustomerId',
                '__wakeup',
                'getBillingAddress',
                'setPasswordHash',
                'getCheckoutMethod',
                'isVirtual',
                'getShippingAddress',
                'getCustomerData',
                'collectTotals',
            ],
            [],
            '',
            false
        );
        $quoteMock->expects($this->once())->method('isMultipleShippingAddresses')->will($this->returnValue(true));
        $quoteMock->expects($this->once())->method('removeAllAddresses');
        $quoteMock->expects($this->once())->method('assignCustomer')->with($customer);

        $this->quoteRepositoryMock->expects($this->once())->method('save')->with($quoteMock);

        $this->customerSessionMock
            ->expects($this->once())
            ->method('getCustomerDataObject')
            ->will($this->returnValue($customer));
        $this->customerSessionMock->expects($this->any())->method('isLoggedIn')->will($this->returnValue($isLoggedIn));
        $this->checkoutSessionMock->expects($this->once())->method('getQuote')->will($this->returnValue($quoteMock));
        $this->checkoutSessionMock->expects($this->any())->method('getStepData')->will($this->returnValue($stepData));

        if ($isSetStepDataCalled) {
            $this->checkoutSessionMock->expects($this->once())
                ->method('setStepData')
                ->with(key($stepData), 'allow', false);
        } else {
            $this->checkoutSessionMock->expects($this->never())->method('setStepData');
        }

        $this->onepage->initCheckout();
    }

    public function initCheckoutDataProvider()
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
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->will($this->returnValue($isLoggedIn));
        /** @var \Magento\Sales\Model\Quote|\PHPUnit_Framework_MockObject_MockObject $quoteMock */
        $quoteMock = $this->getMock('Magento\Sales\Model\Quote', [], [], '', false);
        $quoteMock->expects($this->any())->method('setCheckoutMethod')->with($expected);

        $quoteMock->expects($this->any())
            ->method('getCheckoutMethod')
            ->will($this->returnValue($quoteCheckoutMethod));

        $this->checkoutHelperMock
            ->expects($this->any())
            ->method('isAllowedGuestCheckout')
            ->will($this->returnValue($isAllowedGuestCheckout));

        $this->onepage->setQuote($quoteMock);
        $this->assertEquals($expected, $this->onepage->getCheckoutMethod());
    }

    public function getCheckoutMethodDataProvider()
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
        /** @var \Magento\Sales\Model\Quote|\PHPUnit_Framework_MockObject_MockObject $quoteMock */
        $quoteMock = $this->getMock(
            'Magento\Sales\Model\Quote',
            ['setCheckoutMethod', '__wakeup'],
            [],
            '',
            false
        );
        $quoteMock->expects($this->once())->method('setCheckoutMethod')->with('someMethod')->will($this->returnSelf());
        $this->quoteRepositoryMock->expects($this->once())->method('save')->with($quoteMock);
        $this->checkoutSessionMock->expects($this->once())->method('setStepData')->with('billing', 'allow', true);
        $this->onepage->setQuote($quoteMock);
        $this->assertEquals([], $this->onepage->saveCheckoutMethod('someMethod'));
    }

    public function testSaveBillingInvalidData()
    {
        $this->assertEquals(['error' => -1, 'message' => 'Invalid data'], $this->onepage->saveBilling([], 0));
    }

    /**
     * @dataProvider saveBillingDataProvider
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function testSaveBilling(
        $data,
        $customerAddressId,
        $quoteCustomerId,
        $addressCustomerId,
        $isAddress,
        $validateDataResult,
        $validateResult,
        $checkoutMethod,
        $customerPassword,
        $confirmPassword,
        $validationResultMessages,
        $isEmailAvailable,
        $isVirtual,
        $getStepDataResult,
        $expected
    ) {
        $useForShipping = (int)$data['use_for_shipping'];

        $passwordHash = 'password hash';
        $this->requestMock->expects($this->any())->method('isAjax')->will($this->returnValue(false));
        $customerValidationResultMock = $this->getMock(
            'Magento\Customer\Api\Data\ValidationResultsInterface', [], [], '', false
        );
        $customerValidationResultMock
            ->expects($this->any())
            ->method('isValid')
            ->will($this->returnValue(empty($validationResultMessages)));
        $customerValidationResultMock
            ->expects($this->any())
            ->method('getMessages')
            ->will($this->returnValue($validationResultMessages));
        $this->accountManagementMock
            ->expects($this->any())
            ->method('getPasswordHash')
            ->with($customerPassword)
            ->will($this->returnValue($passwordHash));
        $this->accountManagementMock
            ->expects($this->any())
            ->method('validate')
            ->will($this->returnValue($customerValidationResultMock));
        $this->accountManagementMock
            ->expects($this->any())
            ->method('isEmailAvailable')
            ->will($this->returnValue($isEmailAvailable));
        /** @var \Magento\Sales\Model\Quote|\PHPUnit_Framework_MockObject_MockObject $quoteMock */
        $quoteMock = $this->getMock(
            'Magento\Sales\Model\Quote',
            [
                'getData',
                'getCustomerId',
                '__wakeup',
                'getBillingAddress',
                'setPasswordHash',
                'getCheckoutMethod',
                'isVirtual',
                'getShippingAddress',
                'getCustomerData',
                'collectTotals',
                'save',
                'getCustomer'
            ],
            [],
            '',
            false
        );
        $customerMock = $this->getMockForAbstractClass(
            'Magento\Framework\Api\AbstractExtensibleObject',
            [],
            '',
            false,
            true,
            true,
            ['__toArray']
        );
        $shippingAddressMock = $this->getMock(
            'Magento\Sales\Model\Quote\Address',
            [
                'setSameAsBilling',
                'save',
                'collectTotals',
                'addData',
                'setShippingMethod',
                'setCollectShippingRates'
            ],
            [],
            '',
            false
        );
        $quoteMock->expects($this->any())->method('getShippingAddress')->will($this->returnValue($shippingAddressMock));

        $shippingAddressMock->expects($useForShipping ? $this->any() : $this->once())
            ->method('setSameAsBilling')
            ->with($useForShipping)
            ->will($this->returnSelf());

        $expects = (!$useForShipping || ($checkoutMethod != Onepage::METHOD_REGISTER)) ? $this->once() : $this->never();
        $shippingAddressMock->expects($expects)
            ->method('save');

        $shippingAddressMock->expects($useForShipping ? $this->once() : $this->never())
            ->method('addData')
            ->will($this->returnSelf());

        $shippingAddressMock->expects($this->any())
            ->method('setSaveInAddressBook')
            ->will($this->returnSelf());

        $shippingAddressMock->expects($useForShipping ? $this->once() : $this->never())
            ->method('setShippingMethod')
            ->will($this->returnSelf());

        $shippingAddressMock->expects($useForShipping ? $this->once() : $this->never())
            ->method('setCollectShippingRates')
            ->will($this->returnSelf());

        $shippingAddressMock->expects($useForShipping ? $this->once() : $this->never())
            ->method('collectTotals');

        $quoteMock->expects($this->any())->method('setPasswordHash')->with($passwordHash);
        $quoteMock->expects($this->any())->method('getCheckoutMethod')->will($this->returnValue($checkoutMethod));
        $quoteMock->expects($this->any())->method('isVirtual')->will($this->returnValue($isVirtual));

        $addressMock = $this->getMock(
            'Magento\Sales\Model\Quote\Address',
            [
                'setSaveInAddressBook',
                'getData',
                'setEmail',
                '__wakeup',
                'importCustomerAddressData',
                'validate',
                'save'
            ],
            [],
            '',
            false
        );
        $addressMock->expects($this->any())->method('importCustomerAddressData')->will($this->returnSelf());
        $addressMock->expects($this->atLeastOnce())->method('validate')->will($this->returnValue($validateResult));
        $addressMock->expects($this->any())->method('getData')->will($this->returnValue([]));

        $quoteMock->expects($this->any())->method('getBillingAddress')->will($this->returnValue($addressMock));
        $quoteMock->expects($this->any())->method('getCustomerId')->will($this->returnValue($quoteCustomerId));

        $this->quoteRepositoryMock
            ->expects($checkoutMethod === Onepage::METHOD_REGISTER ? $this->once() : $this->never())
            ->method('save')
            ->with($quoteMock);

        $addressMock->expects($checkoutMethod === Onepage::METHOD_REGISTER ? $this->never() : $this->once())
            ->method('save');

        $quoteMock->expects($this->any())->method('getCustomer')->will($this->returnValue($customerMock));
        $data1 = [];
        $extensibleDataObjectConverterMock = $this->getMock(
            'Magento\Framework\Api\ExtensibleDataObjectConverter',
            ['toFlatArray'],
            [],
            '',
            false
        );
        $extensibleDataObjectConverterMock->expects($this->any())
            ->method('toFlatArray')
            ->with($customerMock)
            ->will($this->returnValue($data1));

        $formMock = $this->getMock('Magento\Customer\Model\Metadata\Form', [], [], '', false);
        $formMock->expects($this->atLeastOnce())->method('validateData')->will($this->returnValue($validateDataResult));

        $this->formFactoryMock->expects($this->any())->method('create')->will($this->returnValue($formMock));
        $formMock->expects($this->any())->method('prepareRequest')->will($this->returnValue($this->requestMock));
        $formMock->expects($this->any())
            ->method('extractData')
            ->with($this->requestMock)
            ->will($this->returnValue([]));
        $formMock->expects($this->any())
            ->method('validateData')
            ->with([])
            ->will($this->returnValue(false));

        $customerDataMock = $this->getMock('Magento\Customer\Api\Data\CustomerInterface', [], [], '', false);

        $this->customerBuilderMock
            ->expects($this->any())
            ->method('create')
            ->will($this->returnValue($customerDataMock));

        $this->checkoutSessionMock->expects($this->any())->method('getQuote')->will($this->returnValue($quoteMock));
        $this->checkoutSessionMock->expects($this->any())
            ->method('getStepData')
            ->will($this->returnValue($useForShipping ? true : $getStepDataResult));
        $this->checkoutSessionMock->expects($this->any())->method('setStepData')->will($this->returnSelf());
        $customerAddressMock = $this->getMockForAbstractClass(
            'Magento\Customer\Api\Data\AddressInterface',
            [],
            '',
            false
        );
        $customerAddressMock->expects($this->any())
            ->method('getCustomerId')
            ->will($this->returnValue($addressCustomerId));
        $this->addressRepositoryMock->expects($this->any())
            ->method('getById')
            ->will($isAddress ? $this->returnValue($customerAddressMock) : $this->throwException(new \Exception()));

        $websiteMock = $this->getMock('Magento\Store\Model\Website', [], [], '', false);
        $this->storeManagerMock->expects($this->any())->method('getWebsite')->will($this->returnValue($websiteMock));
        $this->assertEquals($expected, $this->onepage->saveBilling($data, $customerAddressId));
    }

    public function saveBillingDataProvider()
    {
        return [
            [
                ['use_for_shipping' => 0], // $data
                1, // $customerAddressId
                1, // $quoteCustomerId
                1, // $addressCustomerId
                true, //$isAddress
                true, // $validateDataResult
                true, // $validateResult
                Onepage::METHOD_REGISTER, // $checkoutMethod
                'password', // $customerPassword
                'password', // $confirmPassword
                [], // $validationResultMessages
                true, // $isEmailAvailable
                false, // $isVirtual
                false, // $getStepDataResult
                [], // $expected
            ],
            [
                ['use_for_shipping' => 1], // $data
                1, // $customerAddressId
                1, // $quoteCustomerId
                1, // $addressCustomerId
                true, //$isAddress
                true, // $validateDataResult
                true, // $validateResult
                Onepage::METHOD_CUSTOMER, // $checkoutMethod
                'password', // $customerPassword
                'password', // $confirmPassword
                [], // $validationResultMessages
                true, // $isEmailAvailable
                false, // $isVirtual
                false, // $getStepDataResult
                [], // $expected
            ]
        ];
    }

    public function testGetLastOrderId()
    {
        $orderIncrementId = 100001;
        $orderId = 1;
        $this->checkoutSessionMock->expects($this->once())->method('getLastOrderId')
            ->will($this->returnValue($orderId));
        $orderMock = $this->getMock('Magento\Sales\Model\Order', ['load', 'getIncrementId', '__wakeup'], [], '', false);
        $orderMock->expects($this->once())->method('load')->with($orderId)->will($this->returnSelf());
        $orderMock->expects($this->once())->method('getIncrementId')->will($this->returnValue($orderIncrementId));
        $this->orderFactoryMock->expects($this->once())->method('create')->will($this->returnValue($orderMock));
        $this->assertEquals($orderIncrementId, $this->onepage->getLastOrderId());
    }
}
