<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Quote\Model\Cart\ShippingMethodConverter;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Rate;
use Magento\Quote\Model\Quote\TotalsCollector;
use Magento\Quote\Model\QuoteRepository;
use Magento\Quote\Model\ShippingMethodManagement;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ShippingMethodManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ShippingMethodManagement
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $shippingMethodMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $methodDataFactoryMock;

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
     * @var TotalsCollector|MockObject
     */
    private $totalsCollector;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->quoteRepository = $this->getMock('\Magento\Quote\Api\CartRepositoryInterface');
        $this->methodDataFactoryMock = $this->getMock(
            '\Magento\Quote\Api\Data\ShippingMethodInterfaceFactory',
            [
                'create'
            ],
            [],
            '',
            false
        );

        $this->storeMock = $this->getMock('\Magento\Store\Model\Store', [], [], '', false);
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
                'methodDataFactory' => $this->methodDataFactoryMock,
                'converter' => $this->converter,
                'totalsCollector' => $this->totalsCollector
            ]
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage Shipping address not set.
     */
    public function testGetMethodWhenShippingAddressIsNotSet()
    {
        $cartId = 666;
        $this->quoteRepository->expects($this->once())
            ->method('getActive')->with($cartId)->will($this->returnValue($this->quote));
        $this->quote->expects($this->once())
            ->method('getShippingAddress')->will($this->returnValue($this->shippingAddress));
        $this->shippingAddress->expects($this->once())->method('getCountryId')->will($this->returnValue(null));

        $this->assertNull($this->model->get($cartId));
    }

    public function testGetMethod()
    {
        $cartId = 666;
        $countryId = 1;
        $currencyCode = 'US_dollar';
        $this->quoteRepository->expects($this->once())
            ->method('getActive')->with($cartId)->will($this->returnValue($this->quote));
        $this->quote->expects($this->once())
            ->method('getShippingAddress')->will($this->returnValue($this->shippingAddress));
        $this->quote->expects($this->once())
            ->method('getQuoteCurrencyCode')->willReturn($currencyCode);
        $this->shippingAddress->expects($this->any())
            ->method('getCountryId')->will($this->returnValue($countryId));
        $this->shippingAddress->expects($this->any())
            ->method('getShippingMethod')->will($this->returnValue('one_two'));

        $this->shippingAddress->expects($this->once())->method('collectShippingRates')->willReturnSelf();
        $shippingRateMock = $this->getMock('\Magento\Quote\Model\Quote\Address\Rate', [], [], '', false);

        $this->shippingAddress->expects($this->once())
            ->method('getShippingRateByCode')
            ->with('one_two')
            ->willReturn($shippingRateMock);

        $this->shippingMethodMock = $this->getMock('\Magento\Quote\Api\Data\ShippingMethodInterface');
        $this->converter->expects($this->once())
            ->method('modelToDataObject')
            ->with($shippingRateMock, $currencyCode)
            ->willReturn($this->shippingMethodMock);
        $this->model->get($cartId);
    }

    public function testGetMethodIfMethodIsNotSet()
    {
        $cartId = 666;
        $countryId = 1;

        $this->quoteRepository->expects($this->once())
            ->method('getActive')->with($cartId)->will($this->returnValue($this->quote));
        $this->quote->expects($this->once())
            ->method('getShippingAddress')->will($this->returnValue($this->shippingAddress));
        $this->shippingAddress->expects($this->any())
            ->method('getCountryId')->will($this->returnValue($countryId));
        $this->shippingAddress->expects($this->any())
            ->method('getShippingMethod')->will($this->returnValue(null));

        $this->assertNull($this->model->get($cartId));
    }

    public function testGetListForVirtualCart()
    {
        $cartId = 834;
        $this->quoteRepository->expects($this->once())
            ->method('getActive')->with($cartId)->will($this->returnValue($this->quote));
        $this->quote->expects($this->once())
            ->method('isVirtual')->will($this->returnValue(true));

        $this->assertEquals([], $this->model->getList($cartId));
    }

    public function testGetListForEmptyCart()
    {
        $cartId = 834;
        $this->quoteRepository->expects($this->once())
            ->method('getActive')->with($cartId)->will($this->returnValue($this->quote));
        $this->quote->expects($this->once())
            ->method('isVirtual')->will($this->returnValue(false));
        $this->quote->expects($this->once())
            ->method('getItemsCount')->will($this->returnValue(0));

        $this->assertEquals([], $this->model->getList($cartId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage Shipping address not set.
     */
    public function testGetListWhenShippingAddressIsNotSet()
    {
        $cartId = 834;
        $this->quoteRepository->expects($this->once())
            ->method('getActive')->with($cartId)->will($this->returnValue($this->quote));
        $this->quote->expects($this->once())
            ->method('isVirtual')->will($this->returnValue(false));
        $this->quote->expects($this->once())
            ->method('getItemsCount')->will($this->returnValue(3));
        $this->quote->expects($this->once())
            ->method('getShippingAddress')->will($this->returnValue($this->shippingAddress));
        $this->shippingAddress->expects($this->once())->method('getCountryId')->will($this->returnValue(null));

        $this->model->getList($cartId);
    }

    public function testGetList()
    {
        $cartId = 834;
        $this->quoteRepository->expects($this->once())
            ->method('getActive')->with($cartId)->will($this->returnValue($this->quote));
        $this->quote->expects($this->once())
            ->method('isVirtual')->will($this->returnValue(false));
        $this->quote->expects($this->once())
            ->method('getItemsCount')->will($this->returnValue(3));
        $this->quote->expects($this->once())
            ->method('getShippingAddress')->will($this->returnValue($this->shippingAddress));
        $this->shippingAddress->expects($this->once())->method('getCountryId')->will($this->returnValue(345));
        $this->shippingAddress->expects($this->once())->method('collectShippingRates');
        $shippingRateMock = $this->getMock('\Magento\Quote\Model\Quote\Address\Rate', [], [], '', false);
        $this->shippingAddress->expects($this->once())
            ->method('getGroupedAllShippingRates')
            ->will($this->returnValue([[$shippingRateMock]]));

        $currencyCode = 'EUR';
        $this->quote->expects($this->once())
            ->method('getQuoteCurrencyCode')
            ->will($this->returnValue($currencyCode));

        $this->converter->expects($this->once())
            ->method('modelToDataObject')
            ->with($shippingRateMock, $currencyCode)
            ->will($this->returnValue('RateValue'));
        $this->assertEquals(['RateValue'], $this->model->getList($cartId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Shipping method is not applicable for empty cart
     */
    public function testSetMethodWithInputException()
    {
        $cartId = 12;
        $carrierCode = 34;
        $methodCode = 56;
        $this->quoteRepository->expects($this->once())
            ->method('getActive')->with($cartId)->will($this->returnValue($this->quote));
        $this->quote->expects($this->once())->method('getItemsCount')->will($this->returnValue(0));
        $this->quote->expects($this->never())->method('isVirtual');

        $this->model->set($cartId, $carrierCode, $methodCode);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Cart contains virtual product(s) only. Shipping method is not applicable.
     */
    public function testSetMethodWithVirtualProduct()
    {
        $cartId = 12;
        $carrierCode = 34;
        $methodCode = 56;

        $this->quoteRepository->expects($this->once())
            ->method('getActive')->with($cartId)->will($this->returnValue($this->quote));
        $this->quote->expects($this->once())->method('getItemsCount')->will($this->returnValue(1));
        $this->quote->expects($this->once())->method('isVirtual')->will($this->returnValue(true));

        $this->model->set($cartId, $carrierCode, $methodCode);
    }

    /**
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage Shipping address is not set
     */
    public function testSetMethodWithoutShippingAddress()
    {
        $cartId = 12;
        $carrierCode = 34;
        $methodCode = 56;
        $this->quoteRepository->expects($this->once())
            ->method('getActive')->with($cartId)->will($this->returnValue($this->quote));
        $this->quote->expects($this->once())->method('getItemsCount')->will($this->returnValue(1));
        $this->quote->expects($this->once())->method('isVirtual')->will($this->returnValue(false));
        $this->quote->expects($this->once())
            ->method('getShippingAddress')->will($this->returnValue($this->shippingAddress));
        $this->shippingAddress->expects($this->once())->method('getCountryId')->will($this->returnValue(null));

        $this->model->set($cartId, $carrierCode, $methodCode);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Carrier with such method not found: 34, 56
     */
    public function testSetMethodWithNotFoundMethod()
    {
        $cartId = 12;
        $carrierCode = 34;
        $methodCode = 56;
        $countryId = 1;
        $this->quoteRepository->expects($this->once())
            ->method('getActive')->with($cartId)->will($this->returnValue($this->quote));
        $this->quote->expects($this->once())->method('getItemsCount')->will($this->returnValue(1));
        $this->quote->expects($this->once())->method('isVirtual')->will($this->returnValue(false));
        $this->quote->expects($this->once())
            ->method('getShippingAddress')->will($this->returnValue($this->shippingAddress));
        $this->shippingAddress->expects($this->once())
            ->method('getCountryId')->will($this->returnValue($countryId));
        $this->shippingAddress->expects($this->once())
            ->method('setShippingMethod')->with($carrierCode . '_' . $methodCode);
        $this->shippingAddress->expects($this->once())
            ->method('getShippingRateByCode')->will($this->returnValue(false));
        $this->shippingAddress->expects($this->never())->method('save');

        $this->model->set($cartId, $carrierCode, $methodCode);
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Cannot set shipping method. Custom Error
     */
    public function testSetMethodWithCouldNotSaveException()
    {
        $cartId = 12;
        $carrierCode = 34;
        $methodCode = 56;
        $countryId = 1;

        $this->quoteRepository->expects($this->once())
            ->method('getActive')->with($cartId)->will($this->returnValue($this->quote));
        $this->quote->expects($this->once())->method('getItemsCount')->will($this->returnValue(1));
        $this->quote->expects($this->once())->method('isVirtual')->will($this->returnValue(false));
        $this->quote->expects($this->once())
            ->method('getShippingAddress')->will($this->returnValue($this->shippingAddress));
        $this->shippingAddress->expects($this->once())
            ->method('getCountryId')->will($this->returnValue($countryId));
        $this->shippingAddress->expects($this->once())
            ->method('setShippingMethod')->with($carrierCode . '_' . $methodCode);
        $this->shippingAddress->expects($this->once())
            ->method('getShippingRateByCode')->will($this->returnValue(true));
        $exception = new \Exception('Custom Error');
        $this->quote->expects($this->once())->method('collectTotals')->will($this->returnSelf());
        $this->quoteRepository->expects($this->once())
            ->method('save')
            ->with($this->quote)
            ->willThrowException($exception);

        $this->model->set($cartId, $carrierCode, $methodCode);
    }

    /**
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage Shipping address is not set
     */
    public function testSetMethodWithoutAddress()
    {
        $cartId = 12;
        $carrierCode = 34;
        $methodCode = 56;
        $this->quoteRepository->expects($this->once())
            ->method('getActive')->with($cartId)->will($this->returnValue($this->quote));
        $this->quote->expects($this->once())->method('getItemsCount')->will($this->returnValue(1));
        $this->quote->expects($this->once())->method('isVirtual')->will($this->returnValue(false));
        $this->quote->expects($this->once())
            ->method('getShippingAddress')->will($this->returnValue($this->shippingAddress));
        $this->shippingAddress->expects($this->once())->method('getCountryId');

        $this->model->set($cartId, $carrierCode, $methodCode);
    }

    public function testSetMethod()
    {
        $cartId = 12;
        $carrierCode = 34;
        $methodCode = 56;
        $countryId = 1;
        $this->quoteRepository->expects($this->once())
            ->method('getActive')->with($cartId)->will($this->returnValue($this->quote));
        $this->quote->expects($this->once())->method('getItemsCount')->will($this->returnValue(1));
        $this->quote->expects($this->once())->method('isVirtual')->will($this->returnValue(false));
        $this->quote->expects($this->once())
            ->method('getShippingAddress')->will($this->returnValue($this->shippingAddress));
        $this->shippingAddress->expects($this->once())
            ->method('getCountryId')->will($this->returnValue($countryId));
        $this->shippingAddress->expects($this->once())
            ->method('setShippingMethod')->with($carrierCode . '_' . $methodCode);
        $this->shippingAddress->expects($this->once())
            ->method('getShippingRateByCode')->will($this->returnValue(true));
        $this->quote->expects($this->once())->method('collectTotals')->will($this->returnSelf());
        $this->quoteRepository->expects($this->once())->method('save')->with($this->quote);

        $this->assertTrue($this->model->set($cartId, $carrierCode, $methodCode));
    }

    /**
     * @covers \Magento\Quote\Model\ShippingMethodManagement::estimateByExtendedAddress
     */
    public function testEstimateByExtendedAddress()
    {
        $cartId = 1;

        $addressData = [
            'region' => 'California',
            'region_id' => 23,
            'country_id' => 1,
            'postcode' => 90200
        ];
        $currencyCode = 'UAH';

        $address = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData'])
            ->getMock();

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

        $address->expects(static::once())
            ->method('getData')
            ->willReturn($addressData);

        $this->quote->expects(static::once())
            ->method('getShippingAddress')
            ->willReturn($this->shippingAddress);

        $this->shippingAddress->expects(static::once())
            ->method('addData')
            ->with($addressData)
            ->willReturnSelf();
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
}
