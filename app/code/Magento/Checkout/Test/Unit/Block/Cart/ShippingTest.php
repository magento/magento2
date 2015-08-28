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

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $collectQuote;

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

        $this->collectQuote = $this->getMockBuilder('Magento\Checkout\Model\Cart\CollectQuote')
            ->disableOriginalConstructor()
            ->getMock();

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
            $this->quoteRepository,
            $this->collectQuote
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

        $quote = $this->getMockBuilder('Magento\Quote\Model\Quote')
            ->disableOriginalConstructor()
            ->getMock();
        $this->checkoutSession->expects($this->any())
            ->method('getQuote')
            ->willReturn($quote);
        $this->collectQuote->expects($this->once())
            ->method('collect')
            ->with($quote);

        $this->assertEquals('', $this->model->toHtml());
    }

    /**
     * @param int $count
     * @param bool $expectedResult
     * @dataProvider dataProviderIsMultipleCountriesAllowed
     */
    public function testIsMultipleCountriesAllowed(
        $count,
        $expectedResult
    ) {
        $collection = $this->getMockBuilder('Magento\Directory\Model\Resource\Country\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $collection->expects($this->once())
            ->method('count')
            ->willReturn($count);

        $this->directoryData->expects($this->once())
            ->method('getCountryCollection')
            ->willReturn($collection);

        $this->assertEquals($expectedResult, $this->model->isMultipleCountriesAllowed());
    }

    /**
     * @return array
     */
    public function dataProviderIsMultipleCountriesAllowed()
    {
        return [
            [0, false],
            [1, false],
            [2, true],
        ];
    }
}
