<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Stdlib\DateTime\Filter;

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
            \Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_SHORT
        )->will(
            $this->returnValue('MM-dd-yyyy')
        );
        $model = new Date($localeMock);
        // Check that date is converted to 'yyyy-MM-dd' format
        $this->assertEquals('2241-12-31', $model->filter('12-31-2241'));
    }
}
