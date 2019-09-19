<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

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
    protected $listsModel;

    /**
     * @var  MockObject | ConfigInterface
     */
    protected $mockConfig;

    /**
     * @var  MockObject | ResolverInterface
     */
    protected $mockLocaleResolver;

    protected function setUp()
    {
        $this->mockConfig = $this->getMockBuilder(ConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
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
        $allowedCurrencies = ['USD', 'EUR', 'GBP', 'UAH'];

        $this->mockConfig->expects($this->once())
            ->method('getAllowedCurrencies')
            ->willReturn($allowedCurrencies);

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
        $this->setupForOptionLocales();

        $expectedResults = ['en_US', 'uk_UA', 'de_DE'];

        $list = $this->listsModel->getOptionLocales();
        foreach ($expectedResults as $value) {
            $found = false;
            foreach ($list as $item) {
                $found = $found || ($value == $item['value']);
            }
            $this->assertTrue($found);
        }
    }

    public function testGetTranslatedOptionLocales()
    {
        $this->setupForOptionLocales();

        $expectedResults = ['en_US', 'uk_UA', 'de_DE'];

        $list = $this->listsModel->getOptionLocales();
        foreach ($expectedResults as $value) {
            $found = false;
            foreach ($list as $item) {
                $found = $found || ($value == $item['value']);
            }
            $this->assertTrue($found);
        }
    }

    /**
     * Setup for option locales
     */
    protected function setupForOptionLocales()
    {
        $allowedLocales = ['en_US', 'uk_UA', 'de_DE'];
        $this->mockConfig->expects($this->once())
            ->method('getAllowedLocales')
            ->willReturn($allowedLocales);
    }
}
