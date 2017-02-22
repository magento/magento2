<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Stdlib\Test\Unit\DateTime;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class DateTimeFormatterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTimeFormatter
     */
    protected $dateTimeFormatter;

    protected function setUp()
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('Skip this test for hhvm due to problem with \IntlDateFormatter::formatObject');
        }

        $this->dateTimeFormatter = (new ObjectManager($this))
            ->getObject('Magento\Framework\Stdlib\DateTime\DateTimeFormatter', [
                'useIntlFormatObject' => false,
            ]);
    }

    /**
     * @param \IntlCalendar|\DateTime $object
     * @param string|int|array|null $format
     * @param string|null $locale
     * @dataProvider dataProviderFormatObject
     */
    public function testFormatObject($object, $format = null, $locale = null)
    {
        $this->assertEquals(
            \IntlDateFormatter::formatObject($object, $format, $locale),
            $this->dateTimeFormatter->formatObject($object, $format, $locale)
        );
    }

    /**
     * @return array
     */
    public function dataProviderFormatObject()
    {
        $date = new \DateTime('2013-06-06 17:05:06 Europe/Dublin');
        $calendar = \IntlCalendar::fromDateTime('2013-06-06 17:05:06 Europe/Dublin');

        return [
            [$date, null, null],
            [$date, \IntlDateFormatter::FULL, null],
            [$date, null, 'en-US'],
            [$date, [\IntlDateFormatter::SHORT, \IntlDateFormatter::FULL], 'en-US'],
            [$date, 'E y-MM-d HH,mm,ss.SSS v', 'en-US'],
            [$date, [\IntlDateFormatter::NONE, \IntlDateFormatter::FULL], null],
            [$date, "d 'of' MMMM y", 'en_US'],
            [new \DateTime('2013-09-09 09:09:09 Europe/Madrid'), \IntlDateFormatter::FULL, 'es_ES'],
            [new \DateTime('2013-09-09 09:09:09 -01:00'), null, null],
            [new \DateTime('2013-09-09 09:09:09 +01:00'), null, null],

            [$calendar, null, null],
            [$calendar, \IntlDateFormatter::FULL, null],
            [$calendar, null, 'en-US'],
            [$calendar, [\IntlDateFormatter::SHORT, \IntlDateFormatter::FULL], 'en-US'],
            [$calendar, 'E y-MM-d HH,mm,ss.SSS v', 'en-US'],
            [$calendar, [\IntlDateFormatter::NONE, \IntlDateFormatter::FULL], null],
            [$calendar, "d 'of' MMMM y", 'en_US'],
            [\IntlCalendar::fromDateTime('2013-09-09 09:09:09 Europe/Madrid'), \IntlDateFormatter::FULL, 'es_ES'],
            [\IntlCalendar::fromDateTime('2013-09-09 09:09:09 -01:00'), null, null],
            [\IntlCalendar::fromDateTime('2013-09-09 09:09:09 +01:00'), null, null],
        ];
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Format type is invalid
     */
    public function testFormatObjectIfPassedWrongFormat()
    {
        $this->dateTimeFormatter->formatObject(new \DateTime('2013-06-06 17:05:06 Europe/Dublin'), new \StdClass());
    }
}
