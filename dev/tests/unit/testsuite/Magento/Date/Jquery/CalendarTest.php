<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Date_Jquery
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Magento_Date_Jquery_CalendarTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test conversions from old calendar date/time formats to jQuery datepicker compatible formats.
     *
     * @param string  $expected
     * @param string  $formatString - Date and/or time string to convert
     * @param boolean $formatDate - Whether to convert date (true) or not (false)
     * @param boolean $formatTime - Whether to convert time (true) or not (false)
     *
     * @dataProvider convertToDateTimeFormatDataProvider
     */
    public function testConvertToDateTimeFormat($expected, $formatString, $formatDate, $formatTime)
    {
        $this->assertEquals(
            $expected, Magento_Date_Jquery_Calendar::convertToDateTimeFormat($formatString, $formatDate, $formatTime)
        );
    }

    /**
     * @return array
     */
    public function convertToDateTimeFormatDataProvider()
    {
        return array(
            array("mm/dd/yy", "%m/%d/%Y", true, false),
            array("%H:%M:%S", "HH:mm:ss", false, true),
            array("mm/dd/yy %H:%M:%S", "%m/%d/%Y HH:mm:ss", true, true),
            array("%m/%d/%Y HH:mm:ss", "%m/%d/%Y HH:mm:ss", false, false)
        );
    }
}
