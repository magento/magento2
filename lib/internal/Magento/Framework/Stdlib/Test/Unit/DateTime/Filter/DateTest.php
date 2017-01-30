<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Stdlib\Test\Unit\DateTime\Filter;

use \Magento\Framework\Stdlib\DateTime\Filter\Date;

class DateTest extends \PHPUnit_Framework_TestCase
{
    public function testFilter()
    {
        $localeMock = $this->getMock('\Magento\Framework\Stdlib\DateTime\TimezoneInterface');
        $localeMock->expects(
            $this->once()
        )->method(
            'getDateFormat'
        )->with(
            \IntlDateFormatter::SHORT
        )->will(
            $this->returnValue('MM-dd-yyyy')
        );
        $model = new Date($localeMock);
        // Check that date is converted to 'yyyy-MM-dd' format
        $this->assertEquals('2241-12-31', $model->filter('12-31-2241'));
    }
}
