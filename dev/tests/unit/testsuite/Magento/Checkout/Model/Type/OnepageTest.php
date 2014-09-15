<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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

    /** @var \Magento\Customer\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $customerHelperMock;

    /** @var \Magento\Framework\Logger|\PHPUnit_Framework_MockObject_MockObject */
    protected $loggerMock;

    /** @var \Magento\Checkout\Model\Session|\PHPUnit_Framework_MockObject_MockObject */
    protected $checkoutSessionMock;

    /** @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject */
    protected $customerSessionMock;

    /** @var \Magento\Framework\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $storeManagerMock;

    /** @var \Magento\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $requestMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $addressFactoryMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $formFactoryMock;

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
    protected $customerFormFactoryMock;

    /** @var \Magento\Customer\Service\V1\Data\CustomerBuilder|\PHPUnit_Framework_MockObject_MockObject */
    protected $customerBuilderMock;

    /** @var \Magento\Customer\Service\V1\Data\AddressBuilder|\PHPUnit_Framework_MockObject_MockObject */
    protected $addressBuilderMock;

    /** @var \Magento\Framework\Math\Random|\PHPUnit_Framework_MockObject_MockObject */
    protected $randomMock;

    /** @var \Magento\Framework\Encryption\EncryptorInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $encryptorMock;

    /** @var \Magento\Customer\Service\V1\CustomerAddressServiceInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $customerAddressServiceMock;

    /** @var \Magento\Customer\Service\V1\CustomerAccountServiceInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $customerAccountServiceMock;

    protected function setUp()
    {
        $this->eventManagerMock = $this->getMock('Magento\Framework\Event\ManagerInterface');
        $this->checkoutHelperMock = $this->getMock('Magento\Checkout\Helper\Data', [], [], '', false);
        $this->customerHelperMock = $this->getMock('Magento\Customer\Helper\Data', [], [], '', false);
        $this->loggerMock = $this->getMock('Magento\Framework\Logger', [], [], '', false);
        $this->checkoutSessionMock = $this->getMock('Magento\Checkout\Model\Session',
            ['getLastOrderId', 'getQuote', 'setStepData', 'getStepData'], [], '', false);
        $this->customerSessionMock = $this->getMock('Magento\Customer\Model\Session', [], [], '', false);
        $this->storeManagerMock = $this->getMock('Magento\Framework\StoreManagerInterface');
        $this->requestMock = $this->getMock(
            'Magento\Framework\App\RequestInterface',
            ['isAjax', 'getModuleName', 'setModuleName', 'getActionName', 'setActionName', 'getParam', 'getCookie']
        );
        $this->addressFactoryMock = $this->getMock('Magento\Customer\Model\AddressFactory');
        $this->formFactoryMock = $this->getMock('Magento\Customer\Model\FormFactory');
        $this->customerFactoryMock = $this->getMock('Magento\Customer\Model\CustomerFactory');
        $this->quoteFactoryMock = $this->getMock('Magento\Sales\Model\Service\QuoteFactory');
        $this->orderFactoryMock = $this->getMock('Magento\Sales\Model\OrderFactory', ['create'], [], '', false);
        $this->copyMock = $this->getMock('Magento\Framework\Object\Copy', [], [], '', false);
        $this->messageManagerMock = $this->getMock('Magento\Framework\Message\ManagerInterface');

        $this->customerFormFactoryMock = $this->getMock(
            'Magento\Customer\Model\Metadata\FormFactory',
            [],
            [],
            '',
            false
        );

        $this->customerBuilderMock = $this->getMock(
            'Magento\Customer\Service\V1\Data\CustomerBuilder',
            [],
            [],
            '',
            false
        );

        $this->addressBuilderMock = $this->getMock(
            'Magento\Customer\Service\V1\Data\AddressBuilder',
            [],
            [],
            '',
            false
        );

        $this->randomMock = $this->getMock('Magento\Framework\Math\Random');
        $this->encryptorMock = $this->getMock('Magento\Framework\Encryption\EncryptorInterface');

        $this->customerAddressServiceMock = $this->getMock(
            'Magento\Customer\Service\V1\CustomerAddressServiceInterface'
        );

        $this->customerAccountServiceMock = $this->getMock(
            'Magento\Customer\Service\V1\CustomerAccountServiceInterface'
        );

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->onepage = $this->objectManagerHelper->getObject(
            'Magento\Checkout\Model\Type\Onepage',
            [
                'eventManager' => $this->eventManagerMock,
                'helper' => $this->checkoutHelperMock,
                'customerData' => $this->customerHelperMock,
                'logger' => $this->loggerMock,
                'checkoutSession' => $this->checkoutSessionMock,
                'customerSession' => $this->customerSessionMock,
                'storeManager' => $this->storeManagerMock,
                'request' => $this->requestMock,
                'customrAddrFactory' => $this->addressFactoryMock,
                'customerFormFactory' => $this->formFactoryMock,
                'customerFactory' => $this->customerFactoryMock,
                'serviceQuoteFactory' => $this->quoteFactoryMock,
                'orderFactory' => $this->orderFactoryMock,
                'objectCopyService' => $this->copyMock,
                'messageManager' => $this->messageManagerMock,
                'formFactory' => $this->customerFormFactoryMock,
                'customerBuilder' => $this->customerBuilderMock,
                'addressBuilder' => $this->addressBuilderMock,
                'mathRandom' => $this->randomMock,
                'encryptor' => $this->encryptorMock,
                'customerAddressService' => $this->customerAddressServiceMock,
                'accountService' => $this->customerAccountServiceMock
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
        $customer = 'customer';
        /** @var \Magento\Sales\Model\Quote|\PHPUnit_Framework_MockObject_MockObject $quoteMock */
        $quoteMock = $this->getMock('Magento\Sales\Model\Quote', [], [], '', false);
        $quoteMock->expects($this->once())->method('isMultipleShippingAddresses')->will($this->returnValue(true));
        $quoteMock->expects($this->once())->method('removeAllAddresses');
        $quoteMock->expects($this->once())->method('save');
        $quoteMock->expects($this->once())->method('assignCustomer')->with($customer);

        $this->customerSessionMock
            ->expects($this->once())
            ->method('getCustomerDataObject')
            ->will($this->returnValue($customer));
        $this->customerSessionMock->expects($this->any())->method('isLoggedIn')->will($this->returnValue($isLoggedIn));
        $this->checkoutSessionMock->expects($this->once())->method('getQuote')->will($this->returnValue($quoteMock));
        $this->checkoutSessionMock->expects($this->any())->method('getStepData')->will($this->returnValue($stepData));

        if ($isSetStepDataCalled) {
            $this->checkoutSessionMock
                ->expects($this->once())
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

        $quoteMock
            ->expects($this->any())
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
            ['setCheckoutMethod', 'save', '__wakeup'],
            [],
            '',
            false
        );
        $quoteMock->expects($this->once())->method('save');
        $quoteMock->expects($this->once())->method('setCheckoutMethod')->with('someMethod')->will($this->returnSelf());
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
        $passwordHash = 'password hash';
        $this->requestMock->expects($this->any())->method('isAjax')->will($this->returnValue(false));
        $customerValidationResultMock = $this->getMock(
            'Magento\Customer\Service\V1\Data\CustomerValidationResults', [], [], '', false
        );
        $customerValidationResultMock
            ->expects($this->any())
            ->method('isValid')
            ->will($this->returnValue(empty($validationResultMessages)));
        $customerValidationResultMock
            ->expects($this->any())
            ->method('getMessages')
            ->will($this->returnValue($validationResultMessages));
        $this->customerAccountServiceMock
            ->expects($this->any())
            ->method('getPasswordHash')
            ->with($customerPassword)
            ->will($this->returnValue($passwordHash));
        $this->customerAccountServiceMock
            ->expects($this->any())
            ->method('validateCustomerData')
            ->will($this->returnValue($customerValidationResultMock));
        $this->customerAccountServiceMock
            ->expects($this->any())
            ->method('isEmailAvailable')
            ->will($this->returnValue($isEmailAvailable));
        /** @var \Magento\Sales\Model\Quote|\PHPUnit_Framework_MockObject_MockObject $quoteMock */
        $quoteMock = $this->getMock(
            'Magento\Sales\Model\Quote',
            [
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
            ],
            [],
            '',
            false
        );
        $shippingAddressMock = $this->getMock(
            'Magento\Sales\Model\Quote\Address',
            ['setSameAsBilling', '__wakeup', 'unserialize'],
            [],
            '',
            false
        );
        $shippingAddressMock->expects($this->any())->method('setSameAsBilling')->with((int)$data['use_for_shipping']);
        $quoteMock->expects($this->any())->method('setPasswordHash')->with($passwordHash);
        $quoteMock->expects($this->any())->method('getCheckoutMethod')->will($this->returnValue($checkoutMethod));
        $quoteMock->expects($this->any())->method('isVirtual')->will($this->returnValue($isVirtual));
        $quoteMock->expects($this->any())->method('getShippingAddress')->will($this->returnValue($shippingAddressMock));
        $addressMock = $this->getMock(
            'Magento\Sales\Model\Quote\Address',
            ['setSaveInAddressBook', 'getData', 'setEmail', '__wakeup', 'importCustomerAddressData', 'validate'],
            [],
            '',
            false
        );
        $addressMock->expects($this->any())->method('importCustomerAddressData')->will($this->returnSelf());
        $addressMock->expects($this->atLeastOnce())->method('validate')->will($this->returnValue($validateResult));
        $addressMock->expects($this->any())->method('getData')->will($this->returnValue([]));
        $quoteMock->expects($this->any())->method('getBillingAddress')->will($this->returnValue($addressMock));
        $quoteMock->expects($this->any())->method('getCustomerId')->will($this->returnValue($quoteCustomerId));
        $formMock = $this->getMock('Magento\Customer\Model\Metadata\Form', [], [], '', false);
        $formMock->expects($this->atLeastOnce())->method('validateData')->will($this->returnValue($validateDataResult));
        $this->requestMock
            ->expects($this->any())
            ->method('getParam')
            ->will(
                $this->returnValueMap(
                    [['customer_password', $customerPassword], ['confirm_password', $confirmPassword]]
                )
            );
        $formMock->expects($this->any())->method('prepareRequest')->will($this->returnValue($this->requestMock));
        $this->customerFormFactoryMock->expects($this->any())->method('create')->will($this->returnValue($formMock));
        $customerDataMock = $this->getMock('Magento\Customer\Service\V1\Data\Customer', [], [], '', false);
        $customerDataMock->expects($this->any())->method('__toArray')->will($this->returnValue([]));
        $this->customerBuilderMock
            ->expects($this->any())
            ->method('create')
            ->will($this->returnValue($customerDataMock));
        $quoteMock
            ->expects($this->atLeastOnce())
            ->method('getCustomerData')
            ->will($this->returnValue($customerDataMock));
        $this->checkoutSessionMock->expects($this->any())->method('getQuote')->will($this->returnValue($quoteMock));
        $this->checkoutSessionMock
            ->expects($this->any())
            ->method('getStepData')
            ->will($this->returnValue((int)$data['use_for_shipping'] === 1 ? true : $getStepDataResult));
        $this->checkoutSessionMock->expects($this->any())->method('setStepData')->will($this->returnSelf());
        $customerAddressMock = $this->getMock('Magento\Customer\Service\V1\Data\Address', [], [], '', false);
        $customerAddressMock
            ->expects($this->any())
            ->method('getCustomerId')
            ->will($this->returnValue($addressCustomerId));
        $this->customerAddressServiceMock
            ->expects($this->any())
            ->method('getAddress')
            ->will($isAddress ? $this->returnValue($customerAddressMock) : $this->throwException(new \Exception()));

        $this->customerBuilderMock
            ->expects($checkoutMethod === Onepage::METHOD_REGISTER ? $this->never() : $this->once())
            ->method('populate');
        $this->customerBuilderMock
            ->expects($checkoutMethod === Onepage::METHOD_REGISTER ? $this->never() : $this->once())
            ->method('setGroupId');

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
                [] // $expected
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
