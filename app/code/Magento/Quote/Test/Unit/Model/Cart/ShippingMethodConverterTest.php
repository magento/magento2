<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Quote\Test\Unit\Model\Cart;

use Magento\Quote\Model\Cart\ShippingMethodConverter;

class ShippingMethodConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ShippingMethodConverter
     */
    protected $converter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $shippingMethodDataFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $rateModelMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $currencyMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $shippingMethodMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $taxHelper;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->shippingMethodDataFactoryMock = $this->getMock(
            \Magento\Quote\Api\Data\ShippingMethodInterfaceFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->storeManagerMock = $this->getMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->currencyMock = $this->getMock(\Magento\Directory\Model\Currency::class, [], [], '', false);
        $this->shippingMethodMock = $this->getMock(
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
            ],
            [],
            '',
            false);
        $this->rateModelMock = $this->getMock(
            \Magento\Quote\Model\Quote\Address\Rate::class,
            [
                'getPrice',
                'getCarrier',
                'getMethod',
                'getCarrierTitle',
                'getMethodTitle',
                '__wakeup',
                'getAddress'
            ],
            [],
            '',
            false);
        $this->storeMock = $this->getMock(\Magento\Store\Model\Store::class, [], [], '', false);
        $this->taxHelper = $this->getMock(\Magento\Tax\Helper\Data::class, [], [], '', false);

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

        $this->storeManagerMock->expects($this->once())->method('getStore')->will($this->returnValue($this->storeMock));
        $this->storeMock->expects($this->once())
            ->method('getBaseCurrency')
            ->will($this->returnValue($this->currencyMock));

        $this->rateModelMock->expects($this->once())->method('getCarrier')->will($this->returnValue('CARRIER_CODE'));
        $this->rateModelMock->expects($this->once())->method('getMethod')->will($this->returnValue('METHOD_CODE'));
        $this->rateModelMock->expects($this->any())->method('getPrice')->will($this->returnValue($price));
        $this->currencyMock->expects($this->at(0))
            ->method('convert')->with($price, 'USD')->willReturn(100.12);
        $this->currencyMock->expects($this->at(1))
            ->method('convert')->with($shippingPriceExclTax, 'USD')->willReturn($shippingPriceExclTax);
        $this->currencyMock->expects($this->at(2))
            ->method('convert')->with($shippingPriceInclTax, 'USD')->willReturn($shippingPriceInclTax);

        $this->rateModelMock->expects($this->once())
            ->method('getCarrierTitle')->will($this->returnValue('CARRIER_TITLE'));
        $this->rateModelMock->expects($this->once())
            ->method('getMethodTitle')->will($this->returnValue('METHOD_TITLE'));

        $quoteMock = $this->getMock(\Magento\Quote\Model\Quote::class, [], [], '', false);
        $addressMock = $this->getMock(\Magento\Quote\Model\Quote\Address::class, [], [], '', false);
        $this->rateModelMock->expects($this->exactly(4))->method('getAddress')->willReturn($addressMock);

        $addressMock->expects($this->exactly(2))->method('getQuote')->willReturn($quoteMock);
        $quoteMock->expects($this->exactly(2))->method('getCustomerTaxClassId')->willReturn($customerTaxClassId);

        $this->shippingMethodDataFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->shippingMethodMock));

        $this->shippingMethodMock->expects($this->once())
            ->method('setCarrierCode')
            ->with('CARRIER_CODE')
            ->will($this->returnValue($this->shippingMethodMock));
        $this->shippingMethodMock->expects($this->once())
            ->method('setMethodCode')
            ->with('METHOD_CODE')
            ->will($this->returnValue($this->shippingMethodMock));
        $this->shippingMethodMock->expects($this->once())
            ->method('setCarrierTitle')
            ->with('CARRIER_TITLE')
            ->will($this->returnValue($this->shippingMethodMock));
        $this->shippingMethodMock->expects($this->once())
            ->method('setMethodTitle')
            ->with('METHOD_TITLE')
            ->will($this->returnValue($this->shippingMethodMock));
        $this->shippingMethodMock->expects($this->once())
            ->method('setAmount')
            ->with('100.12')
            ->will($this->returnValue($this->shippingMethodMock));
        $this->shippingMethodMock->expects($this->once())
            ->method('setBaseAmount')
            ->with('90.12')
            ->will($this->returnValue($this->shippingMethodMock));
        $this->shippingMethodMock->expects($this->once())
            ->method('setAvailable')
            ->with(true)
            ->will($this->returnValue($this->shippingMethodMock));
        $this->shippingMethodMock->expects($this->once())
            ->method('setPriceExclTax')
            ->with($shippingPriceExclTax)
            ->will($this->returnValue($this->shippingMethodMock));
        $this->shippingMethodMock->expects($this->once())
            ->method('setPriceInclTax')
            ->with($shippingPriceInclTax)
            ->will($this->returnValue($this->shippingMethodMock));

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
