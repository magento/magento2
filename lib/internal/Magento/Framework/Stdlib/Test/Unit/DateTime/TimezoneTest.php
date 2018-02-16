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
     * Test date parsing with different date format
     *
     * @param string $date
     * @param string $locale
     * @param int $expectedTimestamp
     * @dataProvider dateDataProvider
     */
    public function testDate($date, $locale, $expectedTimestamp)
    {
        $this->scopeConfigMock->method('getValue')->willReturn('America/Chicago');
        /** @var Timezone $timezone */
        $timezone = $this->objectManager->getObject(Timezone::class, ['scopeConfig' => $this->scopeConfigMock]);

        /** @var \DateTime $dateTime */
        $date = $timezone->date($date, $locale, true);
        $this->assertEquals($expectedTimestamp, $date->getTimestamp());
    }

    public function dateDataProvider()
    {
        return [
            'Parse date with dd/mm/yyyy format' => [
                '19/05/2017', // date
                'ar_KW', // locale
                1495177200 // expected timestamp
            ],
            'Parse date with mm/dd/yyyy format' => [
                '05/19/2017', // date
                'en_US', // locale
                1495177200 // expected timestamp
            ]
        ];
    }
}
