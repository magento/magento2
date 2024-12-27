<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Test\Unit\Model\Resolver;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\Context;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\QuoteGraphQl\Model\Cart\TotalsCollector;
use Magento\QuoteGraphQl\Model\Resolver\CartPrices;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @see CartPrices
 */
class CartPricesTest extends TestCase
{
    /**
     * @var CartPrices
     */
    private CartPrices $cartPrices;

    /**
     * @var TotalsCollector|MockObject
     */
    private TotalsCollector $totalsCollectorMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private ScopeConfigInterface $scopeConfigMock;

    /**
     * @var Field|MockObject
     */
    private Field $fieldMock;

    /**
     * @var ResolveInfo|MockObject
     */
    private ResolveInfo $resolveInfoMock;

    /**
     * @var Context|MockObject
     */
    private Context $contextMock;

    /**
     * @var Quote|MockObject
     */
    private Quote $quoteMock;

    /**
     * @var Total|MockObject
     */
    private Total $totalMock;

    /**
     * @var array
     */
    private array $valueMock = [];

    protected function setUp(): void
    {
        $this->totalsCollectorMock = $this->createMock(TotalsCollector::class);
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->fieldMock = $this->createMock(Field::class);
        $this->resolveInfoMock = $this->createMock(ResolveInfo::class);
        $this->contextMock = $this->createMock(Context::class);
        $this->quoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->addMethods(['getQuoteCurrencyCode'])
            ->getMock();
        $this->totalMock = $this->getMockBuilder(Total::class)
            ->disableOriginalConstructor()
            ->addMethods(
                [
                    'getSubtotal',
                    'getSubtotalInclTax',
                    'getGrandTotal',
                    'getDiscountTaxCompensationAmount',
                    'getDiscountAmount',
                    'getDiscountDescription',
                    'getAppliedTaxes'
                ]
            )
            ->getMock();
        $this->cartPrices = new CartPrices(
            $this->totalsCollectorMock,
            $this->scopeConfigMock
        );
    }

    public function testResolveWithoutModelInValueParameter(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('"model" value should be specified');
        $this->cartPrices->resolve($this->fieldMock, $this->contextMock, $this->resolveInfoMock, $this->valueMock);
    }

    public function testResolve(): void
    {
        $this->valueMock = ['model' => $this->quoteMock];
        $this->quoteMock
            ->expects($this->once())
            ->method('getQuoteCurrencyCode')
            ->willReturn('USD');
        $this->totalMock
            ->expects($this->once())
            ->method('getGrandTotal');
        $this->totalMock
            ->expects($this->exactly(2))
            ->method('getSubtotal');
        $this->totalMock
            ->expects($this->once())
            ->method('getSubtotalInclTax');
        $this->totalMock
            ->method('getDiscountDescription')
            ->willReturn('Discount Description');
        $this->totalMock
            ->expects($this->once())
            ->method('getAppliedTaxes');
        $this->scopeConfigMock
            ->expects($this->once())
            ->method('getValue')
            ->willReturn(1);
        $this->totalMock
            ->expects($this->atLeast(2))
            ->method('getDiscountAmount');
        $this->totalMock
            ->expects($this->once())
            ->method('getDiscountTaxCompensationAmount');
        $this->totalsCollectorMock
            ->method('collectQuoteTotals')
            ->willReturn($this->totalMock);
        $this->cartPrices->resolve($this->fieldMock, $this->contextMock, $this->resolveInfoMock, $this->valueMock);
    }
}
