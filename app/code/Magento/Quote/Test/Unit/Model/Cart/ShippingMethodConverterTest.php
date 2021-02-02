<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Test\Unit\Model\Cart;

use Magento\Quote\Model\Cart\ShippingMethodConverter;

class ShippingMethodConverterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ShippingMethodConverter
     */
    protected $converter;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $shippingMethodDataFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $rateModelMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $currencyMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $shippingMethodMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $taxHelper;

    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->shippingMethodDataFactoryMock = $this->createPartialMock(
            \Magento\Quote\Api\Data\ShippingMethodInterfaceFactory::class,
            ['create']
        );
        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->currencyMock = $this->createMock(\Magento\Directory\Model\Currency::class);
        $this->shippingMethodMock = $this->createPartialMock(
            \Magento\Quote\Model\Cart\ShippingMethod::class,
            [
                'create',
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
        );
        $this->rateModelMock = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Address\Rate::class,
            [
                'getPrice',
                'getCarrier',
                'getMethod',
                'getCarrierTitle',
                'getMethodTitle',
                '__wakeup',
                'getAddress'
            ]
        );
        $this->storeMock = $this->createMock(\Magento\Store\Model\Store::class);
        $this->taxHelper = $this->createMock(\Magento\Tax\Helper\Data::class);

        $this->converter = $objectManager->getObject(
            \Magento\Quote\Model\Cart\ShippingMethodConverter::class,
            [
                'shippingMethodDataFactory' => $this->shippingMethodDataFactoryMock,
                'storeManager' => $this->storeManagerMock,
                'taxHelper' => $this->taxHelper
            ]
        );
    }

    public function testModelToDataObject()
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
        $this->currencyMock->expects($this->at(0))
            ->method('convert')->with($price, 'USD')->willReturn(100.12);
        $this->currencyMock->expects($this->at(1))
            ->method('convert')->with($shippingPriceExclTax, 'USD')->willReturn($shippingPriceExclTax);
        $this->currencyMock->expects($this->at(2))
            ->method('convert')->with($shippingPriceInclTax, 'USD')->willReturn($shippingPriceInclTax);

        $this->rateModelMock->expects($this->once())
            ->method('getCarrierTitle')->willReturn('CARRIER_TITLE');
        $this->rateModelMock->expects($this->once())
            ->method('getMethodTitle')->willReturn('METHOD_TITLE');

        $quoteMock = $this->createMock(\Magento\Quote\Model\Quote::class);
        $addressMock = $this->createMock(\Magento\Quote\Model\Quote\Address::class);
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

        $this->taxHelper->expects($this->at(0))
        ->method('getShippingPrice')
        ->with($price, false, $addressMock, $customerTaxClassId)
        ->willReturn($shippingPriceExclTax);

        $this->taxHelper->expects($this->at(1))
            ->method('getShippingPrice')
            ->with($price, true, $addressMock, $customerTaxClassId)
            ->willReturn($shippingPriceInclTax);

        $this->assertEquals(
            $this->shippingMethodMock,
            $this->converter->modelToDataObject($this->rateModelMock, 'USD')
        );
    }
}
