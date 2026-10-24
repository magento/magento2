<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Locale\Test\Unit;

use Magento\Framework\Currency\Data\Currency as CurrencyData;
use Magento\Framework\CurrencyFactory;
use Magento\Framework\Event\Manager;
use Magento\Framework\Locale\Currency;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CurrencyTest extends TestCase
{
    /**
     * @var Manager|MockObject
     */
    private $mockEventManager;

    /**
     * @var Resolver|MockObject
     */
    private $mockLocaleResolver;

    /**
     * @var CurrencyFactory|MockObject
     */
    private $mockCurrencyFactory;

    /**
     * @var Currency
     */
    private $testCurrencyObject;

    public const TEST_NONCACHED_CURRENCY = 'USD';
    public const TEST_NONCACHED_CURRENCY_LOCALE = 'en_US';
    public const TEST_CACHED_CURRENCY = 'CAD';
    public const TEST_CACHED_CURRENCY_LOCALE = 'en_CA';
    public const TEST_NONEXISTENT_CURRENCY = 'QQQ';
    public const TEST_NONEXISTENT_CURRENCY_LOCALE = 'fr_FR';
    public const TEST_EXCEPTION_CURRENCY = 'ZZZ';
    public const TEST_EXCEPTION_CURRENCY_LOCALE = 'es_ES';

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        (new \ReflectionProperty(Currency::class, '_currencyCache'))->setValue(null, []);
        $this->mockEventManager = $this
            ->getMockBuilder(Manager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockLocaleResolver = $this
            ->getMockBuilder(Resolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockCurrencyFactory = $this
            ->getMockBuilder(CurrencyFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->testCurrencyObject = (new ObjectManager($this))
            ->getObject(
                Currency::class,
                [
                    'eventManager'     => $this->mockEventManager,
                    'localeResolver'   => $this->mockLocaleResolver,
                    'currencyFactory'  => $this->mockCurrencyFactory
                ]
            );
    }

    /**
     * @return void
     */
    public function testGetDefaultCurrency(): void
    {
        $expectedDefaultCurrency = Currency::DEFAULT_CURRENCY;
        $retrievedDefaultCurrency = $this->testCurrencyObject->getDefaultCurrency();
        $this->assertEquals($expectedDefaultCurrency, $retrievedDefaultCurrency);
    }

    /**
     * @return void
     */
    public function testGetCurrencyNonCached(): void
    {
        $options = new CurrencyData(null, self::TEST_NONCACHED_CURRENCY_LOCALE);

        $this->mockCurrencyFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($options);

        $this->mockEventManager
            ->expects($this->once())
            ->method('dispatch');

        $retrievedCurrencyObject = $this->testCurrencyObject
            ->getCurrency(self::TEST_NONCACHED_CURRENCY);

        $this->assertInstanceOf(CurrencyData::class, $retrievedCurrencyObject);
        $this->assertEquals(self::TEST_NONCACHED_CURRENCY_LOCALE, $retrievedCurrencyObject->getLocale());
        $this->assertEquals('US Dollar', $retrievedCurrencyObject->getName());
        $this->assertEquals([self::TEST_NONCACHED_CURRENCY], $retrievedCurrencyObject->getCurrencyList());
    }

    /**
     * @return void
     */
    public function testGetCurrencyCached(): void
    {
        $options = new CurrencyData(null, self::TEST_CACHED_CURRENCY_LOCALE);

        $this->mockCurrencyFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($options);

        $this->mockEventManager
            ->expects($this->once())
            ->method('dispatch');

        $retrievedCurrencyObject = $this->testCurrencyObject
            ->getCurrency(self::TEST_CACHED_CURRENCY);

        $this->assertInstanceOf(CurrencyData::class, $retrievedCurrencyObject);
        $this->assertEquals(self::TEST_CACHED_CURRENCY_LOCALE, $retrievedCurrencyObject->getLocale());
        $this->assertEquals('Canadian Dollar', $retrievedCurrencyObject->getName());
        $this->assertEquals([self::TEST_CACHED_CURRENCY], $retrievedCurrencyObject->getCurrencyList());

        /*
         * Since the CAD currency object was previously retrieved, getCurrency()
         * should return the previously created and cached currency object for CAD, rather
         * than creating another one.
         */

        $this->mockCurrencyFactory
            ->expects($this->never())
            ->method('create')
            ->willReturn($options);

        $this->mockEventManager
            ->expects($this->never())
            ->method('dispatch');

        $retrievedCurrencyObject = $this->testCurrencyObject
            ->getCurrency(self::TEST_CACHED_CURRENCY);

        $this->assertInstanceOf(CurrencyData::class, $retrievedCurrencyObject);
        $this->assertEquals(self::TEST_CACHED_CURRENCY_LOCALE, $retrievedCurrencyObject->getLocale());
        $this->assertEquals('Canadian Dollar', $retrievedCurrencyObject->getName());
        $this->assertEquals([self::TEST_CACHED_CURRENCY], $retrievedCurrencyObject->getCurrencyList());
    }

    /**
     * @return void
     */
    public function testGetNonExistentCurrency(): void
    {
        $options = new CurrencyData(null, self::TEST_NONEXISTENT_CURRENCY_LOCALE);

        $this->mockCurrencyFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($options);

        $this->mockEventManager
            ->expects($this->once())
            ->method('dispatch');

        $this->mockEventManager
            ->expects($this->once())
            ->method('dispatch');

        $retrievedCurrencyObject = $this->testCurrencyObject
            ->getCurrency(self::TEST_NONEXISTENT_CURRENCY);

        $this->assertInstanceOf(CurrencyData::class, $retrievedCurrencyObject);
        $this->assertEquals(self::TEST_NONEXISTENT_CURRENCY_LOCALE, $retrievedCurrencyObject->getLocale());
        $this->assertEquals('euro', $retrievedCurrencyObject->getName());
        $this->assertEquals(['EUR'], $retrievedCurrencyObject->getCurrencyList());
    }

    /**
     * @return void
     */
    public function testExceptionCase(): void
    {
        $options = new CurrencyData(null, self::TEST_EXCEPTION_CURRENCY_LOCALE);

        $this->mockCurrencyFactory
            ->method('create')
            ->willReturnOnConsecutiveCalls(
                $this->throwException(new \Exception()),
                $options
            );

        $this->mockEventManager
            ->expects($this->once())
            ->method('dispatch');

        $this->mockLocaleResolver
            ->expects($this->exactly(5))
            ->method('getLocale');

        $retrievedCurrencyObject = $this->testCurrencyObject
            ->getCurrency(self::TEST_EXCEPTION_CURRENCY);

        $this->assertInstanceOf(CurrencyData::class, $retrievedCurrencyObject);
        $this->assertEquals(self::TEST_EXCEPTION_CURRENCY_LOCALE, $retrievedCurrencyObject->getLocale());
        $this->assertEquals(self::TEST_EXCEPTION_CURRENCY, $retrievedCurrencyObject->getName());
        $this->assertEquals(['EUR'], $retrievedCurrencyObject->getCurrencyList());
    }

    /**
     * @param string $code
     * @param string $locale
     * @param string $expected
     * @return void
     */
    #[DataProvider('currencyPrecisionProvider')]
    public function testCurrencyPrecision(string $code, string $locale, string $expected): void
    {
        $currency = new CurrencyData(null, 'en_US');
        $this->mockLocaleResolver->method('getLocale')->willReturn($locale);
        $this->mockCurrencyFactory->method('create')->willReturn($currency);

        $result = $this->testCurrencyObject->getCurrency($code);

        self::assertSame($expected, $result->toCurrency(12.3456, ['display' => CurrencyData::NO_SYMBOL]));
        self::assertSame(
            '12.35',
            $result->toCurrency(12.3456, ['precision' => 2, 'display' => CurrencyData::NO_SYMBOL])
        );
    }

    /**
     * @return array
     */
    public static function currencyPrecisionProvider(): array
    {
        return [
            'two decimals' => ['USD', 'en_US', '12.35'],
            'zero decimals in another locale' => ['JPY', 'en_US', '12'],
            'three decimals' => ['BHD', 'en_US', '12.346'],
            'three decimals in Japan' => ['KWD', 'ja_JP', '12.346'],
            'empty locale' => ['JPY', '', '12'],
            'unusual locale' => ['JPY', 'zz_ZZ', '12'],
        ];
    }

    /**
     * @return void
     */
    public function testPrecisionObserverOverride(): void
    {
        $currency = new CurrencyData(null, 'en_US');
        $this->mockLocaleResolver->method('getLocale')->willReturn('en_US');
        $this->mockCurrencyFactory->method('create')->willReturn($currency);
        $this->mockEventManager->expects(self::once())->method('dispatch')->willReturnCallback(
            static function ($event, $data): void {
                self::assertSame('currency_display_options_forming', $event);
                self::assertSame(0, $data['currency_options']->getData('precision'));
                $data['currency_options']->setData('precision', 3);
            }
        );

        $result = $this->testCurrencyObject->getCurrency('JPY');

        self::assertSame('12.346', $result->toCurrency(12.3456, ['display' => CurrencyData::NO_SYMBOL]));
        self::assertSame(
            '12.35',
            $result->toCurrency(12.3456, ['precision' => 2, 'display' => CurrencyData::NO_SYMBOL])
        );
    }

    /**
     * @return void
     */
    public function testInvalidCurrencyFallback(): void
    {
        $currency = new CurrencyData(null, 'en_US');
        $this->mockLocaleResolver->method('getLocale')->willReturn('en_US');
        $this->mockCurrencyFactory->expects(self::exactly(2))->method('create')->willReturnCallback(
            static function (array $arguments) use ($currency) {
                return match ($arguments['options']) {
                    'INVALID' => throw new \Exception('Invalid currency'),
                    'USD' => $currency,
                };
            }
        );

        $result = $this->testCurrencyObject->getCurrency('INVALID');

        self::assertSame($currency, $result);
        self::assertSame('INVALID', $result->getName());
        self::assertSame('12.35', $result->toCurrency(12.3456, ['display' => CurrencyData::NO_SYMBOL]));
    }

    /**
     * @return void
     */
    public function testInvalidPrecisionLocaleDoesNotPreventCurrencyCreation(): void
    {
        $currency = new CurrencyData(null, 'en_US');
        $this->mockLocaleResolver->method('getLocale')->willReturn(str_repeat('x', 200));
        $this->mockCurrencyFactory->expects(self::once())->method('create')->willReturn($currency);
        $this->mockEventManager->expects(self::once())->method('dispatch');

        self::assertSame($currency, $this->testCurrencyObject->getCurrency('JPY'));
    }
}
