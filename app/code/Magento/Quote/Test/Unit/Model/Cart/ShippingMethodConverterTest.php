<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
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
use Magento\Quote\Test\Unit\Helper\RateTestHelper;

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
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->shippingMethodDataFactoryMock = $this->createPartialMock(
            ShippingMethodInterfaceFactory::class,
            ['create']
        );
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->currencyMock = $this->createMock(Currency::class);
        $this->shippingMethodMock = $this->createMock(ShippingMethod::class);
        $this->rateModelMock = $this->createMock(RateTestHelper::class);
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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
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

        $this->rateModelMock->method('getCarrier')->willReturn('CARRIER_CODE');
        $this->rateModelMock->method('getMethod')->willReturn('METHOD_CODE');
        $this->rateModelMock->method('getPrice')->willReturn($price);
        $this->currencyMock
            ->method('convert')
            ->willReturnCallback(function ($arg1, $arg2) use ($price, $shippingPriceExclTax, $shippingPriceInclTax) {
                if ($arg1 == $price && $arg2 == 'USD') {
                    return 100.12;
                } elseif ($arg1 == $shippingPriceExclTax && $arg2 == 'USD') {
                    return $shippingPriceExclTax;
                } elseif ($arg1 == $shippingPriceInclTax && $arg2 == 'USD') {
                    return $shippingPriceInclTax;
                }
            });

        $this->rateModelMock->method('getCarrierTitle')->willReturn('CARRIER_TITLE');
        $this->rateModelMock->method('getMethodTitle')->willReturn('METHOD_TITLE');

        $quoteMock = $this->createMock(Quote::class);
        $addressMock = $this->createMock(Address::class);
        $this->rateModelMock->expects($this->exactly(4))->method('getAddress')->willReturn($addressMock);

        $addressMock->expects($this->exactly(2))->method('getQuote')->willReturn($quoteMock);
        $quoteMock->expects($this->exactly(2))->method('getCustomerTaxClassId')->willReturn($customerTaxClassId);

        $this->shippingMethodDataFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->shippingMethodMock);

        $this->shippingMethodMock->expects($this->once())
            ->method('setCarrierCode')
            ->with('CARRIER_CODE')
            ->willReturnSelf();
        $this->shippingMethodMock->expects($this->once())
            ->method('setMethodCode')
            ->with('METHOD_CODE')
            ->willReturnSelf();
        $this->shippingMethodMock->expects($this->once())
            ->method('setCarrierTitle')
            ->with('CARRIER_TITLE')
            ->willReturnSelf();
        $this->shippingMethodMock->expects($this->once())
            ->method('setMethodTitle')
            ->with('METHOD_TITLE')
            ->willReturnSelf();
        $this->shippingMethodMock->expects($this->once())
            ->method('setAmount')
            ->with('100.12')
            ->willReturnSelf();
        $this->shippingMethodMock->expects($this->once())
            ->method('setBaseAmount')
            ->with('90.12')
            ->willReturnSelf();
        $this->shippingMethodMock->expects($this->once())
            ->method('setAvailable')
            ->with(true)
            ->willReturnSelf();
        $this->shippingMethodMock->method('setErrorMessage')->willReturnSelf();
        $this->shippingMethodMock->expects($this->once())
            ->method('setPriceExclTax')
            ->with($shippingPriceExclTax)
            ->willReturnSelf();
        $this->shippingMethodMock->expects($this->once())
            ->method('setPriceInclTax')
            ->with($shippingPriceInclTax)
            ->willReturnSelf();

        $this->taxHelper
            ->method('getShippingPrice')
            ->willReturnCallback(function ($arg1, $arg2, $arg3, $arg4)
 use ($price, $addressMock, $customerTaxClassId, $shippingPriceExclTax, $shippingPriceInclTax) {
                if ($arg1 == $price && $arg2 == false && $arg3 == $addressMock && $arg4 == $customerTaxClassId) {
                    return $shippingPriceExclTax;
                } elseif ($arg1 == $price && $arg2 == true && $arg3 == $addressMock &&
                        $arg4 == $customerTaxClassId) {
                    return $shippingPriceInclTax;
                }
            });
        $this->assertEquals(
            $this->shippingMethodMock,
            $this->converter->modelToDataObject($this->rateModelMock, 'USD')
        );
    }
}
