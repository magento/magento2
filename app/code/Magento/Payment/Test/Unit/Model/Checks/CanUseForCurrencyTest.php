<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Payment\Test\Unit\Model\Checks;

use Magento\Payment\Model\Checks\CanUseForCurrency;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\Store;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CanUseForCurrencyTest extends TestCase
{
    private const EXPECTED_CURRENCY_CODE = 'US';

    /**
     * @var CanUseForCurrency
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_model = new CanUseForCurrency();
    }

    /**
     * @param bool $expectation
     */
    #[DataProvider('paymentMethodDataProvider')]
    public function testIsApplicable($expectation)
    {
        $paymentMethod = $this->createMock(MethodInterface::class);
        $paymentMethod->expects($this->once())->method('canUseForCurrency')->with(
            self::EXPECTED_CURRENCY_CODE
        )->willReturn($expectation);

        $quoteMock = $this->createMock(Quote::class);
        $store = $this->createMock(Store::class);
        $store->expects($this->once())->method('getBaseCurrencyCode')->willReturn(
            self::EXPECTED_CURRENCY_CODE
        );
        $quoteMock->expects($this->once())->method('getStore')->willReturn($store);

        $this->assertEquals($expectation, $this->_model->isApplicable($paymentMethod, $quoteMock));
    }

    /**
     * @return array
     */
    public static function paymentMethodDataProvider()
    {
        return [[true], [false]];
    }
}
