<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Test\Unit\Model\Checks;

use Magento\Payment\Model\Checks\CanUseForCountry;
use Magento\Payment\Model\Checks\CanUseForCountry\CountryProvider;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CanUseForCountryTest extends TestCase
{
    /**
     * Expected country id
     */
    const EXPECTED_COUNTRY_ID = 1;

    /**
     * @var MockObject
     */
    private $countryProviderMock;

    /**
     * @var CanUseForCountry
     */
    private $model;

    protected function setUp()
    {
        $this->countryProviderMock = $this->createMock(CountryProvider::class);
        $this->model = new CanUseForCountry($this->countryProviderMock);
    }

    /**
     * @dataProvider paymentMethodDataProvider
     * @param bool $expectation
     */
    public function testIsApplicable($expectation)
    {
        $quoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $paymentMethod = $this->getMockBuilder(
            MethodInterface::class
        )->disableOriginalConstructor()->setMethods([])->getMock();
        $paymentMethod->expects($this->once())->method('canUseForCountry')->with(
            self::EXPECTED_COUNTRY_ID
        )->will($this->returnValue($expectation));
        $this->countryProviderMock->expects($this->once())->method('getCountry')->willReturn(self::EXPECTED_COUNTRY_ID);

        $this->assertEquals($expectation, $this->model->isApplicable($paymentMethod, $quoteMock));
    }

    /**
     * @return array
     */
    public function paymentMethodDataProvider()
    {
        return [[true], [false]];
    }
}
