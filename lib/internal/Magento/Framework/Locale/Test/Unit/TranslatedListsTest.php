<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Locale\Test\Unit;

class TranslatedListsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Locale\TranslatedLists
     */
    protected $listsModel;

    /**
     * @var  \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Locale\ConfigInterface
     */
    protected $mockConfig;

    /**
     * @var  \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Locale\ResolverInterface
     */
    protected $mockLocaleResolver;

    protected function setUp()
    {
        $this->mockConfig = $this->getMockBuilder(\Magento\Framework\Locale\ConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockLocaleResolver = $this->getMockBuilder(\Magento\Framework\Locale\ResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockLocaleResolver->expects($this->once())
            ->method('getLocale')
            ->will($this->returnValue('en_US'));

        $this->listsModel = new \Magento\Framework\Locale\TranslatedLists(
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
            ->will($this->returnValue($allowedCurrencies));

        $expectedResults = ['USD', 'EUR', 'GBP', 'UAH'];

        $currencyList = $this->listsModel->getOptionCurrencies();
        $currencyCodes = array_map(
            function ($data) {
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
            ->will($this->returnValue($allowedLocales));
    }
}
