<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Test\Unit;

use Magento\Framework\Locale\ConfigInterface;
use Magento\Framework\Setup\Lists;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ListsTest extends TestCase
{
    /**
     * @var Lists
     */
    private $lists;

    /**
     * @var MockObject|ConfigInterface
     */
    private $mockConfig;

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

    private function getExpectedTimezones($timeZone): array
    {
        return [
            'Australia/Darwin',
            'America/Los_Angeles',
            $timeZone,
            'Asia/Jerusalem',
        ];
    }

    protected function setUp(): void
    {
        $this->mockConfig = $this->getMockBuilder(ConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->mockConfig->method('getAllowedLocales')
            ->willReturn(array_keys($this->expectedLocales));
        $this->mockConfig->method('getAllowedCurrencies')
            ->willReturn($this->expectedCurrencies);

        $this->lists = new Lists($this->mockConfig);
    }

    public function testGetTimezoneList()
    {
        $resultTimezone = array_keys($this->lists->getTimezoneList());
        $timeZone = in_array('Europe/Kyiv', $resultTimezone) ? 'Europe/Kyiv' : 'Europe/Kiev';
        $expectedTimezones = $this->getExpectedTimezones($timeZone);
        $timezones = array_intersect($expectedTimezones, $resultTimezone);
        $this->assertEquals($expectedTimezones, $timezones);
    }

    public function testGetLocaleList()
    {
        $locales = array_intersect($this->expectedLocales, $this->lists->getLocaleList());
        $this->assertEquals($this->expectedLocales, $locales);
    }

    public function testGetCurrencyList()
    {
        $currencies = array_intersect($this->expectedCurrencies, array_keys($this->lists->getCurrencyList()));
        $this->assertEquals($this->expectedCurrencies, $currencies);
    }
}
