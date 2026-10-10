<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Locale\Test\Unit;

use Magento\Directory\Model\Currency;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\App\ScopeInterface;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\Locale\Format;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Tests class for Number locale format
 */
class FormatTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var Format
     */
    protected $formatModel;

    /**
     * @var MockObject|ResolverInterface
     */
    protected $localeResolver;

    /**
     * @var MockObject|ScopeInterface
     */
    protected $scope;

    /**
     * @var MockObject|ScopeResolverInterface
     */
    protected $scopeResolver;

    /**
     * @var MockObject|Currency
     */
    protected $currency;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->currency = $this->getMockBuilder(Currency::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scope = $this->createPartialMockWithReflection(
            ScopeInterface::class,
            [
                'getCurrentCurrency',  // Custom method not in interface
                'getCode',            // ScopeInterface methods
                'getId',
                'getScopeType',
                'getScopeTypeName',
                'getName'
            ]
        );

        $this->scopeResolver = $this->createMock(ScopeResolverInterface::class);
        $this->scopeResolver->expects($this->any())
            ->method('getScope')
            ->willReturn($this->scope);
        $this->localeResolver = $this->getMockBuilder(ResolverInterface::class)
            ->getMock();

        /** @var CurrencyFactory|MockObject $currencyFactory */
        $currencyFactory = $this->getMockBuilder(CurrencyFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->formatModel = new Format(
            $this->scopeResolver,
            $this->localeResolver,
            $currencyFactory
        );
    }

    /**
     * @param string $localeCode
     * @param string $currencyCode
     * @param array $expectedResult     */
    #[DataProvider('getPriceFormatDataProvider')]
    public function testGetPriceFormat($localeCode, $currencyCode, array $expectedResult): void
    {
        $this->scope->expects($this->once())
            ->method('getCurrentCurrency')
            ->willReturn($this->currency);

        $this->currency->method('getCode')->willReturn($currencyCode);
        $result = $this->formatModel->getPriceFormat($localeCode);
        $intersection = array_intersect_assoc($result, $expectedResult);
        $this->assertCount(count($expectedResult), $intersection);
    }

    /**
     *
     * @return array
     */
    public static function getPriceFormatDataProvider(): array
    {
        $swissGroupSymbol = INTL_ICU_VERSION >= 59.1 ? '’' : '\'';
        return [
            ['en_US', 'USD', ['decimalSymbol' => '.', 'groupSymbol' => ',']],
            ['de_DE', 'EUR', ['decimalSymbol' => ',', 'groupSymbol' => '.']],
            ['de_CH', 'CHF', ['decimalSymbol' => '.', 'groupSymbol' => $swissGroupSymbol]],
            ['uk_UA', 'UAH', ['decimalSymbol' => ',', 'groupSymbol' => ' ']]
        ];
    }

    /**
     *
     * @param mixed $value
     * @param float $expected
     * @param string $locale     */
    #[DataProvider('provideNumbers')]
    public function testGetNumber(string $value, float $expected, ?string $locale = null): void
    {
        if ($locale !== null) {
            $this->localeResolver->method('getLocale')->willReturn($locale);
        }
        $this->assertEquals($expected, $this->formatModel->getNumber($value));
    }

    /**
     *
     * @return array
     */
    public static function provideNumbers(): array
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
            ['4,000', 4000.0, 'ja_JP'],
            ['4,000', 4.0, 'en_US'],
            ['2٬599٫50', 2599.50, 'ar_EG'],
            ['2٬000٬000٫99', 2000000.99, 'ar_SA'],
        ];
    }
}
