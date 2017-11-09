<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Stdlib\Test\Unit\DateTime;

use Magento\Framework\Stdlib\DateTime\Timezone;

/**
 * Test for @see Timezone
 */
class TimezoneTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        parent::setUp();
    }

    /**
     * Test date parsing with different includeTime options
     *
     * @param string $date
     * @param string $locale
     * @param bool $includeTime
     * @param int $expectedTimestamp
     * @dataProvider dateIncludeTimeDataProvider
     */
    public function testDateIncludeTime($date, $locale, $includeTime, $expectedTimestamp)
    {
        $this->scopeConfigMock->method('getValue')->willReturn('America/Chicago');
        /** @var Timezone $timezone */
        $timezone = $this->objectManager->getObject(Timezone::class, ['scopeConfig' => $this->scopeConfigMock]);

        /** @var \DateTime $dateTime */
        $dateTime = $timezone->date($date, $locale, true, $includeTime);
        $this->assertEquals($expectedTimestamp, $dateTime->getTimestamp());
    }

    public function dateIncludeTimeDataProvider()
    {
        return [
            'Parse date without time' => [
                '19/05/2017', // date
                'ar_KW', // locale
                false, // include time
                1495170000 // expected timestamp
            ],
            'Parse date with time' => [
                '05/19/2017 00:01 am', // date
                'en_US', // locale
                true, // include time
                1495170060 // expected timestamp
            ],
        ];
    }
}
