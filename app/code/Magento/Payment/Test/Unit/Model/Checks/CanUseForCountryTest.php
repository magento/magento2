<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Payment\Test\Unit\Model\Checks;

use Magento\Payment\Model\Checks\CanUseForCountry;
use Magento\Payment\Model\Checks\CanUseForCountry\CountryProvider;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CanUseForCountryTest extends TestCase
{
    private const EXPECTED_COUNTRY_ID = 1;

    /**
     * @var MockObject
     */
    protected $countryProvider;

    /**
     * @var CanUseForCountry
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->countryProvider = $this->createMock(
            CountryProvider::class
        );
        $this->_model = new CanUseForCountry($this->countryProvider);
    }

    /**
     * @param bool $expectation
     */
    #[DataProvider('paymentMethodDataProvider')]
    public function testIsApplicable($expectation)
    {
        $quoteMock = $this->createMock(Quote::class);

        $paymentMethod = $this->createMock(MethodInterface::class);
        $paymentMethod->expects($this->once())->method('canUseForCountry')->with(
            self::EXPECTED_COUNTRY_ID
        )->willReturn($expectation);
        $this->countryProvider->expects($this->once())->method('getCountry')->willReturn(self::EXPECTED_COUNTRY_ID);

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
