<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\Model\Session;

/**
 * Class QuoteTest
 * 
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class QuoteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Sales\Model\OrderFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderFactoryMock;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cookieMetadataFactoryMock;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cookieManagerMock;

    /**
     * @var \Magento\Framework\Session\StorageInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storageMock;

    /**
     * @var \Magento\Framework\Session\ValidatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $validatorMock;

    /**
     * @var \Magento\Framework\Session\SaveHandlerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $saveHandlerMock;

    /**
     * @var \Magento\Framework\Session\Config\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionConfigMock;

    /**
     * @var \Magento\Framework\Session\SidResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sidResolverMock;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerRepositoryMock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Quote\Model\QuoteFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteRepositoryMock;

    /**
     * @var \Magento\Backend\Model\Session\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quote;

    /**
     * @var \Magento\Customer\Api\GroupManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $groupManagementMock;

    /**
     * @var \Magento\Quote\Model\Quote\Address|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $billingAddressMock;

    /**
     * @var \Magento\Quote\Model\Quote\Address|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shippingAddressMock;

    /**
     * Set up
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->customerRepositoryMock = $this->getMockForAbstractClass(
            'Magento\Customer\Api\CustomerRepositoryInterface',
            [],
            '',
            false,
            true,
            true,
            ['getCustomer']
        );
        $this->groupManagementMock = $this->getMockForAbstractClass(
            'Magento\Customer\Api\GroupManagementInterface',
            [],
            '',
            false,
            true,
            true,
            ['getDefaultGroup']
        );

        $this->scopeConfigMock = $this->getMockForAbstractClass(
            'Magento\Framework\App\Config\ScopeConfigInterface',
            [],
            '',
            false,
            true,
            true,
            ['getValue']
        );
        $this->quoteRepositoryMock = $this->getMock(
            'Magento\Quote\Model\QuoteRepository',
            ['create', 'save', 'get'],
            [],
            '',
            false
        );

        $this->requestMock = $this->getMock(
            'Magento\Framework\App\Request\Http',
            [],
            [],
            '',
            false
        );
        $this->sidResolverMock = $this->getMockForAbstractClass(
            'Magento\Framework\Session\SidResolverInterface',
            [],
            '',
            false
        );
        $this->sessionConfigMock = $this->getMockForAbstractClass(
            'Magento\Framework\Session\Config\ConfigInterface',
            [],
            '',
            false
        );
        $this->saveHandlerMock = $this->getMockForAbstractClass(
            'Magento\Framework\Session\SaveHandlerInterface',
            [],
            '',
            false
        );
        $this->validatorMock = $this->getMockForAbstractClass(
            'Magento\Framework\Session\ValidatorInterface',
            [],
            '',
            false
        );
        $this->storageMock = $this->getMockForAbstractClass(
            'Magento\Framework\Session\StorageInterface',
            [],
            '',
            false
        );
        $this->cookieManagerMock = $this->getMock('Magento\Framework\Stdlib\CookieManagerInterface');
        $this->cookieMetadataFactoryMock = $this->getMock(
            'Magento\Framework\Stdlib\Cookie\CookieMetadataFactory',
            [],
            [],
            '',
            false
        );
        $this->orderFactoryMock = $this->getMock(
            'Magento\Sales\Model\OrderFactory',
            [],
            [],
            '',
            false
        );
        $this->storeManagerMock = $this->getMockForAbstractClass(
            'Magento\Store\Model\StoreManagerInterface',
            [],
            '',
            false
        );

        $this->quote = $this->getMock(
            'Magento\Backend\Model\Session\Quote',
            ['getStoreId', 'getQuoteId', 'setQuoteId', 'hasCustomerId', 'getCustomerId'],
            [
                'request' => $this->requestMock,
                'sidResolver' => $this->sidResolverMock,
                'sessionConfig' => $this->sessionConfigMock,
                'saveHandler' => $this->saveHandlerMock,
                'validator' => $this->validatorMock,
                'storage' => $this->storageMock,
                'cookieManager' => $this->cookieManagerMock,
                'cookieMetadataFactory' => $this->cookieMetadataFactoryMock,
                'customerRepository' => $this->customerRepositoryMock,
                'quoteRepository' => $this->quoteRepositoryMock,
                'orderFactory' => $this->orderFactoryMock,
                'storeManager' => $this->storeManagerMock,
                'groupManagement' => $this->groupManagementMock
            ],
            '',
            true
        );
    }

    /**
     * Run test getQuote method
     *
     * @param \Magento\Quote\Model\Quote\Address[] $allAddresses
     * @param \Magento\Quote\Model\Quote\Address|null $expectedBillingAddress
     * @param \Magento\Quote\Model\Quote\Address|null $expectedShippingAddress
     * @return void
     * @dataProvider allAddressesDataProvider
     */
    public function testGetQuoteWithoutQuoteId($allAddresses, $expectedBillingAddress, $expectedShippingAddress)
    {
        $storeId = 10;
        $quoteId = 22;
        $customerGroupId = 77;
        $customerId = 66;

        $quoteMock = $this->getMock(
            'Magento\Quote\Model\Quote',
            [
                'setStoreId',
                'setCustomerGroupId',
                'setIsActive',
                'getId',
                'assignCustomerWithAddressChange',
                'setIgnoreOldQty',
                'setIsSuperMode',
                'getAllAddresses',
                '__wakeup'
            ],
            [],
            '',
            false
        );

        $defaultGroup = $this->getMockBuilder('Magento\Customer\Api\Data\GroupInterface')
            ->getMock();
        $defaultGroup->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($customerGroupId));
        $this->groupManagementMock->expects($this->any())
            ->method('getDefaultGroup')
            ->will($this->returnValue($defaultGroup));

        $this->quoteRepositoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($quoteMock));
        $this->quote->expects($this->any())
            ->method('getStoreId')
            ->will($this->returnValue($storeId));
        $quoteMock->expects($this->once())
            ->method('setStoreId')
            ->with($storeId);
        $this->quote->expects($this->any())
            ->method('getQuoteId')
            ->will($this->returnValue(null));
        $quoteMock->expects($this->once())
            ->method('setCustomerGroupId')
            ->with($customerGroupId)
            ->will($this->returnSelf());
        $quoteMock->expects($this->once())
            ->method('setIsActive')
            ->with(false)
            ->will($this->returnSelf());
        $this->quoteRepositoryMock->expects($this->once())
            ->method('save')
            ->with($quoteMock);
        $quoteMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($quoteId));
        $this->quote->expects($this->any())
            ->method('setQuoteId')
            ->with($quoteId);
        $this->quote->expects($this->any())
            ->method('getCustomerId')
            ->will($this->returnValue($customerId));
        $dataCustomerMock = $this->getMockBuilder('Magento\Customer\Api\Data\CustomerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willReturn($dataCustomerMock);
        $quoteMock->expects($this->once())
            ->method('assignCustomerWithAddressChange')
            ->with($dataCustomerMock, $expectedBillingAddress, $expectedShippingAddress);
        $quoteMock->expects($this->once())
            ->method('setIgnoreOldQty')
            ->with(true);
        $quoteMock->expects($this->once())
            ->method('setIsSuperMode')
            ->with(true);
        $quoteMock->expects($this->any())
            ->method('getAllAddresses')
            ->will($this->returnValue($allAddresses));

        $this->assertEquals($quoteMock, $this->quote->getQuote());
    }

    /**
     * @return array
     */
    public function allAddressesDataProvider()
    {
        // since setup() is called after the dataProvider, ensure we have valid addresses
        $this->buildAddressMocks();

        return [
            'empty addresses' => [
                [],
                null,
                null
            ],
            'use typical addresses' => [
                [$this->billingAddressMock, $this->shippingAddressMock],
                $this->billingAddressMock,
                $this->shippingAddressMock
            ],
        ];
    }

    protected function buildAddressMocks()
    {
        if ($this->billingAddressMock == null) {
            $this->billingAddressMock = $this->getMock(
                'Magento\Quote\Model\Quote\Address',
                ['getAddressType'],
                [],
                '',
                false
            );
            $this->billingAddressMock->expects($this->any())
                ->method('getAddressType')
                ->will($this->returnValue(\Magento\Quote\Model\Quote\Address::ADDRESS_TYPE_BILLING));
        }

        if ($this->shippingAddressMock == null) {
            $this->shippingAddressMock = $this->getMock(
                'Magento\Quote\Model\Quote\Address',
                ['getAddressType'],
                [],
                '',
                false
            );
            $this->shippingAddressMock->expects($this->any())
                ->method('getAddressType')
                ->will($this->returnValue(\Magento\Quote\Model\Quote\Address::ADDRESS_TYPE_SHIPPING));
        }
    }

    /**
     * Run test getQuote method
     *
     * @return void
     */
    public function testGetQuoteWithQuoteId()
    {
        $storeId = 10;
        $quoteId = 22;
        $customerId = 66;

        $quoteMock = $this->getMock(
            'Magento\Quote\Model\Quote',
            [
                'setStoreId',
                'setCustomerGroupId',
                'setIsActive',
                'getId',
                'assignCustomerWithAddressChange',
                'setIgnoreOldQty',
                'setIsSuperMode',
                'getAllAddresses',
                '__wakeup'
            ],
            [],
            '',
            false
        );

        $this->quoteRepositoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($quoteMock));
        $this->quote->expects($this->any())
            ->method('getStoreId')
            ->will($this->returnValue($storeId));
        $quoteMock->expects($this->once())
            ->method('setStoreId')
            ->with($storeId);
        $this->quote->expects($this->any())
            ->method('getQuoteId')
            ->will($this->returnValue($quoteId));
        $this->quoteRepositoryMock->expects($this->once())
            ->method('get')
            ->with($quoteId)
            ->willReturn($quoteMock);
        $this->quote->expects($this->any())
            ->method('setQuoteId')
            ->with($quoteId);
        $this->quote->expects($this->any())
            ->method('getCustomerId')
            ->will($this->returnValue($customerId));
        $dataCustomerMock = $this->getMockBuilder('Magento\Customer\Api\Data\CustomerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willReturn($dataCustomerMock);
        $quoteMock->expects($this->once())
            ->method('assignCustomerWithAddressChange')
            ->with($dataCustomerMock);
        $quoteMock->expects($this->once())
            ->method('setIgnoreOldQty')
            ->with(true);
        $quoteMock->expects($this->once())
            ->method('setIsSuperMode')
            ->with(true);
        $quoteMock->expects($this->any())
            ->method('getAllAddresses')
            ->will($this->returnValue([]));

        $this->assertEquals($quoteMock, $this->quote->getQuote());
    }
}
