<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Element\Html;

use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Html\Calendar;
use Magento\Framework\View\Element\Template\Context;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @see Calendar
 */
class CalendarTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @see MAGETWO-60828
     * @see Calendar::_toHtml
     *
     * @param string $locale
     * @dataProvider localesDataProvider
     */
    public function testToHtmlWithDifferentLocales($locale)
    {
        $calendarBlock = (new ObjectManager($this))->getObject(
            Calendar::class,
            [
                'localeResolver' => $this->getLocalResolver($locale)
            ]
        );

        $calendarBlock->toHtml();
    }

    /**
     * @return array
     */
    public function localesDataProvider()
    {
        return [
            ['en_US'],
            ['ja_JP'],
            ['ko_KR'],
        ];
    }

    /**
     * @see Calendar::getYearRange
     */
    public function testGetYearRange()
    {
        $calendarBlock = (new ObjectManager($this))->getObject(
            Calendar::class,
            [
                'context' => $this->getContext()
            ]
        );

        $testCurrentYear = (new \DateTime())->format('Y');
        $this->assertEquals(
            (int) $testCurrentYear - 100 . ':' . ($testCurrentYear + 100),
            $calendarBlock->getYearRange()
        );
    }

    /**
     * @param string $locale
     * @return ResolverInterface|MockObject
     */
    private function getLocalResolver($locale)
    {
        $localResolver = $this->getMockBuilder(ResolverInterface::class)
            ->getMockForAbstractClass();
        $localResolver->method('getLocale')->willReturn($locale);

        return $localResolver;
    }

    /**
     * @return Context|Object
     */
    private function getContext()
    {
        $localeDate = $this->getMockBuilder(TimezoneInterface::class)
            ->getMockForAbstractClass();

        return (new ObjectManager($this))->getObject(
            Context::class,
            ['localeDate' => $localeDate]
        );
    }
}
