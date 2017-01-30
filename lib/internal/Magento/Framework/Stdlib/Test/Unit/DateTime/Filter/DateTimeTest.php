<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Stdlib\Test\Unit\DateTime\Filter;

use \Magento\Framework\Stdlib\DateTime\Filter\DateTime;

class DateTimeTest extends \PHPUnit_Framework_TestCase
{
    public function testFilter()
    {
        $localeMock = $this->getMock('\Magento\Framework\Stdlib\DateTime\TimezoneInterface');
        $localeMock->expects(
            $this->once()
        )->method(
            'getDateTimeFormat'
        )->with(
            \IntlDateFormatter::SHORT
        )->will(
            $this->returnValue('HH:mm:ss MM-dd-yyyy')
        );
        $model = new DateTime($localeMock);
        // Check that datetime is converted to 'yyyy-MM-dd HH:mm:ss' format
        $this->assertEquals('2241-12-31 23:59:53', $model->filter('23:59:53 12-31-2241'));
    }
}
