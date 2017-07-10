<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Locale\Test\Unit;

class FormatTest extends \PHPUnit_Framework_TestCase
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

    protected function setUp()
    {
        $this->currency = $this->getMockBuilder(\Magento\Directory\Model\Currency::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scope = $this->getMockBuilder(\Magento\Framework\App\ScopeInterface::class)
            ->setMethods(['getCurrentCurrency'])
            ->getMockForAbstractClass();
        $this->scope->expects($this->once())
            ->method('getCurrentCurrency')
            ->willReturn($this->currency);
        $this->scopeResolver = $this->getMockBuilder(\Magento\Framework\App\ScopeResolverInterface::class)
            ->setMethods(['getScope'])
            ->getMockForAbstractClass();
        $this->scopeResolver->expects($this->any())
            ->method('getScope')
            ->willReturn($this->scope);
        $this->localeResolver = $this->getMockBuilder(\Magento\Framework\Locale\ResolverInterface::class)
            ->getMock();
        $currencyFactory = $this->getMockBuilder(\Magento\Directory\Model\CurrencyFactory::class)
            ->getMock();

        $this->formatModel = new \Magento\Framework\Locale\Format(
            $this->scopeResolver,
            $this->localeResolver,
            $currencyFactory
        );
    }

    /**
     * @param $localeCode
     * @param $expectedResult
     * @dataProvider getPriceFormatDataProvider
     */
    public function testGetPriceFormat($localeCode, $expectedResult)
    {
        $result = $this->formatModel->getPriceFormat($localeCode);
        $intersection = array_intersect_assoc($result, $expectedResult);
        $this->assertCount(count($expectedResult), $intersection);
    }

    /**
     * @return array
     */
    public function getPriceFormatDataProvider()
    {
        return [
            ['en_US', ['decimalSymbol' => '.', 'groupSymbol' => ',']],
            ['de_DE', ['decimalSymbol' => ',', 'groupSymbol' => '.']],
            ['de_CH', ['decimalSymbol' => '.', 'groupSymbol' => '\'']],
            ['uk_UA', ['decimalSymbol' => ',', 'groupSymbol' => ' ']]
        ];
    }
}
