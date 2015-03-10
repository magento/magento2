<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Locale;

class ListsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Locale\Lists
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
        $this->mockConfig = $this->getMockBuilder('\Magento\Framework\Locale\ConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockLocaleResolver = $this->getMockBuilder('\Magento\Framework\Locale\ResolverInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockLocaleResolver->expects($this->once())
            ->method('getLocale')
            ->will($this->returnValue('en_US'));

        $this->listsModel = new \Magento\Framework\Locale\Lists(
            $this->mockConfig,
            $this->mockLocaleResolver
        );
    }

    public function testGetCountryTranslation()
    {
        $this->assertNull($this->listsModel->getCountryTranslation(null));
    }

    public function testGetOptionAllCurrencies()
    {
        // clearly English results
        $expectedResults = [
            ['value' => 'BAM', 'label' => 'Bosnia-Herzegovina Convertible Mark'],
            ['value' => 'TTD', 'label' => 'Trinidad and Tobago Dollar'],
            ['value' => 'USN', 'label' => 'US Dollar (Next day)'],
            ['value' => 'USS', 'label' => 'US Dollar (Same day)'],
        ];

        $currencyList = $this->listsModel->getOptionAllCurrencies();
        foreach ($expectedResults as $value) {
            $this->assertContains($value, $currencyList);
        }
    }

    public function testGetOptionCurrencies()
    {
        $allowedCurrencies = ['USD', 'GBP', 'EUR'];

        $this->mockConfig->expects($this->once())
            ->method('getAllowedCurrencies')
            ->will($this->returnValue($allowedCurrencies));

        $expectedArray = [
            ['value' => 'GBP', 'label' => 'British Pound Sterling'],
            ['value' => 'EUR', 'label' => 'Euro'],
            ['value' => 'USD', 'label' => 'US Dollar'],
        ];

        $this->assertSame($expectedArray, $this->listsModel->getOptionCurrencies());
    }

    public function testGetOptionCountries()
    {
        // clearly English results
        $expectedResults = [
            ['value' => 'AG', 'label' => 'Antigua and Barbuda'],
            ['value' => 'BA', 'label' => 'Bosnia and Herzegovina'],
            ['value' => 'GS', 'label' => 'South Georgia & South Sandwich Islands'],
            ['value' => 'PM', 'label' => 'Saint Pierre and Miquelon'],
        ];

        $optionCountries = $this->listsModel->getOptionCountries();
        foreach ($expectedResults as $value) {
            $this->assertContains($value, $optionCountries);
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
        $expectedResults = [
            ['value' => 'Australia/Darwin', 'label' => 'Australian Central Standard Time (Australia/Darwin)'],
            ['value' => 'America/Los_Angeles', 'label' => 'Pacific Standard Time (America/Los_Angeles)'],
            ['value' => 'Europe/Kiev', 'label' => 'Eastern European Standard Time (Europe/Kiev)'],
            ['value' => 'Asia/Jerusalem', 'label' => 'Israel Standard Time (Asia/Jerusalem)'],
        ];

        $timeZones = $this->listsModel->getOptionTimezones();
        foreach ($expectedResults as $value) {
            $this->assertContains($value, $timeZones);
        }

        $timeZoneList = \DateTimeZone::listIdentifiers(\DateTimeZone::ALL_WITH_BC);
        foreach ($timeZones as $timeZone) {
            $this->assertContains($timeZone['value'], $timeZoneList);
        }
    }

    public function testGetOptionLocales()
    {
        $this->setupForOptionLocales();

        $this->assertEquals(
            [
                ['value' => 'en_US', 'label' => 'English (United States)'],
                ['value' => 'uk_UA', 'label' => 'Ukrainian (Ukraine)'],
            ],
            $this->listsModel->getOptionLocales()
        );
    }

    public function testGetTranslatedOptionLocales()
    {
        $this->setupForOptionLocales();

        $this->assertEquals(
            [
                ['value' => 'en_US', 'label' => 'English (United States) / English (United States)'],
                ['value' => 'uk_UA', 'label' => 'українська (Україна) / Ukrainian (Ukraine)'],
            ],
            $this->listsModel->getTranslatedOptionLocales()
        );
    }

    /**
     * Setup for option locales
     */
    protected function setupForOptionLocales()
    {
        $allowedLocales = ['en_US', 'uk_UA'];
        $this->mockConfig->expects($this->once())
            ->method('getAllowedLocales')
            ->will($this->returnValue($allowedLocales));
    }
}
