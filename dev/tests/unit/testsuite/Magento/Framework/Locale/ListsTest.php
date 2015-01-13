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
     * @var  \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\App\ScopeResolverInterface
     */
    protected $mockScopeResolver;

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
        $this->mockScopeResolver = $this->getMockBuilder('\Magento\Framework\App\ScopeResolverInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockConfig = $this->getMockBuilder('\Magento\Framework\Locale\ConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockLocaleResolver = $this->getMockBuilder('\Magento\Framework\Locale\ResolverInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $locale = "some_locale";
        $this->mockLocaleResolver->expects($this->atLeastOnce())
            ->method('setLocale')
            ->with($locale);

        $this->listsModel = new \Magento\Framework\Locale\Lists(
            $this->mockScopeResolver,
            $this->mockConfig,
            $this->mockLocaleResolver,
            $locale
        );
    }

    public function testGetCountryTranslationList()
    {
        $locale = new \Magento\Framework\Locale('en');

        $this->mockLocaleResolver->expects($this->once())
            ->method('getLocale')
            ->will($this->returnValue($locale));

        // clearly english results
        $expectedResults = [
            'AD' => 'Andorra',
            'ZZ' => 'Unknown Region',
            'VC' => 'St. Vincent & Grenadines',
            'PM' => 'Saint Pierre and Miquelon',
        ];

        $countryTranslationList = $this->listsModel->getCountryTranslationList();
        foreach ($expectedResults as $key => $value) {
            $this->assertArrayHasKey($key, $countryTranslationList);
            $this->assertEquals($value, $countryTranslationList[$key]);
        }
    }

    public function testGetCountryTranslation()
    {
        $locale = new \Magento\Framework\Locale('en');

        $this->mockLocaleResolver->expects($this->once())
            ->method('getLocale')
            ->will($this->returnValue($locale));

        $this->assertFalse($this->listsModel->getCountryTranslation(null));
    }

    public function testGetTranslationList()
    {
        $locale = new \Magento\Framework\Locale('en');

        $this->mockLocaleResolver->expects($this->exactly(2))
            ->method('getLocale')
            ->will($this->returnValue($locale));

        $path = 'territory';
        $value = 2;

        // clearly english results
        $expectedResults = [
            'AD' => 'Andorra',
            'ZZ' => 'Unknown Region',
            'VC' => 'St. Vincent & Grenadines',
            'PM' => 'Saint Pierre and Miquelon',
        ];

        $countryTranslationList = $this->listsModel->getTranslationList($path, $value);
        foreach ($expectedResults as $key => $value) {
            $this->assertArrayHasKey($key, $countryTranslationList);
            $this->assertEquals($value, $countryTranslationList[$key]);
        }
    }

    public function testGetOptionAllCurrencies()
    {
        $locale = new \Magento\Framework\Locale('en');

        $this->mockLocaleResolver->expects($this->exactly(2))
            ->method('getLocale')
            ->will($this->returnValue($locale));

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
        $locale = new \Magento\Framework\Locale('en');

        $this->mockLocaleResolver->expects($this->exactly(2))
            ->method('getLocale')
            ->will($this->returnValue($locale));

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
        $locale = new \Magento\Framework\Locale('en');

        $this->mockLocaleResolver->expects($this->once())
            ->method('getLocale')
            ->will($this->returnValue($locale));

        // clearly English results
        $expectedResults = [
            ['value' => 'AG', 'label' => 'Antigua and Barbuda'],
            ['value' => 'BA', 'label' => 'Bosnia and Herzegovina'],
            ['value' => 'CC', 'label' => 'Cocos (Keeling) Islands'],
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
        $locale = new \Magento\Framework\Locale('en');

        $this->mockLocaleResolver->expects($this->exactly(2))
            ->method('getLocale')
            ->will($this->returnValue($locale));

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
        $locale = new \Magento\Framework\Locale('en');

        $this->mockLocaleResolver->expects($this->exactly(2))
            ->method('getLocale')
            ->will($this->returnValue($locale));

        $expectedResults = [
            ['value' => 'Australia/Darwin', 'label' => 'AUS Central Standard Time (Australia/Darwin)'],
            ['value' => 'Asia/Jerusalem', 'label' => 'Israel Standard Time (Asia/Jerusalem)'],
            ['value' => 'Asia/Yakutsk', 'label' => 'Yakutsk Standard Time (Asia/Yakutsk)'],
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
                ['value' => 'az_AZ', 'label' => 'Azerbaijani (Azerbaijan)'],
                ['value' => 'en_US', 'label' => 'English (United States)'],
            ],
            $this->listsModel->getOptionLocales()
        );
    }

    public function testGetTranslatedOptionLocales()
    {
        $this->setupForOptionLocales();

        $this->assertEquals(
            [
                ['value' => 'az_AZ', 'label' => 'Azərbaycan (Azərbaycan) / Azerbaijani (Azerbaijan)'],
                ['value' => 'en_US', 'label' => 'English (United States) / English (United States)'],
            ],
            $this->listsModel->getTranslatedOptionLocales()
        );
    }

    /**
     * @return \Magento\Framework\LocaleInterface
     */
    protected function setupForOptionLocales()
    {
        $locale = new \Magento\Framework\Locale('en');

        $this->mockLocaleResolver->expects($this->any())
            ->method('getLocale')
            ->will($this->returnValue($locale));

        $allowedLocales = ['en_US', 'az_AZ'];
        $this->mockConfig->expects($this->once())
            ->method('getAllowedLocales')
            ->will($this->returnValue($allowedLocales));

        return $locale;
    }
}
