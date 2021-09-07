<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Locale\Test\Unit;

use Magento\Framework\Locale\ConfigInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Locale\TranslatedLists;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TranslatedListsTest extends TestCase
{
    /**
     * @var TranslatedLists
     */
    private $listsModel;

    /**
     * @var MockObject | ConfigInterface
     */
    private $mockConfig;

    /**
     * @var MockObject | ResolverInterface
     */
    private $mockLocaleResolver;

    /**
     * @var array
     */
    private $expectedCurrencies = [
        'USD',
        'EUR',
        'UAH',
        'GBP',
    ];

    /**
     * @var array
     */
    private $expectedLocales = [
        'en_US',
        'en_GB',
        'uk_UA',
        'de_DE',
        'sr_Cyrl_RS',
        'sr_Latn_RS'
    ];

    /**
     * @var string[]
     */
    private $languages = [
        'en_US' => 'English',
        'en_GB' => 'English',
        'uk_UA' => 'Ukrainian',
        'de_DE' => 'German',
        'sr_Cyrl_RS' => 'Serbian',
        'sr_Latn_RS' => 'Serbian'
    ];

    /**
     * @var string[]
     */
    private $countries = [
        'en_US' => 'United States',
        'en_GB' => 'United Kingdom',
        'uk_UA' => 'Ukraine',
        'de_DE' => 'Germany',
        'sr_Cyrl_RS' => 'Serbia',
        'sr_Latn_RS' => 'Serbia'
    ];

    protected function setUp(): void
    {
        $this->mockConfig = $this->getMockBuilder(ConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->mockConfig->method('getAllowedLocales')
            ->willReturn($this->expectedLocales);
        $this->mockConfig->method('getAllowedCurrencies')
            ->willReturn($this->expectedCurrencies);

        $this->mockLocaleResolver = $this->getMockBuilder(ResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->mockLocaleResolver->expects($this->once())
            ->method('getLocale')
            ->willReturn('en_US');

        $this->listsModel = new TranslatedLists(
            $this->mockConfig,
            $this->mockLocaleResolver
        );
    }

    public function testGetCountryTranslation()
    {
        $this->assertNull($this->listsModel->getCountryTranslation(null));
        $this->assertNull($this->listsModel->getCountryTranslation(null, 'en_US'));
    }

    public function testGetOptionAllCurrencies()
    {
        $expectedResults = ['USD', 'EUR', 'GBP', 'UAH'];

        $currencyList = $this->listsModel->getOptionAllCurrencies();
        foreach ($expectedResults as $value) {
            $found = false;
            foreach ($currencyList as $item) {
                $found = $found || ($value == $item['value']);
            }
            $this->assertTrue($found);
        }
    }

    public function testGetOptionCurrencies()
    {
        $expectedResults = ['USD', 'EUR', 'GBP', 'UAH'];

        $currencyList = $this->listsModel->getOptionCurrencies();
        $currencyCodes = array_map(
            static function ($data) {
                return $data['value'];
            },
            $currencyList
        );
        foreach ($expectedResults as $value) {
            $this->assertContains($value, $currencyCodes);
        }
    }

    public function testGetOptionCountries()
    {
        $expectedResults = ['US', 'GB', 'DE', 'UA'];

        $list = $this->listsModel->getOptionCountries();
        foreach ($expectedResults as $value) {
            $found = false;
            foreach ($list as $item) {
                $found = $found || ($value == $item['value']);
            }
            $this->assertTrue($found);
        }
    }

    public function testGetOptionsWeekdays()
    {
        $expectedArray = [
            ['label' => 'Sunday', 'value' => 'Sun'],
            ['label' => 'Monday', 'value' => 'Mon'],
            ['label' => 'Tuesday', 'value' => 'Tue'],
            ['label' => 'Wednesday', 'value' => 'Wed'],
            ['label' => 'Thursday', 'value' => 'Thu'],
            ['label' => 'Friday', 'value' => 'Fri'],
            ['label' => 'Saturday', 'value' => 'Sat'],
        ];

        $this->assertEquals($expectedArray, $this->listsModel->getOptionWeekdays(true, true));
    }

    public function testGetOptionTimezones()
    {
        $expectedResults = ['Australia/Darwin', 'America/Los_Angeles', 'Asia/Jerusalem'];

        $list = $this->listsModel->getOptionTimezones();
        foreach ($expectedResults as $value) {
            $found = false;
            foreach ($list as $item) {
                $found = $found || ($value == $item['value']);
            }
            $this->assertTrue($found);
        }
    }

    public function testGetOptionLocales()
    {
        $expected = $this->getExpectedLocales();
        $locales = array_intersect(
            $expected,
            $this->convertOptionLocales($this->listsModel->getOptionLocales())
        );
        $this->assertEquals($expected, $locales);
    }

    public function testGetTranslatedOptionLocales()
    {
        $expected = $this->getExpectedTranslatedLocales();
        $locales = array_intersect(
            $expected,
            $this->convertOptionLocales($this->listsModel->getTranslatedOptionLocales())
        );
        $this->assertEquals($expected, $locales);
    }

    /**
     * @param array $optionLocales
     * @return array
     */
    private function convertOptionLocales($optionLocales): array
    {
        $result = [];

        foreach ($optionLocales as $optionLocale) {
            $result[$optionLocale['value']] = $optionLocale['label'];
        }

        return $result;
    }

    /**
     * Expected translated locales list.
     *
     * @return string[]
     */
    private function getExpectedTranslatedLocales(): array
    {
        $expected = [];
        foreach ($this->expectedLocales as $locale) {
            $script = \Locale::getDisplayScript($locale);
            $scriptTranslated = $script ? \Locale::getDisplayScript($locale, $locale) . ', ' : '';
            $expected[$locale] = ucwords(\Locale::getDisplayLanguage($locale, $locale))
                . ' (' . $scriptTranslated
                . \Locale::getDisplayRegion($locale, $locale) . ') / '
                . $this->languages[$locale]
                . ' (' . ($script ? $script . ', ' : '') . $this->countries[$locale] . ')';
        }

        return $expected;
    }

    /**
     * Expected locales list.
     *
     * @return string[]
     */
    private function getExpectedLocales(): array
    {
        $expected = [];
        foreach ($this->expectedLocales as $locale) {
            $script = \Locale::getScript($locale);
            $scriptDisplayed = $script ? \Locale::getDisplayScript($locale) . ', ' : '';
            $expected[$locale] = $this->languages[$locale] . ' (' . $scriptDisplayed . $this->countries[$locale] . ')';
        }

        return $expected;
    }
}
