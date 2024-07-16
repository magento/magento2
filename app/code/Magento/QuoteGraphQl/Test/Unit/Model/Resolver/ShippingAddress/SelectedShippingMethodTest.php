<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Test\Unit\Model\Resolver\ShippingAddress;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\Context;
use Magento\Quote\Model\Quote;
use Magento\QuoteGraphQl\Model\Resolver\ShippingAddress\SelectedShippingMethod;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Quote\Model\Cart\ShippingMethodConverter;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Rate;

/**
 * @see SelectedShippingMethod
 */
class SelectedShippingMethodTest extends TestCase
{
    /**
     * @var SelectedShippingMethod
     */
    private $selectedShippingMethod;

    /**
     * @var ShippingMethodConverter|MockObject
     */
    private $shippingMethodConverterMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var Address|MockObject
     */
    private $addressMock;

    /**
     * @var Rate|MockObject
     */
    private $rateMock;

    /**
     * @var Field|MockObject
     */
    private $fieldMock;

    /**
     * @var ResolveInfo|MockObject
     */
    private $resolveInfoMock;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var Quote|MockObject
     */
    private $quoteMock;

    /**
     * @var array
     */
    private $valueMock = [];

    protected function setUp(): void
    {
        $this->shippingMethodConverterMock = $this->createMock(ShippingMethodConverter::class);
        $this->contextMock = $this->createMock(Context::class);
        $this->fieldMock = $this->getMockBuilder(Field::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resolveInfoMock = $this->getMockBuilder(ResolveInfo::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMockForAbstractClass();
        $this->addressMock = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getShippingMethod','getAllShippingRates','getQuote',])
            ->AddMethods(['getShippingAmount','getMethod',])
            ->getMock();
        $this->rateMock = $this->getMockBuilder(Rate::class)
            ->disableOriginalConstructor()
            ->AddMethods(['getCode','getCarrier','getMethod'])
            ->getMock();
        $this->quoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->addMethods([
                'getQuoteCurrencyCode',
                'getMethodTitle',
                'getCarrierTitle',
                'getPriceExclTax',
                'getPriceInclTax'
            ])
            ->getMock();
        $this->selectedShippingMethod = new SelectedShippingMethod(
            $this->shippingMethodConverterMock
        );
    }

    public function testResolveWithoutModelInValueParameter(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('"model" value should be specified');
        $this->selectedShippingMethod->resolve(
            $this->fieldMock,
            $this->contextMock,
            $this->resolveInfoMock,
            $this->valueMock
        );
    }

    public function testResolve(): void
    {
        $this->valueMock = ['model' => $this->addressMock];
        $this->quoteMock
            ->method('getQuoteCurrencyCode')
            ->willReturn('USD');
        $this->quoteMock
            ->method('getMethodTitle')
            ->willReturn('method_title');
        $this->quoteMock
            ->method('getCarrierTitle')
            ->willReturn('carrier_title');
        $this->quoteMock
            ->expects($this->once())
            ->method('getPriceExclTax')
            ->willReturn('PriceExclTax');
        $this->quoteMock
            ->expects($this->once())
            ->method('getPriceInclTax')
            ->willReturn('PriceInclTax');
        $this->rateMock
            ->expects($this->once())
            ->method('getCode')
            ->willReturn('shipping_method');
        $this->rateMock
            ->expects($this->once())
            ->method('getCarrier')
            ->willReturn('shipping_carrier');
        $this->rateMock
            ->expects($this->once())
            ->method('getMethod')
            ->willReturn('shipping_carrier');
        $this->addressMock
            ->method('getAllShippingRates')
            ->willReturn([$this->rateMock]);
        $this->addressMock
            ->method('getShippingMethod')
            ->willReturn('shipping_method');
        $this->addressMock
            ->method('getShippingAmount')
            ->willReturn('shipping_amount');
        $this->addressMock
            ->expects($this->once())
            ->method('getQuote')
            ->willReturn($this->quoteMock);
        $this->shippingMethodConverterMock->method('modelToDataObject')
            ->willReturn($this->quoteMock);
        $this->selectedShippingMethod->resolve(
            $this->fieldMock,
            $this->contextMock,
            $this->resolveInfoMock,
            $this->valueMock
        );
    }
}
