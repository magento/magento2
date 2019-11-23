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
        'en_US' => 'English (United States)',
        'en_GB' => 'English (United Kingdom)',
        'uk_UA' => 'Ukrainian (Ukraine)',
        'de_DE' => 'German (Germany)',
        'sr_Cyrl_RS' => 'Serbian (Cyrillic, Serbia)',
        'sr_Latn_RS' => 'Serbian (Latin, Serbia)'
    ];

    /**
     * @var array
     */
    private $expectedTranslatedLocales = [
        'en_US' => 'English (United States) / English (United States)',
        'en_GB' => 'English (United Kingdom) / English (United Kingdom)',
        'uk_UA' => 'українська (Україна) / Ukrainian (Ukraine)',
        'de_DE' => 'Deutsch (Deutschland) / German (Germany)',
        'sr_Cyrl_RS' => 'српски (ћирилица, Србија) / Serbian (Cyrillic, Serbia)',
        'sr_Latn_RS' => 'Srpski (latinica, Srbija) / Serbian (Latin, Serbia)'
    ];

    protected function setUp()
    {
        $this->mockConfig = $this->getMockBuilder(ConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockConfig->method('getAllowedLocales')
            ->willReturn(array_keys($this->expectedLocales));
        $this->mockConfig->method('getAllowedCurrencies')
            ->willReturn($this->expectedCurrencies);

        $this->mockLocaleResolver = $this->getMockBuilder(ResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
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
        $locales = array_intersect(
            $this->expectedLocales,
            $this->convertOptionLocales($this->listsModel->getOptionLocales())
        );
        $this->assertEquals($this->expectedLocales, $locales);
    }

    public function testGetTranslatedOptionLocales()
    {
        $locales = array_intersect(
            $this->expectedTranslatedLocales,
            $this->convertOptionLocales($this->listsModel->getTranslatedOptionLocales())
        );
        $this->assertEquals($this->expectedTranslatedLocales, $locales);
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
}
