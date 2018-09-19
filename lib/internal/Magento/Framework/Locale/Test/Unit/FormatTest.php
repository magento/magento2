<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Locale\Test\Unit;

class FormatTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Locale\Format
     */
    protected $formatModel;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Locale\ResolverInterface
     */
    protected $localeResolver;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\ScopeInterface
     */
    protected $scope;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\ScopeResolverInterface
     */
    protected $scopeResolver;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Directory\Model\Currency
     */
    protected $currency;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->currency = $this->getMockBuilder(\Magento\Directory\Model\Currency::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scope = $this->getMockBuilder(\Magento\Framework\App\ScopeInterface::class)
            ->setMethods(['getCurrentCurrency'])
            ->getMockForAbstractClass();

        $this->scopeResolver = $this->getMockBuilder(\Magento\Framework\App\ScopeResolverInterface::class)
            ->setMethods(['getScope'])
            ->getMockForAbstractClass();
        $this->scopeResolver->expects($this->any())
            ->method('getScope')
            ->willReturn($this->scope);
        $this->localeResolver = $this->getMockBuilder(\Magento\Framework\Locale\ResolverInterface::class)
            ->getMock();

        /** @var \Magento\Directory\Model\CurrencyFactory|\PHPUnit_Framework_MockObject_MockObject $currencyFactory */
        $currencyFactory = $this->getMockBuilder(\Magento\Directory\Model\CurrencyFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->formatModel = new \Magento\Framework\Locale\Format(
            $this->scopeResolver,
            $this->localeResolver,
            $currencyFactory
        );
    }

    /**
     * @param string $localeCode
     * @param array $expectedResult
     * @dataProvider getPriceFormatDataProvider
     */
    public function testGetPriceFormat($localeCode, array $expectedResult): void
    {
        $this->scope->expects($this->once())
            ->method('getCurrentCurrency')
            ->willReturn($this->currency);

        $result = $this->formatModel->getPriceFormat($localeCode);
        $intersection = array_intersect_assoc($result, $expectedResult);
        $this->assertCount(count($expectedResult), $intersection);
    }

    /**
     *
     * @return array
     */
    public function getPriceFormatDataProvider(): array
    {
        return [
            ['en_US', ['decimalSymbol' => '.', 'groupSymbol' => ',']],
            ['de_DE', ['decimalSymbol' => ',', 'groupSymbol' => '.']],
            ['de_CH', ['decimalSymbol' => '.', 'groupSymbol' => '\'']],
            ['uk_UA', ['decimalSymbol' => ',', 'groupSymbol' => ' ']]
        ];
    }

    /**
     *
     * @param        mixed $value
     * @param        float $expected
     * @dataProvider provideNumbers
     */
    public function testGetNumber($value, $expected): void
    {
        $this->assertEquals($expected, $this->formatModel->getNumber($value));
    }

    /**
     *
     * @return array
     */
    public function provideNumbers(): array
    {
        return [
            ['  2345.4356,1234', 23454356.1234],
            ['+23,3452.123', 233452.123],
            ['12343', 12343],
            ['-9456km', -9456],
            ['0', 0],
            ['2 054,10', 2054.1],
            ['2046,45', 2046.45],
            ['2 054.52', 2054.52],
            ['2,46 GB', 2.46],
            ['2,054.00', 2054],
        ];
    }
}
