<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Test\Unit\Model;

use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Quote\Model\Cart\ShippingMethodConverter;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Rate;
use Magento\Quote\Model\Quote\TotalsCollector;
use Magento\Quote\Model\QuoteRepository;
use Magento\Quote\Model\ResourceModel\Quote\Address as QuoteAddressResource;
use Magento\Quote\Model\ShippingMethodManagement;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject as MockObject;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Session;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ShippingMethodManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ShippingMethodManagement
     */
    protected $model;

    /**
     * @var ShippingMethodConverter|MockObject
     */
    protected $converter;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var QuoteRepository|MockObject
     */
    private $quoteRepository;

    /**
     * @var Quote|MockObject
     */
    private $quote;

    /**
     * @var Address|MockObject
     */
    private $shippingAddress;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor|MockObject
     */
    private $dataProcessor;

    /**
     * @var \Magento\Customer\Api\Data\AddressInterfaceFactory|MockObject
     */
    private $addressFactory;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface|MockObject
     */
    private $addressRepository;

    /**
     * @var TotalsCollector|MockObject
     */
    private $totalsCollector;

    /**
     * @var Store|MockObject
     */
    private $storeMock;

    /**
     * @var QuoteAddressResource|MockObject
     */
    private $quoteAddressResource;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->quoteRepository = $this->createMock(\Magento\Quote\Api\CartRepositoryInterface::class);
        $this->addressRepository = $this->createMock(\Magento\Customer\Api\AddressRepositoryInterface::class);
        $this->customerSession = $this->createMock(Session::class);

        $className = \Magento\Quote\Api\Data\ShippingMethodInterfaceFactory::class;
        $methodDataFactoryMock = $this->createPartialMock($className, ['create']);

        $className = \Magento\Customer\Api\Data\AddressInterfaceFactory::class;
        $this->addressFactory = $this->createPartialMock($className, ['create']);

        $className = \Magento\Framework\Reflection\DataObjectProcessor::class;
        $this->dataProcessor = $this->createMock($className);

        $this->quoteAddressResource = $this->createMock(QuoteAddressResource::class);
        $this->storeMock = $this->createMock(Store::class);
        $this->quote = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getShippingAddress',
                'isVirtual',
                'getItemsCount',
                'getQuoteCurrencyCode',
                'getBillingAddress',
                'collectTotals',
                'save',
                '__wakeup',
                'getCustomer'
            ])
            ->getMock();

        $this->shippingAddress = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getCountryId',
                'getShippingMethod',
                'getShippingDescription',
                'getShippingAmount',
                'getBaseShippingAmount',
                'getGroupedAllShippingRates',
                'collectShippingRates',
                'requestShippingRates',
                'setShippingMethod',
                'getShippingRateByCode',
                'addData',
                'setCollectShippingRates',
                '__wakeup',
            ])
            ->getMock();

        $this->converter = $this->getMockBuilder(ShippingMethodConverter::class)
            ->disableOriginalConstructor()
            ->setMethods(['modelToDataObject'])
            ->getMock();

        $this->totalsCollector = $this->getMockBuilder(TotalsCollector::class)
            ->disableOriginalConstructor()
            ->setMethods(['collectAddressTotals'])
            ->getMock();

        $this->model = $this->objectManager->getObject(
            ShippingMethodManagement::class,
            [
                'quoteRepository' => $this->quoteRepository,
                'methodDataFactory' => $methodDataFactoryMock,
                'converter' => $this->converter,
                'totalsCollector' => $this->totalsCollector,
                'addressRepository' => $this->addressRepository,
                'quoteAddressResource' => $this->quoteAddressResource,
                'customerSession' => $this->customerSession,
            ]
        );

        $this->objectManager->setBackwardCompatibleProperty($this->model, 'addressFactory', $this->addressFactory);
        $this->objectManager->setBackwardCompatibleProperty($this->model, 'dataProcessor', $this->dataProcessor);
    }

    /**
     */
    public function testGetMethodWhenShippingAddressIsNotSet()
    {
        $this->expectException(\Magento\Framework\Exception\StateException::class);
        $this->expectExceptionMessage('The shipping address is missing. Set the address and try again.');

        $cartId = 666;
        $this->quoteRepository->expects($this->once())
            ->method('getActive')->with($cartId)->willReturn($this->quote);
        $this->quote->expects($this->once())
            ->method('getShippingAddress')->willReturn($this->shippingAddress);
        $this->shippingAddress->expects($this->once())->method('getCountryId')->willReturn(null);

        $this->assertNull($this->model->get($cartId));
    }

    /**
     * Test to returns selected shipping method for a specified quote
     */
    public function testGetMethod()
    {
        $cartId = 666;
        $countryId = 1;
        $currencyCode = 'US_dollar';
        $this->quoteRepository->expects($this->once())
            ->method('getActive')->with($cartId)->willReturn($this->quote);
        $this->quote->expects($this->once())
            ->method('getShippingAddress')->willReturn($this->shippingAddress);
        $this->quote->expects($this->once())
            ->method('getQuoteCurrencyCode')->willReturn($currencyCode);
        $this->shippingAddress->expects($this->any())
            ->method('getCountryId')->willReturn($countryId);
        $this->shippingAddress->expects($this->any())
            ->method('getShippingMethod')->willReturn('one_two');

        $this->shippingAddress->expects($this->once())->method('collectShippingRates')->willReturnSelf();
        $shippingRateMock = $this->createMock(\Magento\Quote\Model\Quote\Address\Rate::class);

        $this->shippingAddress->expects($this->once())
            ->method('getShippingRateByCode')
            ->with('one_two')
            ->willReturn($shippingRateMock);

        $shippingMethodMock = $this->createMock(\Magento\Quote\Api\Data\ShippingMethodInterface::class);
        $this->converter->expects($this->once())
            ->method('modelToDataObject')
            ->with($shippingRateMock, $currencyCode)
            ->willReturn($shippingMethodMock);
        $this->model->get($cartId);
    }

    /**
     * Test to returns selected shipping method for a specified quote if method isn't set
     */
    public function testGetMethodIfMethodIsNotSet()
    {
        $cartId = 666;
        $countryId = 1;

        $this->quoteRepository->expects($this->once())
            ->method('getActive')->with($cartId)->willReturn($this->quote);
        $this->quote->expects($this->once())
            ->method('getShippingAddress')->willReturn($this->shippingAddress);
        $this->shippingAddress->expects($this->any())
            ->method('getCountryId')->willReturn($countryId);
        $this->shippingAddress->expects($this->any())
            ->method('getShippingMethod')->willReturn(null);

        $this->assertNull($this->model->get($cartId));
    }

    /**
     * Test to get lists applicable shipping methods for a specified quote
     */
    public function testGetListForVirtualCart()
    {
        $cartId = 834;
        $this->quoteRepository->expects($this->once())
            ->method('getActive')->with($cartId)->willReturn($this->quote);
        $this->quote->expects($this->once())
            ->method('isVirtual')->willReturn(true);

        $this->assertEquals([], $this->model->getList($cartId));
    }

    /**
     * Test to get lists applicable shipping methods for a specified quote
     */
    public function testGetListForEmptyCart()
    {
        $cartId = 834;
        $this->quoteRepository->expects($this->once())
            ->method('getActive')->with($cartId)->willReturn($this->quote);
        $this->quote->expects($this->once())
            ->method('isVirtual')->willReturn(false);
        $this->quote->expects($this->once())
            ->method('getItemsCount')->willReturn(0);

        $this->assertEquals([], $this->model->getList($cartId));
    }

    /**
     */
    public function testGetListWhenShippingAddressIsNotSet()
    {
        $this->expectException(\Magento\Framework\Exception\StateException::class);
        $this->expectExceptionMessage('The shipping address is missing. Set the address and try again.');

        $cartId = 834;
        $this->quoteRepository->expects($this->once())
            ->method('getActive')->with($cartId)->willReturn($this->quote);
        $this->quote->expects($this->once())
            ->method('isVirtual')->willReturn(false);
        $this->quote->expects($this->once())
            ->method('getItemsCount')->willReturn(3);
        $this->quote->expects($this->once())
            ->method('getShippingAddress')->willReturn($this->shippingAddress);
        $this->shippingAddress->expects($this->once())->method('getCountryId')->willReturn(null);

        $this->model->getList($cartId);
    }

    /**
     * Test to get lists applicable shipping methods for a specified quote
     */
    public function testGetList()
    {
        $cartId = 834;
        $this->quoteRepository->expects($this->once())
            ->method('getActive')->with($cartId)->willReturn($this->quote);
        $this->quote->expects($this->once())
            ->method('isVirtual')->willReturn(false);
        $this->quote->expects($this->once())
            ->method('getItemsCount')->willReturn(3);
        $this->quote->expects($this->once())
            ->method('getShippingAddress')->willReturn($this->shippingAddress);
        $this->shippingAddress->expects($this->once())->method('getCountryId')->willReturn(345);
        $this->shippingAddress->expects($this->once())->method('collectShippingRates');
        $shippingRateMock = $this->createMock(\Magento\Quote\Model\Quote\Address\Rate::class);
        $this->shippingAddress->expects($this->once())
            ->method('getGroupedAllShippingRates')
            ->willReturn([[$shippingRateMock]]);

        $currencyCode = 'EUR';
        $this->quote->expects($this->once())
            ->method('getQuoteCurrencyCode')
            ->willReturn($currencyCode);

        $this->converter->expects($this->once())
            ->method('modelToDataObject')
            ->with($shippingRateMock, $currencyCode)
            ->willReturn('RateValue');
        $this->assertEquals(['RateValue'], $this->model->getList($cartId));
    }

    /**
     */
    public function testSetMethodWithInputException()
    {
        $this->expectException(\Magento\Framework\Exception\InputException::class);
        $exceptionMessage =  'The shipping method can\'t be set for an empty cart. Add an item to cart and try again.';
        $this->expectExceptionMessage($exceptionMessage);

        $cartId = 12;
        $carrierCode = 34;
        $methodCode = 56;
        $this->quoteRepository->expects($this->exactly(2))
            ->method('getActive')
            ->with($cartId)
            ->willReturn($this->quote);
        $this->quote->expects($this->once())->method('getItemsCount')->willReturn(0);
        $this->quote->expects($this->never())->method('isVirtual');

        $this->model->set($cartId, $carrierCode, $methodCode);
    }

    /**
     */
    public function testSetMethodWithVirtualProduct()
    {
        $this->expectException(\Magento\Framework\Exception\NoSuchEntityException::class);
        $this->expectExceptionMessage('The Cart includes virtual product(s) only, so a shipping address is not used.');

        $cartId = 12;
        $carrierCode = 34;
        $methodCode = 56;

        $this->quoteRepository->expects($this->exactly(2))
            ->method('getActive')
            ->with($cartId)
            ->willReturn($this->quote);
        $this->quote->expects($this->once())->method('getItemsCount')->willReturn(1);
        $this->quote->expects($this->once())->method('isVirtual')->willReturn(true);

        $this->model->set($cartId, $carrierCode, $methodCode);
    }

    /**
     */
    public function testSetMethodWithoutShippingAddress()
    {
        $this->expectException(\Magento\Framework\Exception\StateException::class);
        $this->expectExceptionMessage('The shipping address is missing. Set the address and try again.');

        $cartId = 12;
        $carrierCode = 34;
        $methodCode = 56;
        $this->quoteRepository->expects($this->exactly(2))
            ->method('getActive')
            ->with($cartId)
            ->willReturn($this->quote);
        $this->quote->expects($this->once())->method('getItemsCount')->willReturn(1);
        $this->quote->expects($this->once())->method('isVirtual')->willReturn(false);
        $this->quote->expects($this->once())
            ->method('getShippingAddress')->willReturn($this->shippingAddress);
        $this->shippingAddress->expects($this->once())->method('getCountryId')->willReturn(null);
        $this->quoteAddressResource->expects($this->once())->method('delete')->with($this->shippingAddress);

        $this->model->set($cartId, $carrierCode, $methodCode);
    }

    /**
     */
    public function testSetMethodWithCouldNotSaveException()
    {
        $this->expectException(\Magento\Framework\Exception\CouldNotSaveException::class);
        $this->expectExceptionMessage('The shipping method can\'t be set. Custom Error');

        $cartId = 12;
        $carrierCode = 34;
        $methodCode = 56;
        $countryId = 1;

        $this->quoteRepository->expects($this->exactly(2))
            ->method('getActive')
            ->with($cartId)
            ->willReturn($this->quote);
        $this->quote->expects($this->once())->method('getItemsCount')->willReturn(1);
        $this->quote->expects($this->once())->method('isVirtual')->willReturn(false);
        $this->quote->expects($this->once())
            ->method('getShippingAddress')
            ->willReturn($this->shippingAddress);
        $this->shippingAddress->expects($this->once())
            ->method('getCountryId')
            ->willReturn($countryId);
        $this->shippingAddress->expects($this->once())
            ->method('setShippingMethod')
            ->with($carrierCode . '_' . $methodCode);
        $exception = new \Exception('Custom Error');
        $this->quote->expects($this->once())->method('collectTotals')->willReturnSelf();
        $this->quoteRepository->expects($this->once())
            ->method('save')
            ->with($this->quote)
            ->willThrowException($exception);

        $this->model->set($cartId, $carrierCode, $methodCode);
    }

    /**
     */
    public function testSetMethodWithoutAddress()
    {
        $this->expectException(\Magento\Framework\Exception\StateException::class);
        $this->expectExceptionMessage('The shipping address is missing. Set the address and try again.');

        $cartId = 12;
        $carrierCode = 34;
        $methodCode = 56;
        $this->quoteRepository->expects($this->exactly(2))
            ->method('getActive')
            ->with($cartId)
            ->willReturn($this->quote);
        $this->quote->expects($this->once())->method('getItemsCount')->willReturn(1);
        $this->quote->expects($this->once())->method('isVirtual')->willReturn(false);
        $this->quote->expects($this->once())
            ->method('getShippingAddress')
            ->willReturn($this->shippingAddress);
        $this->shippingAddress->expects($this->once())->method('getCountryId');
        $this->quoteAddressResource->expects($this->once())->method('delete')->with($this->shippingAddress);

        $this->model->set($cartId, $carrierCode, $methodCode);
    }

    /**
     * Test to sets the carrier and shipping methods codes for a specified cart
     */
    public function testSetMethod()
    {
        $cartId = 12;
        $carrierCode = 34;
        $methodCode = 56;
        $countryId = 1;
        $this->quoteRepository->expects($this->exactly(2))
            ->method('getActive')
            ->with($cartId)
            ->willReturn($this->quote);
        $this->quote->expects($this->once())->method('getItemsCount')->willReturn(1);
        $this->quote->expects($this->once())->method('isVirtual')->willReturn(false);
        $this->quote->expects($this->once())
            ->method('getShippingAddress')->willReturn($this->shippingAddress);
        $this->shippingAddress->expects($this->once())
            ->method('getCountryId')->willReturn($countryId);
        $this->shippingAddress->expects($this->once())
            ->method('setShippingMethod')->with($carrierCode . '_' . $methodCode);
        $this->quote->expects($this->once())->method('collectTotals')->willReturnSelf();
        $this->quoteRepository->expects($this->once())->method('save')->with($this->quote);

        $this->assertTrue($this->model->set($cartId, $carrierCode, $methodCode));
    }

    /**
     * @covers \Magento\Quote\Model\ShippingMethodManagement::estimateByExtendedAddress
     */
    public function testEstimateByExtendedAddress()
    {
        $cartId = 1;

        $addressExtAttr = [
            'discounts' => 100
        ];
        $addressData = [
            'region' => 'California',
            'region_id' => 23,
            'country_id' => 1,
            'postcode' => 90200,
        ];
        $currencyCode = 'UAH';

        /**
         * @var \Magento\Quote\Api\Data\AddressInterface|MockObject $address
         */
        $address = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->addressFactory->expects($this->any())
            ->method('create')
            ->willReturn($address);

        $this->quoteRepository->expects(static::once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($this->quote);

        $this->quote->expects(static::once())
            ->method('isVirtual')
            ->willReturn(false);
        $this->quote->expects(static::once())
            ->method('getItemsCount')
            ->willReturn(1);

        $this->quote->expects(static::once())
            ->method('getShippingAddress')
            ->willReturn($this->shippingAddress);

        $this->dataProcessor->expects(static::any())
            ->method('buildOutputDataArray')
            ->willReturn($addressData + [ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY => $addressExtAttr]);

        $this->shippingAddress->expects($this->once())->method('addData')->with($addressData);

        $this->shippingAddress->expects(static::once())
            ->method('setCollectShippingRates')
            ->with(true)
            ->willReturnSelf();

        $this->totalsCollector->expects(static::once())
            ->method('collectAddressTotals')
            ->with($this->quote, $this->shippingAddress)
            ->willReturnSelf();

        $rate = $this->getMockBuilder(Rate::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $methodObject = $this->getMockForAbstractClass(ShippingMethodInterface::class);
        $expectedRates = [$methodObject];

        $this->shippingAddress->expects(static::once())
            ->method('getGroupedAllShippingRates')
            ->willReturn([[$rate]]);

        $this->quote->expects(static::once())
            ->method('getQuoteCurrencyCode')
            ->willReturn($currencyCode);

        $this->converter->expects(static::once())
            ->method('modelToDataObject')
            ->with($rate, $currencyCode)
            ->willReturn($methodObject);

        $carriersRates = $this->model->estimateByExtendedAddress($cartId, $address);
        static::assertEquals($expectedRates, $carriersRates);
    }

    /**
     * @dataProvider getAddressDataProvider
     *
     * @covers \Magento\Quote\Model\ShippingMethodManagement::estimateByAddressId
     * @param int $cartId
     * @param int $addressId
     * @param int $randomAddressId
     * @param bool $throwsException
     */
    public function testEstimateByAddressId($cartId, $addressId, $randomAddressId, $throwsException)
    {
        $addressData = [
            'region' => 'California',
            'region_id' => 23,
            'country_id' => 1,
            'postcode' => 90200,
        ];
        $currencyCode = 'UAH';
        $customerId = 1;

        $rate = $this->getMockBuilder(Rate::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $methodObject = $this->getMockForAbstractClass(ShippingMethodInterface::class);

        $this->quoteRepository->expects(self::once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($this->quote);

        $this->quote->expects(self::once())
            ->method('isVirtual')
            ->willReturn(false);

        $this->quote->expects(self::once())
            ->method('getItemsCount')
            ->willReturn(1);

        $this->setCustomerSession($addressId, $customerId);
        if ($throwsException) {
            $this->expectException('Magento\Framework\Exception\InputException');
            $this->expectExceptionMessage('The shipping address is missing. Set the address and try again.');
            $this->model->estimateByAddressId($cartId, $randomAddressId);
        } else {
            $this->quote->expects(self::once())
                ->method('getShippingAddress')
                ->willReturn($this->shippingAddress);

            $this->dataProcessor->expects(self::any())
                ->method('buildOutputDataArray')
                ->willReturn($addressData);

            $this->shippingAddress->expects(self::once())
                ->method('setCollectShippingRates')
                ->with(true)
                ->willReturnSelf();

            $this->totalsCollector->expects(self::once())
                ->method('collectAddressTotals')
                ->with($this->quote, $this->shippingAddress)
                ->willReturnSelf();

            $expectedRates = [$methodObject];
            $this->shippingAddress->expects(self::once())
                ->method('getGroupedAllShippingRates')
                ->willReturn([[$rate]]);

            $this->quote->expects(self::once())
                ->method('getQuoteCurrencyCode')
                ->willReturn($currencyCode);

            $this->converter->expects(self::once())
                ->method('modelToDataObject')
                ->with($rate, $currencyCode)
                ->willReturn($methodObject);

            $carriersRates = $this->model->estimateByAddressId($cartId, $addressId);
            self::assertEquals($expectedRates, $carriersRates);
        }
    }

    /**
     * @return array
     */
    public function getAddressDataProvider()
    {
        return [
            [1, 1, 5, true],
            [1, 1, 1, false],
        ];
    }

    private function setCustomerSession($addressId, $customerId)
    {
        /**
         * @var \Magento\Customer\Model\Data\Address|MockObject $address
         */
        $address = $this->getMockBuilder(\Magento\Customer\Model\Data\Address::class)
            ->setMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $address->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($addressId);

        $this->addressRepository->expects($this->any())
            ->method('getById')
            ->willReturn($address);

        $this->addressFactory->expects($this->any())
            ->method('create')
            ->willReturn($address);

        $customerAddresses = [$address];
        $customerMock = $this->getMockForAbstractClass(CustomerInterface::class);
        $customerMock->method('getAddresses')->willReturn($customerAddresses);

        $this->quote->method('getCustomer')->willReturn($customerMock);
        $this->customerSession->method('getCustomerId')->willReturn($customerId);
        $this->customerSession->expects(self::any())->method('isLoggedIn')->willReturn(true);
        $this->customerSession->expects(self::any())->method('getCustomerData')->willReturn($customerMock);
    }
}
