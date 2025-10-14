<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Payment\Test\Unit\Model\Checks;

use Magento\Payment\Model\Checks\CanUseForCountry;
use Magento\Payment\Model\Checks\CanUseForCountry\CountryProvider;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Model\Quote;
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
     * @dataProvider paymentMethodDataProvider
     * @param bool $expectation
     */
    public function testIsApplicable($expectation)
    {
        $quoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()->getMock();

        $paymentMethod = $this->getMockBuilder(
            MethodInterface::class
        )->disableOriginalConstructor()->getMock();
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
