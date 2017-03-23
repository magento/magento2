<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Stdlib\Test\Unit\DateTime;

use \Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Magento\Framework\Stdlib\DateTimeTest test case
 */
class DateTimeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function testGmtTimestamp()
    {
        $timezone = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class)->getMock();
        $timezone->expects($this->any())
            ->method('date')
            ->willReturn(new \DateTime('2015-04-02 21:03:00'));
        /** @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone */

        $dateTime = new DateTime($timezone);
        $this->assertEquals(
            gmdate('U', strtotime('2015-04-02 21:03:00')),
            $dateTime->gmtTimestamp('2015-04-02 21:03:00')
        );
    }
}
