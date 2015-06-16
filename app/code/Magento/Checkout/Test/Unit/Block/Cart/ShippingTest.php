<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Block\Cart;

use Magento\Checkout\Block\Cart\Shipping;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Directory\Block\Data as DirectoryData;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Layout;
use Magento\Quote\Api\Data\EstimateAddressInterface;
use Magento\Quote\Api\Data\EstimateAddressInterfaceFactory;
use Magento\Quote\Api\ShippingMethodManagementInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteRepository;
use Magento\Shipping\Model\CarrierFactoryInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ShippingTest extends \PHPUnit_Framework_TestCase
{
    /** @var  Shipping */
    protected $model;

    /** @var  Context |\PHPUnit_Framework_MockObject_MockObject */
    protected $context;

    /** @var  CustomerSession |\PHPUnit_Framework_MockObject_MockObject */
    protected $customerSession;

    /** @var  CheckoutSession |\PHPUnit_Framework_MockObject_MockObject */
    protected $checkoutSession;

    /** @var  DirectoryData |\PHPUnit_Framework_MockObject_MockObject */
    protected $directoryData;

    /** @var  CarrierFactoryInterface |\PHPUnit_Framework_MockObject_MockObject */
    protected $carrierFactory;

    /** @var  PriceCurrencyInterface |\PHPUnit_Framework_MockObject_MockObject */
    protected $priceCurrency;

    /** @var  EstimateAddressInterfaceFactory |\PHPUnit_Framework_MockObject_MockObject */
    protected $estimatedAddressFactory;

    /** @var  ShippingMethodManagementInterface |\PHPUnit_Framework_MockObject_MockObject */
    protected $shippingMethodManager;

    /** @var  AddressRepositoryInterface |\PHPUnit_Framework_MockObject_MockObject */
    protected $addressReporitory;

    /** @var  CustomerRepositoryInterface |\PHPUnit_Framework_MockObject_MockObject */
    protected $customerRepository;

    /** @var  QuoteRepository |\PHPUnit_Framework_MockObject_MockObject */
    protected $quoteRepository;

    /** @var  Layout |\PHPUnit_Framework_MockObject_MockObject */
    protected $layout;

    /** @var  EventManager |\PHPUnit_Framework_MockObject_MockObject */
    protected $eventManager;

    /** @var  ScopeConfigInterface |\PHPUnit_Framework_MockObject_MockObject */
    protected $scopeConfig;

    /** @var  EstimateAddressInterface |\PHPUnit_Framework_MockObject_MockObject */
    protected $estimatedAddress;

    /** @var  AddressInterface |\PHPUnit_Framework_MockObject_MockObject */
    protected $address;

    /** @var  Quote |\PHPUnit_Framework_MockObject_MockObject */
    protected $quote;

    protected function setUp()
    {
        $this->prepareContext();

        $this->customerSession = $this->getMockBuilder('Magento\Customer\Model\Session')
            ->disableOriginalConstructor()
            ->getMock();

        $this->checkoutSession = $this->getMockBuilder('Magento\Checkout\Model\Session')
            ->disableOriginalConstructor()
            ->getMock();

        $this->directoryData = $this->getMockBuilder('Magento\Directory\Block\Data')
            ->disableOriginalConstructor()
            ->getMock();

        $this->carrierFactory = $this->getMockBuilder('Magento\Shipping\Model\CarrierFactoryInterface')
            ->getMockForAbstractClass();

        $this->priceCurrency = $this->getMockBuilder('Magento\Framework\Pricing\PriceCurrencyInterface')
            ->getMockForAbstractClass();

        $this->prepareEstimatedAddress();

        $this->shippingMethodManager = $this->getMockBuilder('Magento\Quote\Api\ShippingMethodManagementInterface')
            ->getMockForAbstractClass();

        $this->prepareAddressRepository();

        $this->customerRepository = $this->getMockBuilder('Magento\Customer\Api\CustomerRepositoryInterface')
            ->getMockForAbstractClass();

        $this->prepareQuoteRepository();

        $this->model = new Shipping(
            $this->context,
            $this->customerSession,
            $this->checkoutSession,
            $this->directoryData,
            $this->carrierFactory,
            $this->priceCurrency,
            $this->estimatedAddressFactory,
            $this->shippingMethodManager,
            $this->addressReporitory,
            $this->customerRepository,
            $this->quoteRepository
        );
    }

    protected function prepareContext()
    {
        $this->layout = $this->getMockBuilder('Magento\Framework\View\Layout')
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventManager = $this->getMockBuilder('Magento\Framework\Event\ManagerInterface')
            ->setMethods([
                'dispatch',
            ])
            ->getMockForAbstractClass();

        $this->scopeConfig = $this->getMockBuilder('Magento\Framework\App\Config\ScopeConfigInterface')
            ->setMethods([
                'getValue',
            ])
            ->getMockForAbstractClass();

        $this->context = $this->getMockBuilder('Magento\Framework\View\Element\Template\Context')
            ->disableOriginalConstructor()
            ->getMock();

        $this->context->expects($this->once())
            ->method('getLayout')
            ->will($this->returnValue($this->layout));

        $this->context->expects($this->once())
            ->method('getEventManager')
            ->will($this->returnValue($this->eventManager));

        $this->context->expects($this->once())
            ->method('getScopeConfig')
            ->will($this->returnValue($this->scopeConfig));
    }

    protected function prepareEstimatedAddress()
    {
        $this->estimatedAddress = $this->getMockBuilder('Magento\Quote\Api\Data\EstimateAddressInterface')
            ->setMethods([
                'setCountryId',
                'setPostcode',
                'setRegion',
                'setRegionId',
            ])
            ->getMockForAbstractClass();

        $this->estimatedAddressFactory = $this->getMockBuilder('Magento\Quote\Api\Data\EstimateAddressInterfaceFactory')
            ->disableOriginalConstructor()
            ->setMethods([
                'create',
            ])
            ->getMock();

        $this->estimatedAddressFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->estimatedAddress);
    }

    protected function prepareAddressRepository()
    {
        $this->address = $this->getMockBuilder('Magento\Customer\Api\Data\AddressInterface')
            ->setMethods([
                'getCountryId',
                'getPostcode',
                'getRegion',
                'getRegionId',
            ])
            ->getMockForAbstractClass();

        $this->addressReporitory = $this->getMockBuilder('Magento\Customer\Api\AddressRepositoryInterface')
            ->getMockForAbstractClass();
    }

    protected function prepareQuoteRepository()
    {
        $this->quoteRepository = $this->getMockBuilder('Magento\Quote\Model\QuoteRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $this->quote = $this->getMockBuilder('Magento\Quote\Model\Quote')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testGetShippingPriceHtml()
    {
        $shippingRateMock = $this->getMockBuilder('\Magento\Quote\Model\Quote\Address\Rate')
            ->disableOriginalConstructor()
            ->getMock();

        $shippingPriceHtml = "$3.25 ($3.56 Incl Tax)";

        $priceBlockMock = $this->getMockBuilder('\Magento\Checkout\Block\Shipping\Price')
            ->disableOriginalConstructor()
            ->setMethods(['setShippingRate', 'toHtml'])
            ->getMock();

        $priceBlockMock->expects($this->once())
            ->method('setShippingRate')
            ->with($shippingRateMock);

        $priceBlockMock->expects($this->once())
            ->method('toHtml')
            ->will($this->returnValue($shippingPriceHtml));

        $this->layout->expects($this->once())
            ->method('getBlock')
            ->with('checkout.shipping.price')
            ->will($this->returnValue($priceBlockMock));

        $this->assertEquals($shippingPriceHtml, $this->model->getShippingPriceHtml($shippingRateMock));
    }

    public function testBeforeToHtmlCustomerNotLoggedIn()
    {
        $this->eventManager->expects($this->once())
            ->method('dispatch')
            ->with('view_block_abstract_to_html_before', ['block' => $this->model])
            ->willReturnSelf();

        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with('advanced/modules_disable_output/Magento_Checkout', ScopeInterface::SCOPE_STORE)
            ->willReturn(false);

        $this->customerSession->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(false);

        $this->assertEquals('', $this->model->toHtml());
    }

    public function testBeforeToHtmlNoDefaultShippingAddress()
    {
        $customerId = 1;
        $defaultShipping = 0;

        $this->eventManager->expects($this->once())
            ->method('dispatch')
            ->with('view_block_abstract_to_html_before', ['block' => $this->model])
            ->willReturnSelf();

        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with('advanced/modules_disable_output/Magento_Checkout', ScopeInterface::SCOPE_STORE)
            ->willReturn(false);

        $this->customerSession->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(true);
        $this->customerSession->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $customerData = $this->getMockBuilder('Magento\Customer\Api\Data\CustomerInterface')
            ->setMethods([
                'getDefaultShipping',
            ])
            ->getMockForAbstractClass();
        $customerData->expects($this->once())
            ->method('getDefaultShipping')
            ->willReturn($defaultShipping);

        $this->customerRepository->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willReturn($customerData);

        $this->assertEquals('', $this->model->toHtml());
    }

    /**
     * @param int $customerId
     * @param int $defaultShipping
     * @param int $countryId
     * @param string $postcode
     * @param string $region
     * @param int $regionId
     * @param int $quoteId
     * @dataProvider dataProviderBeforeToHtml
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testBeforeToHtml(
        $customerId,
        $defaultShipping,
        $countryId,
        $postcode,
        $region,
        $regionId,
        $quoteId
    ) {
        $this->eventManager->expects($this->once())
            ->method('dispatch')
            ->with('view_block_abstract_to_html_before', ['block' => $this->model])
            ->willReturnSelf();

        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with('advanced/modules_disable_output/Magento_Checkout', ScopeInterface::SCOPE_STORE)
            ->willReturn(false);

        $this->customerSession->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(true);
        $this->customerSession->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $customerDataMock = $this->getMockBuilder('Magento\Customer\Api\Data\CustomerInterface')
            ->setMethods([
                'getDefaultShipping',
            ])
            ->getMockForAbstractClass();
        $customerDataMock->expects($this->once())
            ->method('getDefaultShipping')
            ->willReturn($defaultShipping);

        $this->customerRepository->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willReturn($customerDataMock);

        $this->addressReporitory->expects($this->once())
            ->method('getById')
            ->with($defaultShipping)
            ->willReturn($this->address);

        $regionMock = $this->getMockBuilder('Magento\Customer\Api\Data\RegionInterface')
            ->setMethods([
                'getRegion',
            ])
            ->getMockForAbstractClass();
        $regionMock->expects($this->once())
            ->method('getRegion')
            ->willReturn($region);

        $this->address->expects($this->once())
            ->method('getCountryId')
            ->willReturn($countryId);
        $this->address->expects($this->once())
            ->method('getPostcode')
            ->willReturn($postcode);
        $this->address->expects($this->once())
            ->method('getRegion')
            ->willReturn($regionMock);
        $this->address->expects($this->once())
            ->method('getRegionId')
            ->willReturn($regionId);

        $this->estimatedAddress->expects($this->once())
            ->method('setCountryId')
            ->with($countryId)
            ->willReturnSelf();
        $this->estimatedAddress->expects($this->once())
            ->method('setPostcode')
            ->with($postcode)
            ->willReturnSelf();
        $this->estimatedAddress->expects($this->once())
            ->method('setRegion')
            ->with($region)
            ->willReturnSelf();
        $this->estimatedAddress->expects($this->once())
            ->method('setRegionId')
            ->with($regionId)
            ->willReturnSelf();

        $this->checkoutSession->expects($this->once())
            ->method('getQuote')
            ->willReturn($this->quote);

        $this->quote->expects($this->once())
            ->method('getId')
            ->willReturn($quoteId);

        $this->shippingMethodManager->expects($this->once())
            ->method('estimateByAddress')
            ->with($quoteId, $this->estimatedAddress)
            ->willReturnSelf();

        $this->quoteRepository->expects($this->once())
            ->method('save')
            ->with($this->quote)
            ->willReturnSelf();

        $this->assertEquals('', $this->model->toHtml());
    }

    /**
     * @return array
     */
    public function dataProviderBeforeToHtml()
    {
        return [
            [1, 1, 1, '12345', '', 1, 1],
            [1, 1, 1, '12345', '', 0, 1],
            [1, 1, 1, '', '', 0, 1],
            [1, 1, 1, '12345', 'California', 0, 1],
            [1, 1, 1, '12345', 'California', 1, 1],
        ];
    }
}
