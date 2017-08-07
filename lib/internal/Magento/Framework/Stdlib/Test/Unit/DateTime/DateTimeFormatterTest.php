<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Stdlib\Test\Unit\DateTime;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class DateTimeFormatterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeResolverMock;

    protected function setUp()
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('Skip this test for hhvm due to problem with \IntlDateFormatter::formatObject');
        }
        $this->objectManager = new ObjectManager($this);
        $this->localeResolverMock = $this->getMockBuilder(\Magento\Framework\Locale\ResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->localeResolverMock->expects($this->any())
            ->method('getLocale')
            ->willReturn('fr-FR');
    }

    /**
     * @param \IntlCalendar|\DateTimeInterface $object
     * @param string|int|array|null $format
     * @param string|null $locale
     * @param boolean $useIntlFormatObject
     * @dataProvider dataProviderFormatObject
     */
    public function testFormatObject($object, $format = null, $locale = null, $useIntlFormatObject = false)
    {
        $dateTimeFormatter = $this->objectManager->getObject(
            \Magento\Framework\Stdlib\DateTime\DateTimeFormatter::class,
            [
                'useIntlFormatObject' => $useIntlFormatObject,
            ]
        );

        $reflection = new \ReflectionClass(get_class($dateTimeFormatter));
        $reflectionProperty = $reflection->getProperty('localeResolver');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($dateTimeFormatter, $this->localeResolverMock);

        $this->assertEquals(
            \IntlDateFormatter::formatObject(
                $object,
                $format,
                (null === $locale) ? 'fr-FR' : $locale
            ),
            $dateTimeFormatter->formatObject($object, $format, $locale)
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
            [$date, null, null, true],
            [$date, \IntlDateFormatter::FULL, null, true],
            [$date, null, 'en-US', true],
            [$date, [\IntlDateFormatter::SHORT, \IntlDateFormatter::FULL], 'en-US', true],
            [$date, 'E y-MM-d HH,mm,ss.SSS v', 'en-US', true],
            [$date, [\IntlDateFormatter::NONE, \IntlDateFormatter::FULL], null, true],
            [$date, "d 'of' MMMM y", 'en_US', true],
            [new \DateTime('2013-09-09 09:09:09 Europe/Madrid'), \IntlDateFormatter::FULL, 'es_ES', true],
            [new \DateTime('2013-09-09 09:09:09 -01:00'), null, null, true],
            [new \DateTime('2013-09-09 09:09:09 +01:00'), null, null, true],
            [$calendar, null, null, true],
            [$calendar, \IntlDateFormatter::FULL, null, true],
            [$calendar, null, 'en-US', true],
            [$calendar, [\IntlDateFormatter::SHORT, \IntlDateFormatter::FULL], 'en-US', true],
            [$calendar, 'E y-MM-d HH,mm,ss.SSS v', 'en-US', true],
            [$calendar, [\IntlDateFormatter::NONE, \IntlDateFormatter::FULL], null, true],
            [$calendar, "d 'of' MMMM y", 'en_US', true],
            [\IntlCalendar::fromDateTime('2013-09-09 09:09:09 Europe/Madrid'), \IntlDateFormatter::FULL, 'es_ES', true],
            [\IntlCalendar::fromDateTime('2013-09-09 09:09:09 -01:00'), null, null, true],
            [\IntlCalendar::fromDateTime('2013-09-09 09:09:09 +01:00'), null, null, true],
        ];
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Format type is invalid
     */
    public function testFormatObjectIfPassedWrongFormat()
    {
        $dateTimeFormatter = $this->objectManager->getObject(
            \Magento\Framework\Stdlib\DateTime\DateTimeFormatter::class,
            [
                'useIntlFormatObject' => false,
            ]
        );

        $reflection = new \ReflectionClass(get_class($dateTimeFormatter));
        $reflectionProperty = $reflection->getProperty('localeResolver');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($dateTimeFormatter, $this->localeResolverMock);
        $dateTimeFormatter->formatObject(new \DateTime('2013-06-06 17:05:06 Europe/Dublin'), new \StdClass());
    }
}
