<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Quote\Model\Cart\ShippingMethodConverter;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Rate;
use Magento\Quote\Model\Quote\TotalsCollector;
use Magento\Quote\Model\QuoteRepository;
use Magento\Quote\Model\ResourceModel\Quote\Address as QuoteAddressResource;
use Magento\Quote\Model\ShippingMethodManagement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ShippingMethodManagementTest extends TestCase
{
    /**
     * @var ShippingMethodManagement
     */
    private $model;

    /**
     * @var QuoteRepository|MockObject
     */
    private $quoteRepositoryMock;

    /**
     * @var ShippingMethodConverter|MockObject
     */
    private $converterMock;

    /**
     * @var AddressRepositoryInterface|MockObject
     */
    private $addressRepositoryMock;

    /**
     * @var TotalsCollector|MockObject
     */
    private $totalsCollectorMock;

    /**
     * @var QuoteAddressResource|MockObject
     */
    private $quoteAddressResourceMock;

    /**
     * @var DataObjectProcessor|MockObject
     */
    private $dataProcessorMock;

    /**
     * @var Quote|MockObject
     */
    private $quoteMock;

    /**
     * @var Address|MockObject
     */
    private $shippingAddressMock;

    protected function setUp()
    {
        $this->quoteRepositoryMock = $this->createMock(CartRepositoryInterface::class);
        $this->converterMock = $this->getMockBuilder(ShippingMethodConverter::class)
            ->disableOriginalConstructor()
            ->setMethods(['modelToDataObject'])
            ->getMock();
        $this->addressRepositoryMock = $this->createMock(AddressRepositoryInterface::class);
        $this->totalsCollectorMock = $this->getMockBuilder(TotalsCollector::class)
            ->disableOriginalConstructor()
            ->setMethods(['collectAddressTotals'])
            ->getMock();
        $this->quoteAddressResourceMock = $this->createMock(QuoteAddressResource::class);
        $this->dataProcessorMock = $this->createMock(DataObjectProcessor::class);

        $this->quoteMock = $this->getMockBuilder(Quote::class)
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
            ])
            ->getMock();

        $this->shippingAddressMock = $this->getMockBuilder(Address::class)
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

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            ShippingMethodManagement::class,
            [
                'quoteRepository' => $this->quoteRepositoryMock,
                'converter' => $this->converterMock,
                'addressRepository' => $this->addressRepositoryMock,
                'totalsCollector' => $this->totalsCollectorMock,
                'quoteAddressResource' => $this->quoteAddressResourceMock,
                'dataProcessor' => $this->dataProcessorMock
            ]
        );
    }

    public function testGetMethodWhenShippingAddressIsNotSet()
    {
        $cartId = 666;
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')->with($cartId)->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())
            ->method('getShippingAddress')->willReturn($this->shippingAddressMock);
        $this->shippingAddressMock->expects($this->once())->method('getCountryId')->willReturn(null);

        $this->expectException(StateException::class);
        $this->expectExceptionMessage('The shipping address is missing. Set the address and try again.');

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
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')->with($cartId)->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())
            ->method('getShippingAddress')->willReturn($this->shippingAddressMock);
        $this->quoteMock->expects($this->once())
            ->method('getQuoteCurrencyCode')->willReturn($currencyCode);
        $this->shippingAddressMock->method('getCountryId')->willReturn($countryId);
        $this->shippingAddressMock->method('getShippingMethod')->willReturn('one_two');

        $this->shippingAddressMock->expects($this->once())->method('collectShippingRates')->willReturnSelf();
        $shippingRateMock = $this->createMock(Rate::class);

        $this->shippingAddressMock->expects($this->once())
            ->method('getShippingRateByCode')
            ->with('one_two')
            ->willReturn($shippingRateMock);

        $shippingMethodMock = $this->createMock(ShippingMethodInterface::class);
        $this->converterMock->expects($this->once())
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

        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')->with($cartId)->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())
            ->method('getShippingAddress')->willReturn($this->shippingAddressMock);
        $this->shippingAddressMock->method('getCountryId')->willReturn($countryId);
        $this->shippingAddressMock->method('getShippingMethod')->willReturn(null);

        $this->assertNull($this->model->get($cartId));
    }

    /**
     * Test to get lists applicable shipping methods for a specified quote
     */
    public function testGetListForVirtualCart()
    {
        $cartId = 834;
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())
            ->method('isVirtual')->willReturn(true);

        $this->assertEquals([], $this->model->getList($cartId));
    }

    /**
     * Test to get lists applicable shipping methods for a specified quote
     */
    public function testGetListForEmptyCart()
    {
        $cartId = 834;
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())
            ->method('isVirtual')
            ->willReturn(false);
        $this->quoteMock->expects($this->once())
            ->method('getItemsCount')
            ->willReturn(0);

        $this->assertEquals([], $this->model->getList($cartId));
    }

    public function testGetListWhenShippingAddressIsNotSet()
    {
        $cartId = 834;
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')->with($cartId)->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())
            ->method('isVirtual')->willReturn(false);
        $this->quoteMock->expects($this->once())
            ->method('getItemsCount')->willReturn(3);
        $this->quoteMock->expects($this->once())
            ->method('getShippingAddress')->willReturn($this->shippingAddressMock);
        $this->shippingAddressMock->expects($this->once())->method('getCountryId')->willReturn(null);

        $this->expectException(StateException::class);
        $this->expectExceptionMessage('The shipping address is missing. Set the address and try again.');

        $this->model->getList($cartId);
    }

    /**
     * Test to get lists applicable shipping methods for a specified quote
     */
    public function testGetList()
    {
        $cartId = 834;
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')->with($cartId)->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())
            ->method('isVirtual')->willReturn(false);
        $this->quoteMock->expects($this->once())
            ->method('getItemsCount')->willReturn(3);
        $this->quoteMock->expects($this->once())
            ->method('getShippingAddress')->willReturn($this->shippingAddressMock);
        $this->shippingAddressMock->expects($this->once())->method('getCountryId')->willReturn(345);
        $this->shippingAddressMock->expects($this->once())->method('collectShippingRates');
        $shippingRateMock = $this->createMock(Rate::class);
        $this->shippingAddressMock->expects($this->once())
            ->method('getGroupedAllShippingRates')
            ->willReturn([[$shippingRateMock]]);

        $currencyCode = 'EUR';
        $this->quoteMock->expects($this->once())
            ->method('getQuoteCurrencyCode')
            ->willReturn($currencyCode);

        $this->converterMock->expects($this->once())
            ->method('modelToDataObject')
            ->with($shippingRateMock, $currencyCode)
            ->willReturn('RateValue');
        $this->assertEquals(['RateValue'], $this->model->getList($cartId));
    }

    public function testSetMethodWithInputException()
    {
        $cartId = 12;
        $carrierCode = 34;
        $methodCode = 56;
        $this->quoteRepositoryMock->expects($this->exactly(2))
            ->method('getActive')
            ->with($cartId)
            ->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())->method('getItemsCount')->willReturn(0);
        $this->quoteMock->expects($this->never())->method('isVirtual');

        $this->expectException(InputException::class);
        $this->expectExceptionMessage(
            'The shipping method can\'t be set for an empty cart. Add an item to cart and try again.'
        );

        $this->model->set($cartId, $carrierCode, $methodCode);
    }

    public function testSetMethodWithVirtualProduct()
    {
        $cartId = 12;
        $carrierCode = 34;
        $methodCode = 56;

        $this->quoteRepositoryMock->expects($this->exactly(2))
            ->method('getActive')
            ->with($cartId)
            ->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())->method('getItemsCount')->willReturn(1);
        $this->quoteMock->expects($this->once())->method('isVirtual')->willReturn(true);

        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage('The Cart includes virtual product(s) only, so a shipping address is not used.');

        $this->model->set($cartId, $carrierCode, $methodCode);
    }

    public function testSetMethodWithoutShippingAddress()
    {
        $cartId = 12;
        $carrierCode = 34;
        $methodCode = 56;
        $this->quoteRepositoryMock->expects($this->exactly(2))
            ->method('getActive')
            ->with($cartId)
            ->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())->method('getItemsCount')->willReturn(1);
        $this->quoteMock->expects($this->once())->method('isVirtual')->willReturn(false);
        $this->quoteMock->expects($this->once())
            ->method('getShippingAddress')->willReturn($this->shippingAddressMock);
        $this->shippingAddressMock->expects($this->once())->method('getCountryId')->willReturn(null);
        $this->quoteAddressResourceMock->expects($this->once())->method('delete')->with($this->shippingAddressMock);

        $this->expectException(StateException::class);
        $this->expectExceptionMessage('The shipping address is missing. Set the address and try again.');

        $this->model->set($cartId, $carrierCode, $methodCode);
    }

    public function testSetMethodWithCouldNotSaveException()
    {
        $cartId = 12;
        $carrierCode = 34;
        $methodCode = 56;
        $countryId = 1;

        $this->quoteRepositoryMock->expects($this->exactly(2))
            ->method('getActive')
            ->with($cartId)
            ->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())->method('getItemsCount')->willReturn(1);
        $this->quoteMock->expects($this->once())->method('isVirtual')->willReturn(false);
        $this->quoteMock->expects($this->once())
            ->method('getShippingAddress')
            ->willReturn($this->shippingAddressMock);
        $this->shippingAddressMock->expects($this->once())
            ->method('getCountryId')
            ->willReturn($countryId);
        $this->shippingAddressMock->expects($this->once())
            ->method('setShippingMethod')
            ->with($carrierCode . '_' . $methodCode);
        $exception = new \Exception('Custom Error');
        $this->quoteMock->expects($this->once())->method('collectTotals')->willReturnSelf();
        $this->quoteRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->quoteMock)
            ->willThrowException($exception);

        $this->expectException(CouldNotSaveException::class);
        $this->expectExceptionMessage('The shipping method can\'t be set. Custom Error');

        $this->model->set($cartId, $carrierCode, $methodCode);
    }

    public function testSetMethodWithoutAddress()
    {
        $cartId = 12;
        $carrierCode = 34;
        $methodCode = 56;
        $this->quoteRepositoryMock->expects($this->exactly(2))
            ->method('getActive')
            ->with($cartId)
            ->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())->method('getItemsCount')->willReturn(1);
        $this->quoteMock->expects($this->once())->method('isVirtual')->willReturn(false);
        $this->quoteMock->expects($this->once())
            ->method('getShippingAddress')
            ->willReturn($this->shippingAddressMock);
        $this->shippingAddressMock->expects($this->once())->method('getCountryId');
        $this->quoteAddressResourceMock->expects($this->once())->method('delete')->with($this->shippingAddressMock);

        $this->expectException(StateException::class);
        $this->expectExceptionMessage('The shipping address is missing. Set the address and try again.');

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
        $this->quoteRepositoryMock->expects($this->exactly(2))
            ->method('getActive')
            ->with($cartId)
            ->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())->method('getItemsCount')->willReturn(1);
        $this->quoteMock->expects($this->once())->method('isVirtual')->willReturn(false);
        $this->quoteMock->expects($this->once())
            ->method('getShippingAddress')->willReturn($this->shippingAddressMock);
        $this->shippingAddressMock->expects($this->once())
            ->method('getCountryId')->willReturn($countryId);
        $this->shippingAddressMock->expects($this->once())
            ->method('setShippingMethod')->with($carrierCode . '_' . $methodCode);
        $this->quoteMock->expects($this->once())->method('collectTotals')->willReturnSelf();
        $this->quoteRepositoryMock->expects($this->once())->method('save')->with($this->quoteMock);

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

        $this->quoteRepositoryMock->expects(static::once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($this->quoteMock);

        $this->quoteMock->expects(static::once())
            ->method('isVirtual')
            ->willReturn(false);
        $this->quoteMock->expects(static::once())
            ->method('getItemsCount')
            ->willReturn(1);

        $this->quoteMock->expects(static::once())
            ->method('getShippingAddress')
            ->willReturn($this->shippingAddressMock);

        $this->dataProcessorMock->method('buildOutputDataArray')
            ->willReturn($addressData + [ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY => $addressExtAttr]);

        $this->shippingAddressMock->expects($this->once())->method('addData')->with($addressData);

        $this->shippingAddressMock->expects(static::once())
            ->method('setCollectShippingRates')
            ->with(true)
            ->willReturnSelf();

        $this->totalsCollectorMock->expects(static::once())
            ->method('collectAddressTotals')
            ->with($this->quoteMock, $this->shippingAddressMock)
            ->willReturnSelf();

        $rate = $this->getMockBuilder(Rate::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $methodObject = $this->getMockForAbstractClass(ShippingMethodInterface::class);
        $expectedRates = [$methodObject];

        $this->shippingAddressMock->expects(static::once())
            ->method('getGroupedAllShippingRates')
            ->willReturn([[$rate]]);

        $this->quoteMock->expects(static::once())
            ->method('getQuoteCurrencyCode')
            ->willReturn($currencyCode);

        $this->converterMock->expects(static::once())
            ->method('modelToDataObject')
            ->with($rate, $currencyCode)
            ->willReturn($methodObject);

        $carriersRates = $this->model->estimateByExtendedAddress($cartId, $address);
        static::assertEquals($expectedRates, $carriersRates);
    }

    /**
     * @covers \Magento\Quote\Model\ShippingMethodManagement::estimateByAddressId
     */
    public function testEstimateByAddressId()
    {
        $cartId = 1;

        $addressData = [
            'region' => 'California',
            'region_id' => 23,
            'country_id' => 1,
            'postcode' => 90200,
        ];
        $currencyCode = 'UAH';

        /**
         * @var AddressInterface|MockObject $addressMock
         */
        $addressMock = $this->getMockBuilder(AddressInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->addressRepositoryMock->method('getById')
            ->willReturn($addressMock);

        $this->quoteRepositoryMock->expects(static::once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($this->quoteMock);

        $this->quoteMock->expects(static::once())
            ->method('isVirtual')
            ->willReturn(false);
        $this->quoteMock->expects(static::once())
            ->method('getItemsCount')
            ->willReturn(1);

        $this->quoteMock->expects(static::once())
            ->method('getShippingAddress')
            ->willReturn($this->shippingAddressMock);

        $this->dataProcessorMock->method('buildOutputDataArray')
            ->willReturn($addressData);

        $this->shippingAddressMock->expects(static::once())
            ->method('setCollectShippingRates')
            ->with(true)
            ->willReturnSelf();

        $this->totalsCollectorMock->expects(static::once())
            ->method('collectAddressTotals')
            ->with($this->quoteMock, $this->shippingAddressMock)
            ->willReturnSelf();

        $rate = $this->getMockBuilder(Rate::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $methodObject = $this->getMockForAbstractClass(ShippingMethodInterface::class);
        $expectedRates = [$methodObject];

        $this->shippingAddressMock->expects(static::once())
            ->method('getGroupedAllShippingRates')
            ->willReturn([[$rate]]);

        $this->quoteMock->expects(static::once())
            ->method('getQuoteCurrencyCode')
            ->willReturn($currencyCode);

        $this->converterMock->expects(static::once())
            ->method('modelToDataObject')
            ->with($rate, $currencyCode)
            ->willReturn($methodObject);

        $carriersRates = $this->model->estimateByAddressId($cartId, $addressMock);
        static::assertEquals($expectedRates, $carriersRates);
    }
}
