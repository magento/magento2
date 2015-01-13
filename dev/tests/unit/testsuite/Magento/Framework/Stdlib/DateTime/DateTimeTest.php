<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Stdlib\DateTime;

class DateTimeTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\Stdlib\DateTime\DateTime */
    protected $dateTime;

    /** @var \Magento\Framework\Stdlib\DateTime\Date */
    protected $date;

    /** @var  \Magento\Framework\Stdlib\DateTime\Timezone|\PHPUnit_Framework_MockObject_MockObject */
    protected $localeDate;

    protected function setUp()
    {
        require_once __DIR__ . '/../_files/gmdate_mock.php';
        $this->date = new \Magento\Framework\Stdlib\DateTime\Date(1403832149);

        $this->localeDate = $this->getMock(
            'Magento\Framework\Stdlib\DateTime\Timezone',
            ['getConfigTimezone', 'date'],
            [],
            '',
            false
        );
        $this->localeDate->expects($this->any())->method('getConfigTimezone')
            ->will($this->returnValue('America/Los_Angeles'));
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->dateTime = $objectManager->getObject(
            'Magento\Framework\Stdlib\DateTime\DateTime',
            ['localeDate' => $this->localeDate]
        );
    }

    public function testCalculateOffset()
    {
        if (date('I')) {
            $this->assertSame(-25200, $this->dateTime->calculateOffset());
        } else {
            $this->assertSame(-28800, $this->dateTime->calculateOffset());
        }
        $curZone = @date_default_timezone_get();
        date_default_timezone_set('Europe/Kiev');
        if (date('I')) {
            $this->assertSame(10800, $this->dateTime->calculateOffset('Europe/Kiev'));
        } else {
            $this->assertSame(7200, $this->dateTime->calculateOffset('Europe/Kiev'));
        }
        date_default_timezone_set($curZone);
    }

    public function testGmtDate()
    {
        $time = 1403858418;
        $this->localeDate->expects($this->any())->method('date')->with($time)
            ->will($this->returnValue($this->date));
        $this->assertSame(false, $this->dateTime->gmtDate(null, 'tro-lo-lo'));
        $this->assertSame('2014-06-27', $this->dateTime->gmtDate('Y-m-d', $time));
    }

    public function testDate()
    {
        $time = 1403858418;
        $this->localeDate->expects($this->any())->method('date')->with($time)
            ->will($this->returnValue($this->date));
        $this->assertSame('2014-06-26', $this->dateTime->date('Y-m-d', $time));
        $this->assertSame('2014-06-26 11:22:29', $this->dateTime->date(null, $time));
    }

    public function testGmtTimestamp()
    {
        $time = time();
        $this->localeDate->expects($this->at(0))->method('date')->with($time)
            ->will($this->returnValue($this->date));
        $this->localeDate->expects($this->at(1))->method('date')->with(strtotime("10 September 2000"))
            ->will($this->returnValue($this->date));

        $this->assertSame(1403857349, $this->dateTime->gmtTimestamp($time));
        $this->assertSame(1403857349, $this->dateTime->gmtTimestamp("10 September 2000"));
        $this->assertSame(false, $this->dateTime->gmtTimestamp("la-la-la"));
        $this->assertSame(1404377188, $this->dateTime->gmtTimestamp());
    }

    public function testTimestamp()
    {
        $time = time();
        $this->localeDate->expects($this->at(0))->method('date')->with(1404377188)
            ->will($this->returnValue($this->date));
        $this->localeDate->expects($this->at(1))->method('date')->with($time)
            ->will($this->returnValue($this->date));
        $this->localeDate->expects($this->at(2))->method('date')->with(strtotime("10 September 2000"))
            ->will($this->returnValue($this->date));

        $this->assertSame(1403806949, $this->dateTime->timestamp());
        $this->assertSame(1403806949, $this->dateTime->timestamp($time));
        $this->assertSame(1403806949, $this->dateTime->timestamp("10 September 2000"));
    }

    public function testGetGmtOffset()
    {
        if (date('I')) {
            $this->assertSame(-25200, $this->dateTime->getGmtOffset('seconds'));
            $this->assertSame(-25200, $this->dateTime->getGmtOffset('seconds11'));
            $this->assertSame(-420, $this->dateTime->getGmtOffset('minutes'));
            $this->assertSame(-7, $this->dateTime->getGmtOffset('hours'));
        } else {
            $this->assertSame(-28800, $this->dateTime->getGmtOffset('seconds'));
            $this->assertSame(-28800, $this->dateTime->getGmtOffset('seconds11'));
            $this->assertSame(-480, $this->dateTime->getGmtOffset('minutes'));
            $this->assertSame(-8, $this->dateTime->getGmtOffset('hours'));
        }
    }
}
