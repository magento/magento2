<?php
/**
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Stdlib\DateTime\Filter;

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
            \Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_SHORT
        )->will(
            $this->returnValue('HH:mm:ss MM-dd-yyyy')
        );
        $model = new DateTime($localeMock);
        // Check that datetime is converted to 'yyyy-MM-dd HH:mm:ss' format
        $this->assertEquals('2241-12-31 23:59:53', $model->filter('23:59:53 12-31-2241'));
    }
}
