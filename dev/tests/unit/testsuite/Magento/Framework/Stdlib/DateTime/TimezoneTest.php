<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Stdlib\DateTime;

class TimezoneTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\Stdlib\DateTime\Timezone */
    protected $timezone;

    /** @var \Magento\Backend\Model\Locale\Resolver\Interceptor|\PHPUnit_Framework_MockObject_MockObject */
    protected $localeResolver;

    /** @var \Magento\Framework\Stdlib\DateTime\DateFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $dateFactory;

    /** @var \Magento\Framework\App\Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $scopeConfig;

    /** @var \Magento\Framework\Locale|\PHPUnit_Framework_MockObject_MockObject */
    protected $locale;

    /** @var \Magento\Framework\Stdlib\DateTime|\PHPUnit_Framework_MockObject_MockObject */
    protected $dateTime;

    /** @var \Magento\Store\Model\Resolver\Store|\PHPUnit_Framework_MockObject_MockObject */
    protected $scopeResolver;

    protected function setUp()
    {
        $this->locale = $this->getMock('Magento\Framework\Locale', ['getTranslation', 'toString'], [], '', false);
        $this->dateTime = $this->getMock('Magento\Framework\Stdlib\DateTime', ['isEmptyDate'], [], '', false);
        $this->scopeConfig = $this->getMock('Magento\Framework\App\Config', ['getValue'], [], '', false);
        $this->localeResolver = $this->getMock('Magento\Backend\Model\Locale\Resolver', ['getLocale'], [], '', false);
        $this->dateFactory = $this->getMock('Magento\Framework\Stdlib\DateTime\DateFactory', ['create'], [], '', false);
        $this->scopeResolver = $this->getMock('Magento\Store\Model\Resolver\Store', ['getScope'], [], '', false);

        $this->localeResolver->expects($this->any())->method('getLocale')->will($this->returnValue($this->locale));
        $this->scopeConfig->expects($this->any())
            ->method('getValue')
            ->with(\Magento\Core\Helper\Data::XML_PATH_DEFAULT_TIMEZONE, 'store')
            ->will($this->returnValue('America/Los_Angeles'));
        $this->locale->expects($this->any())->method('toString')->will($this->returnValue('en_US'));

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->timezone = $objectManager->getObject(
            'Magento\Framework\Stdlib\DateTime\Timezone',
            [
                'scopeResolver' => $this->scopeResolver,
                'localeResolver' => $this->localeResolver,
                'dateTime' => $this->dateTime,
                'dateFactory' => $this->dateFactory,
                'scopeConfig' => $this->scopeConfig,
                'scopeType' => 'store',
                'defaultTimezonePath' => \Magento\Core\Helper\Data::XML_PATH_DEFAULT_TIMEZONE
            ]
        );
    }

    public function testGetDateFormatWithLongYear()
    {
        $this->markTestIncomplete('MAGETWO-26166');
        $this->locale->staticExpects($this->once())->method('getTranslation')->with('short', 'date')
            ->will($this->returnValue('M/d/yy'));
        $this->assertSame('M/d/yyyy', $this->timezone->getDateFormatWithLongYear());
    }

    public function testDate()
    {
        $this->dateFactory->expects($this->any())->method('create')
            ->with(['date' => null, 'part' => null, 'locale' => $this->locale])
            ->will($this->returnValue(new \Magento\Framework\Stdlib\DateTime\Date(null, null, $this->locale)));
        $date = $this->timezone->date();
        $this->assertSame('America/Los_Angeles', $date->getTimezone());
    }

    public function testFormatDate()
    {
        $time = date('M j, Y');
        $date1 = new \Magento\Framework\Stdlib\DateTime\Date(1347260400, null, $this->locale);
        $date2 = new \Magento\Framework\Stdlib\DateTime\Date(strtotime($time), null, $this->locale);

        $this->dateFactory->expects($this->at(0))->method('create')
            ->will($this->returnValue($date1));
        $this->dateFactory->expects($this->at(1))->method('create')
            ->will($this->returnValue($date1));
        $this->dateFactory->expects($this->at(2))->method('create')
            ->will($this->returnValue($date2));
        $this->dateFactory->expects($this->exactly(3))->method('create');

        $this->markTestIncomplete('MAGETWO-26166');
        $this->locale->staticExpects($this->at(0))->method('getTranslation')
            ->with('medium', 'date', $this->locale)
            ->will($this->returnValue('MMM d, y'));
        $this->locale->staticExpects($this->at(1))->method('getTranslation')
            ->with('medium', 'time', $this->locale)
            ->will($this->returnValue('h:mm:ss a'));
        $this->locale->staticExpects($this->at(2))->method('getTranslation')
            ->with('medium', 'date', $this->locale)
            ->will($this->returnValue('MMM d, y'));
        $this->locale->staticExpects($this->at(3))->method('getTranslation')
            ->with('medium', 'date', $this->locale)
            ->will($this->returnValue('MMM d, y'));
        $this->locale->staticExpects($this->exactly(4))->method('getTranslation');

        $this->assertSame(
            'Sep 10, 2012 12:00:00 AM',
            $this->timezone->formatDate("10 September 2012", 'medium', true)
        );
        $this->assertSame(
            'Sep 10, 2012',
            $this->timezone->formatDate("10 September 2012", 'medium')
        );
        $this->assertSame(
            $time,
            $this->timezone->formatDate(null, 'medium')
        );
        $this->assertSame('date', $this->timezone->formatDate('date', 'wrong'));
        $this->assertSame('', $this->timezone->formatDate('date'));
    }

    public function testFormatTime()
    {
        $time = date('M j, Y g:m:s A');
        $date1 = new \Magento\Framework\Stdlib\DateTime\Date(1347260470, null, $this->locale);
        $date2 = new \Magento\Framework\Stdlib\DateTime\Date(strtotime($time), null, $this->locale);

        $this->dateFactory->expects($this->at(0))->method('create')
            ->with(['date' => 1347260470, 'part' => null, 'locale' => $this->locale])
            ->will($this->returnValue($date1));
        $this->dateFactory->expects($this->at(1))->method('create')->will($this->returnValue($date2));
        $this->dateFactory->expects($this->exactly(2))->method('create');

        $this->markTestIncomplete('MAGETWO-26166');
        $this->locale->staticExpects($this->at(0))->method('getTranslation')
            ->with('medium', 'time', $this->locale)
            ->will($this->returnValue('h:mm:ss a'));
        $this->locale->staticExpects($this->at(1))->method('getTranslation')
            ->with('medium', 'time', $this->locale)
            ->will($this->returnValue('h:mm:ss a'));
        $this->locale->staticExpects($this->at(2))->method('getTranslation')
            ->with('medium', 'date', $this->locale)
            ->will($this->returnValue('MMM d, y'));
        $this->locale->staticExpects($this->at(3))->method('getTranslation')
            ->with('medium', 'time', $this->locale)
            ->will($this->returnValue('h:mm:ss a'));
        $this->locale->staticExpects($this->exactly(4))->method('getTranslation');

        $this->assertSame('10 September 2012', $this->timezone->formatTime('10 September 2012', 'wrong_type'));
        $this->assertSame('12:01:10 AM', $this->timezone->formatTime('September 10, 2012 12:01:10 AM', 'medium'));
        $this->assertSame('12:01:10 AM', $this->timezone->formatTime($date1, 'medium'));
        $this->assertSame($time, $this->timezone->formatTime(null, 'medium', true));
    }

    public function testUtcDate()
    {
        $this->dateFactory->expects($this->any())->method('create')
            ->with(['date' => 1347260470, 'part' => null, 'locale' => $this->locale])
            ->will($this->returnValue(new \Magento\Framework\Stdlib\DateTime\Date(1347260470, null, $this->locale)));

        $date = $this->timezone->utcDate(\Magento\Core\Helper\Data::XML_PATH_DEFAULT_TIMEZONE, 1347260470);
        $this->assertSame('UTC', $date->getTimezone());
    }

    public function testIsScopeDateInInterval()
    {
        $scope = $this->getMock('Magento\Framework\App\ScopeInterface', ['getCode', 'getId']);
        $this->scopeResolver->expects($this->any())->method('getScope')->will($this->returnValue($scope));
        $this->dateTime->expects($this->at(0))->method('isEmptyDate')->will($this->returnValue(false));
        $this->dateTime->expects($this->at(1))->method('isEmptyDate')->will($this->returnValue(false));
        $this->dateTime->expects($this->at(2))->method('isEmptyDate')->will($this->returnValue(true));
        $this->dateTime->expects($this->at(3))->method('isEmptyDate')->will($this->returnValue(true));

        $this->assertFalse($this->timezone->isScopeDateInInterval('store'));
        $this->assertTrue($this->timezone->isScopeDateInInterval('store', null, '10 September 2036'));
    }
}
