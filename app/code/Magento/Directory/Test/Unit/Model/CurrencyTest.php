<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Directory\Test\Unit\Model;

use Magento\Directory\Model\Currency as CurrencyModel;
use Magento\Framework\Currency;
use Magento\Framework\Currency\Data\Currency as CurrencyData;
use Magento\Framework\Currency\Exception\CurrencyException;
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
     * @var CurrencyModel
     */
    protected $currency;

    /**
     * @var string
     */
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
            ->onlyMethods(['create'])
            ->getMock();
        $this->serializer = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->currency = $objectManager->getObject(
            CurrencyModel::class,
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

        $currencyMock = $this->getMockBuilder(Currency::class)
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
     * @param string $locale
     * @param string $currency
     * @param string $expected
     */
    public function testGetOutputFormat(string $locale, string $currency, string $expected): void
    {
        $this->localeResolver->expects(self::atLeastOnce())
            ->method('getLocale')
            ->willReturn($locale);
        $this->numberFormatterFactory
            ->expects(self::exactly(2))
            ->method('create')
            ->willReturnCallback(
                function (array $args) {
                    return new \Magento\Framework\NumberFormatter($args['locale'], $args['style']);
                }
            );
        $this->serializer->method('serialize')->willReturnMap(
            [
                [[], '[]'],
                [['display' => 1], '{"display":1}']
            ]
        );

        $this->currency->load($currency);
        self::assertEquals($expected, $this->currency->getOutputFormat());
    }

    /**
     * Return data sets for testGetOutputFormat()
     *
     * @return array
     */
    public static function getOutputFormatDataProvider(): array
    {
        // Use dynamic detection for problematic locale/currency combinations!
        $ar_DZ_EGP = self::getExpectedFormatForLocale('ar_DZ', 'EGP');

        return [
            'en_US:USD' => ['en_US', 'USD', '$%s'],
            'en_US:PLN' => ['en_US', 'PLN', "PLN\u{00A0}%s"],
            'en_US:PKR' => ['en_US', 'PKR', "PKR\u{00A0}%s"],
            'af_ZA:VND' => ['af_ZA', 'VND', "\u{20AB}%s"],
            'ar_DZ:EGP' => ['ar_DZ', 'EGP', $ar_DZ_EGP],
            'ar_SA:USD' => ['ar_SA', 'USD', "%s\u{00A0}US$"],
            'ar_SA:LBP' => ['ar_SA', 'LBP', "%s\u{00A0}\u{0644}.\u{0644}.\u{200F}"],
            'fa_IR:USD' => ['fa_IR', 'USD', "\u{200E}$%s"],
            'ar_KW:USD' => ['ar_KW', 'USD', "%s\u{00A0}US$"],
            'bn_BD:IQD' => ['bn_BD', 'IQD', "%s\u{00A0}IQD"],
            'ca_ES:VND' => ['ca_ES', 'VND', "%s\u{00A0}\u{20AB}"],
            'de_DE:USD' => ['de_DE', 'USD', "%s\u{00A0}$"],
            'de_DE:AED' => ['de_DE', 'AED', "%s\u{00A0}AED"],
            'es_VE:VEF' => ['es_VE', 'VEF', "Bs.\u{00A0}%s"],
            'pl_PL:USD' => ['pl_PL', 'USD', "%s\u{00A0}USD"],
            'pl_PL:PLN' => ['pl_PL', 'PLN', "%s\u{00A0}z\u{0142}"],
        ];
    }

    /**
     * Get expected format for a specific locale/currency combination
     * This handles cases where intl extension version affects formatting
     *
     * @param string $locale
     * @param string $currency
     * @return string
     */
    private static function getExpectedFormatForLocale(string $locale, string $currency): string
    {
        // Define known problematic combinations and their expected formats
        $problematicFormats = [
            'ar_DZ:EGP' => [
                'old' => "\u{062C}.\u{0645}.\u{200F}\u{00A0}%s",
                'new' => "%s\u{00A0}\u{062C}.\u{0645}.\u{200F}"
            ]
        ];

        $key = $locale . ':' . $currency;

        if (isset($problematicFormats[$key])) {
            // Check if we're using a newer intl version that changes formatting
            if (self::isNewerIntlVersion()) {
                return $problematicFormats[$key]['new'];
            }
            return $problematicFormats[$key]['old'];
        }

        // For non-problematic combinations, return a default format
        // This could be enhanced with more specific formats as needed
        return "%s";
    }

    /**
     * Check if the current intl extension version uses newer formatting rules
     *
     * @return bool
     */
    private static function isNewerIntlVersion(): bool
    {
        // Check intl extension version
        if (extension_loaded('intl')) {
            $intlVersion = INTL_ICU_VERSION ?? '0.0.0';

            // ICU 72+ (released around 2022) introduced changes to RTL formatting
            // This is a more reliable indicator than PHP version
            if (version_compare($intlVersion, '72.0', '>=')) {
                return true;
            }
        }

        // Fallback: Check PHP version as a rough indicator
        // This is less reliable but provides some backward compatibility
        return version_compare(PHP_VERSION, '8.3', '>=');
    }

    /**
     * @dataProvider getFormatTxtNumberFormatterDataProvider
     * @param string $locale
     * @param string $currency
     * @param string $price
     * @param array $options
     * @param string $expected
     */
    public function testFormatTxtWithNumberFormatter(
        string $locale,
        string $currency,
        string $price,
        array $options,
        string $expected
    ): void {
        $this->localeResolver->expects(self::once())
            ->method('getLocale')
            ->willReturn($locale);
        $this->numberFormatterFactory
            ->expects(self::once())
            ->method('create')
            ->willReturnCallback(
                function (array $args) {
                    return new \Magento\Framework\NumberFormatter($args['locale'], $args['style']);
                }
            );
        $this->serializer->method('serialize')->willReturnMap(
            [
                [[], '[]']
            ]
        );

        $this->currency->load($currency);
        self::assertEquals($expected, $this->currency->formatTxt($price, $options));
    }

    /**
     * Return data sets for testFormatTxtWithNumberFormatter()
     *
     * @return array
     */
    public static function getFormatTxtNumberFormatterDataProvider(): array
    {
        return [
            ['en_US', 'USD', '9999', [], '$9,999.00'],
            ['en_US', 'EUR', '9999', [], '€9,999.00'],
            ['en_US', 'LBP', '9999', [], "LBP\u{00A0}9,999"],
            ['ar_SA', 'USD', '9', [], "\u{0669}\u{066B}\u{0660}\u{0660}\u{00A0}US$"],
            ['ar_SA', 'AED', '9', [], "\u{0669}\u{066B}\u{0660}\u{0660}\u{00A0}\u{062F}.\u{0625}.\u{200F}"],
            ['de_DE', 'USD', '9999', [], "9.999,00\u{00A0}$"],
            ['de_DE', 'EUR', '9999', [], "9.999,00\u{00A0}€"],
            ['en_US', 'USD', '9999', ['display' => CurrencyData::NO_SYMBOL, 'precision' => 2], '9,999.00'],
            ['en_US', 'USD', '9999', ['display' => CurrencyData::NO_SYMBOL], '9,999.00'],
            ['en_US', 'PLN', '9999', ['display' => CurrencyData::NO_SYMBOL], '9,999.00'],
            ['en_US', 'LBP', '9999', ['display' => CurrencyData::NO_SYMBOL], '9,999'],
            [
                'ar_SA',
                'USD',
                '9999',
                ['display' => CurrencyData::NO_SYMBOL],
                "\u{0669}\u{066C}\u{0669}\u{0669}\u{0669}\u{066B}\u{0660}\u{0660}"
            ],
            [
                'ar_SA',
                'AED',
                '9999',
                ['display' => CurrencyData::NO_SYMBOL],
                "\u{0669}\u{066C}\u{0669}\u{0669}\u{0669}\u{066B}\u{0660}\u{0660}"
            ],
            ['en_US', 'USD', ' 9999', ['display' => CurrencyData::NO_SYMBOL], '9,999.00'],
            ['en_US', 'USD', '9999', ['precision' => 1], '$9,999.0'],
            ['en_US', 'USD', '9999', ['precision' => 2, 'symbol' => '#'], '# 9,999.00'],
            [
                'en_US',
                'USD',
                '9999.99',
                ['precision' => 2, 'symbol' => '#', 'display' => CurrencyData::NO_SYMBOL],
                '9,999.99'
            ],
            ['he_IL', 'USD', '9999', [], '9,999.00 ‏$'],
            ['he_IL', 'USD', '9999', ['display' => CurrencyData::NO_SYMBOL], '9,999.00'],
        ];
    }

    /**
     * @dataProvider getFormatTxtZendCurrencyDataProvider
     * @param string $price
     * @param array $options
     * @param string $expected
     * @throws CurrencyException
     */
    public function testFormatTxtWithZendCurrency(string $price, array $options, string $expected): void
    {
        $this->localeCurrencyMock
            ->expects(self::once())
            ->method('getCurrency')
            ->with($this->currencyCode)
            ->willReturn(new CurrencyData($options, 'en_US'));
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
    public static function getFormatTxtZendCurrencyDataProvider(): array
    {
        return [
            ['9999', ['display' => Currency::USE_SYMBOL, 'foo' => 'bar'], '$9,999.00'],
            ['9999', ['display' => Currency::USE_SHORTNAME, 'foo' => 'bar'], 'USD9,999.00'],
            ['9999', ['currency' => 'USD'], '$9,999.00'],
            ['9999', ['currency' => 'CNY'], 'CN¥9,999.00'],
            ['9999', ['locale' => 'fr_FR'], "9\u{202F}999,00\u{00A0}$"]
        ];
    }
}
