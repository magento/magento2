<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\Cart;

use Magento\Directory\Model\Currency;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\Data\ShippingMethodInterfaceFactory;
use Magento\Quote\Model\Cart\ShippingMethod;
use Magento\Quote\Model\Cart\ShippingMethodConverter;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Rate;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Helper\Data;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ShippingMethodConverterTest extends TestCase
{
    /**
     * @var ShippingMethodConverter
     */
    protected $converter;

    /**
     * @var MockObject
     */
    protected $shippingMethodDataFactoryMock;

    /**
     * @var MockObject
     */
    protected $storeManagerMock;

    /**
     * @var MockObject
     */
    protected $rateModelMock;

    /**
     * @var MockObject
     */
    protected $currencyMock;

    /**
     * @var MockObject
     */
    protected $storeMock;

    /**
     * @var MockObject
     */
    protected $shippingMethodMock;

    /**
     * @var MockObject
     */
    protected $taxHelper;

    /**
     * @inheriDoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->shippingMethodDataFactoryMock = $this->createPartialMock(
            ShippingMethodInterfaceFactory::class,
            ['create']
        );
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->currencyMock = $this->createMock(Currency::class);
        $this->shippingMethodMock = $this->getMockBuilder(ShippingMethod::class)
            ->addMethods(['create'])
            ->onlyMethods(
                [
                    'setCarrierCode',
                    'setMethodCode',
                    'setCarrierTitle',
                    'setMethodTitle',
                    'setAmount',
                    'setBaseAmount',
                    'setAvailable',
                    'setPriceExclTax',
                    'setPriceInclTax'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->rateModelMock = $this->getMockBuilder(Rate::class)
            ->addMethods(['getPrice', 'getCarrier', 'getMethod', 'getCarrierTitle', 'getMethodTitle'])
            ->onlyMethods(['__wakeup', 'getAddress'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeMock = $this->createMock(Store::class);
        $this->taxHelper = $this->createMock(Data::class);

        $this->converter = $objectManager->getObject(
            ShippingMethodConverter::class,
            [
                'shippingMethodDataFactory' => $this->shippingMethodDataFactoryMock,
                'storeManager' => $this->storeManagerMock,
                'taxHelper' => $this->taxHelper
            ]
        );
    }

    /**
     * @return void
     */
    public function testModelToDataObject(): void
    {
        $customerTaxClassId = 100;
        $shippingPriceExclTax = 1000;
        $shippingPriceInclTax = 1500;
        $price = 90.12;

        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($this->storeMock);
        $this->storeMock->expects($this->once())
            ->method('getBaseCurrency')
            ->willReturn($this->currencyMock);

        $this->rateModelMock->expects($this->once())->method('getCarrier')->willReturn('CARRIER_CODE');
        $this->rateModelMock->expects($this->once())->method('getMethod')->willReturn('METHOD_CODE');
        $this->rateModelMock->expects($this->any())->method('getPrice')->willReturn($price);
        $this->currencyMock
            ->method('convert')
            ->withConsecutive([$price, 'USD'], [$shippingPriceExclTax, 'USD'], [$shippingPriceInclTax, 'USD'])
            ->willReturnOnConsecutiveCalls(100.12, $shippingPriceExclTax, $shippingPriceInclTax);

        $this->rateModelMock->expects($this->once())
            ->method('getCarrierTitle')->willReturn('CARRIER_TITLE');
        $this->rateModelMock->expects($this->once())
            ->method('getMethodTitle')->willReturn('METHOD_TITLE');

        $quoteMock = $this->createMock(Quote::class);
        $addressMock = $this->createMock(Address::class);
        $this->rateModelMock->expects($this->exactly(4))->method('getAddress')->willReturn($addressMock);

        $addressMock->expects($this->exactly(2))->method('getQuote')->willReturn($quoteMock);
        $quoteMock->expects($this->exactly(2))->method('getCustomerTaxClassId')->willReturn($customerTaxClassId);

        $this->shippingMethodDataFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->shippingMethodMock);

        $this->shippingMethodMock->expects($this->once())
            ->method('setCarrierCode')
            ->with('CARRIER_CODE')
            ->willReturn($this->shippingMethodMock);
        $this->shippingMethodMock->expects($this->once())
            ->method('setMethodCode')
            ->with('METHOD_CODE')
            ->willReturn($this->shippingMethodMock);
        $this->shippingMethodMock->expects($this->once())
            ->method('setCarrierTitle')
            ->with('CARRIER_TITLE')
            ->willReturn($this->shippingMethodMock);
        $this->shippingMethodMock->expects($this->once())
            ->method('setMethodTitle')
            ->with('METHOD_TITLE')
            ->willReturn($this->shippingMethodMock);
        $this->shippingMethodMock->expects($this->once())
            ->method('setAmount')
            ->with('100.12')
            ->willReturn($this->shippingMethodMock);
        $this->shippingMethodMock->expects($this->once())
            ->method('setBaseAmount')
            ->with('90.12')
            ->willReturn($this->shippingMethodMock);
        $this->shippingMethodMock->expects($this->once())
            ->method('setAvailable')
            ->with(true)
            ->willReturn($this->shippingMethodMock);
        $this->shippingMethodMock->expects($this->once())
            ->method('setPriceExclTax')
            ->with($shippingPriceExclTax)
            ->willReturn($this->shippingMethodMock);
        $this->shippingMethodMock->expects($this->once())
            ->method('setPriceInclTax')
            ->with($shippingPriceInclTax)
            ->willReturn($this->shippingMethodMock);

        $this->taxHelper
            ->method('getShippingPrice')
            ->withConsecutive(
                [$price, false, $addressMock, $customerTaxClassId],
                [$price, true, $addressMock, $customerTaxClassId]
            )
            ->willReturnOnConsecutiveCalls($shippingPriceExclTax, $shippingPriceInclTax);

        $this->assertEquals(
            $this->shippingMethodMock,
            $this->converter->modelToDataObject($this->rateModelMock, 'USD')
        );
    }
}
