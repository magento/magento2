<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Directory\Test\Unit\Model;

use Magento\Directory\Model\Currency;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\Locale\ResolverInterface as LocalResolverInterface;
use Magento\Framework\NumberFormatterFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CurrencyTest extends TestCase
{
    /**
     * @var Currency
     */
    protected $currency;

    protected $currencyCode = 'USD';

    /**
     * @var CurrencyInterface|MockObject
     */
    protected $localeCurrencyMock;

    /**
     * @var LocalResolverInterface
     */
    private $localeResolver;

    /**
     * @var NumberFormatterFactory
     */
    private $numberFormatterFactory;

    /**
     * @var Json
     */
    private $serializer;

    protected function setUp(): void
    {
        $this->localeCurrencyMock = $this->getMockForAbstractClass(CurrencyInterface::class);
        $currencyFilterFactory = $this->getMockBuilder(\Magento\Directory\Model\Currency\FilterFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->localeResolver = $this->getMockBuilder(LocalResolverInterface::class)
            ->getMockForAbstractClass();
        $this->numberFormatterFactory = $this->getMockBuilder(NumberFormatterFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->serializer = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->currency = $objectManager->getObject(
            Currency::class,
            [
                'localeCurrency' => $this->localeCurrencyMock,
                'currencyFilterFactory' => $currencyFilterFactory,
                'localeResolver' => $this->localeResolver,
                'numberFormatterFactory' => $this->numberFormatterFactory,
                'serializer' => $this->serializer,
                'data' => [
                    'currency_code' => $this->currencyCode,
                ]
            ]
        );
    }

    public function testGetCurrencySymbol(): void
    {
        $currencySymbol = '$';

        $currencyMock = $this->getMockBuilder(\Magento\Framework\Currency::class)
            ->disableOriginalConstructor()
            ->getMock();
        $currencyMock->expects(self::once())
            ->method('getSymbol')
            ->willReturn($currencySymbol);

        $this->localeCurrencyMock->expects(self::once())
            ->method('getCurrency')
            ->with($this->currencyCode)
            ->willReturn($currencyMock);
        self::assertEquals($currencySymbol, $this->currency->getCurrencySymbol());
    }

    /**
     * @dataProvider getOutputFormatDataProvider
     * @param $expected
     * @param $locale
     */
    public function testGetOutputFormat($expected, $locale): void
    {
        $this->localeResolver->method('getLocale')->willReturn($locale);
        $this->numberFormatterFactory
            ->method('create')
            ->with(['locale' => $locale, 'style' => 2])
            ->willReturn(new \Magento\Framework\NumberFormatter($locale, 2));
        $this->serializer->method('serialize')->willReturnMap(
            [
                [[], '[]'],
                [['display' => 1], '{"display":1}']
            ]
        );
        self::assertEquals($expected, $this->currency->getOutputFormat());
    }

    /**
     * Return data sets for testGetOutputFormat()
     *
     * @return array
     */
    public function getOutputFormatDataProvider(): array
    {
        return [
            'no_unicode' => [
                'expected' => '$%s',
                'locale' => 'en_US'
            ],
            'arabic_unicode' => [
                'expected' => json_decode('"\u200E"') . '$%s',
                'locale' => 'fa_IR'
            ]
        ];
    }

    /**
     * @dataProvider getFormatTxtNumberFormatterDataProvider
     * @param string $price
     * @param array $options
     * @param string $locale
     * @param string $expected
     */
    public function testFormatTxtWithNumberFormatter(
        string $price,
        array $options,
        string $locale,
        string $expected
    ): void {
        $this->localeResolver->expects(self::exactly(2))->method('getLocale')->willReturn($locale);
        $this->numberFormatterFactory
            ->expects(self::once())
            ->method('create')
            ->with(['locale' => $locale, 'style' => 2])
            ->willReturn(new \Magento\Framework\NumberFormatter($locale, 2));
        $this->serializer->method('serialize')->willReturnMap(
            [
                [[], '[]']
            ]
        );

        self::assertEquals($expected, $this->currency->formatTxt($price, $options));
    }

    /**
     * Return data sets for testFormatTxtWithNumberFormatter()
     *
     * @return array
     */
    public function getFormatTxtNumberFormatterDataProvider(): array
    {
        return [
            ['9999', [], 'en_US', '$9,999.00'],
            ['9999', ['display' => \Magento\Framework\Currency::NO_SYMBOL, 'precision' => 2], 'en_US', '9,999.00'],
            ['9999', ['display' => \Magento\Framework\Currency::NO_SYMBOL], 'en_US', '9,999.00'],
            [' 9999', ['display' => \Magento\Framework\Currency::NO_SYMBOL], 'en_US', '9,999.00'],
            ['9999', ['precision' => 1], 'en_US', '$9,999.0'],
            ['9999', ['precision' => 2, 'symbol' => '#'], 'en_US', '#9,999.00'],
            [
                '9999.99',
                ['precision' => 2, 'symbol' => '#', 'display' => \Magento\Framework\Currency::NO_SYMBOL],
                'en_US',
                '9,999.99'
            ],
        ];
    }

    /**
     * @dataProvider getFormatTxtZendCurrencyDataProvider
     * @param string $price
     * @param array $options
     * @param string $expected
     * @throws \Zend_Currency_Exception
     */
    public function testFormatTxtWithZendCurrency(string $price, array $options, string $expected): void
    {
        $this->localeCurrencyMock
            ->expects(self::once())
            ->method('getCurrency')
            ->with($this->currencyCode)
            ->willReturn(new \Zend_Currency($options, 'en_US'));
        $this->serializer->method('serialize')->willReturnMap(
            [
                [[], '[]']
            ]
        );

        self::assertEquals($expected, $this->currency->formatTxt($price, $options));
    }

    /**
     * Return data sets for testFormatTxtWithZendCurrency()
     *
     * @return array
     */
    public function getFormatTxtZendCurrencyDataProvider(): array
    {
        return [
            ['9999', ['display' => \Magento\Framework\Currency::USE_SYMBOL, 'foo' => 'bar'], '$9,999.00'],
            ['9999', ['display' => \Magento\Framework\Currency::USE_SHORTNAME, 'foo' => 'bar'], 'USD9,999.00'],
            ['9999', ['currency' => 'USD'], '$9,999.00'],
            ['9999', ['currency' => 'CNY'], 'CN¥9,999.00'],
            ['9999', ['locale' => 'fr_FR'], '9 999,00 $']
        ];
    }
}
