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
 * @package     Mage_Core
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Varien_Date test case
 */
class Varien_DateTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Varien_Date::convertZendToStrftime
     * @todo   Implement testConvertZendToStrftime().
     * @dataProvider dp_convertZendToStrfTime
     */
    public function testConvertZendToStrftime($zendDateFormat, $expectedOutput)
    {
        $actual = Varien_Date::convertZendToStrftime($zendDateFormat);
        $this->assertEquals($expectedOutput, $actual);
    }

    public function dp_convertZendToStrfTime()
    {
        return array(
            'Year' => array(
                Zend_Date::YEAR,
                "%Y"
            ),
            'Month' => array(
                Zend_Date::MONTH,
                "%m"
            ),
            'Month Name' => array(
                Zend_Date::MONTH_NAME,
                "%B"
            ),
            'Hour' => array(
                Zend_Date::HOUR,
                "%H"
            ),
            'Combined Date and Time' => array(
                "yyyy-MM-ddTHH:mm:ssZZZZ",
                "%c"
            ),
            'German Date and Time' => array(
                Zend_Date::DAY . "." . Zend_Date::MONTH . "." . Zend_Date::YEAR . " " . Zend_Date::HOUR . ":" . Zend_Date::MINUTE,
                "%d.%m.%Y %H:%M"
            ),
        );
    }

    /**
     * @covers Varien_Date::toTimestamp
     */
    public function testToTimestamp()
    {
        $date = new Zend_Date();
        $this->assertEquals($date->getTimestamp(), Varien_Date::toTimestamp($date));

        $this->assertEquals(time(), Varien_Date::toTimestamp(true));

        $date = '2012-07-19 16:52';
        $this->assertEquals(strtotime($date), Varien_Date::toTimestamp($date));
    }

    /**
     * @covers Varien_Date::now
     */
    public function testNow()
    {
        $this->assertEquals(date(Varien_Date::DATE_PHP_FORMAT), Varien_Date::now(true));
        $this->assertEquals(date(Varien_Date::DATETIME_PHP_FORMAT), Varien_Date::now(false));
    }

    /**
     * @covers Varien_Date::formatDate
     * @todo   Implement testFormatDate().
     * @dataProvider dp_formatDate
     */
    public function testFormatDate($date, $includeTime, $expectedResult)
    {
        $actual = Varien_Date::formatDate($date, $includeTime);
        $this->assertEquals($expectedResult, $actual);
    }

    public function dp_formatDate()
    {
        return array(
            'null' => array(
                null,
                false,
                ""
            ),
            'Bool true' => array(
                true,
                false,
                date("Y-m-d")
            ),
            'Bool false' => array(
                false,
                false,
                ""
            ),
            'Zend Date' => array(
                new Zend_Date(),
                false,
                date("Y-m-d")
            ),
            'Zend Date including Time' => array(
                new Zend_Date(),
                true,
                date("Y-m-d H:i:s")
            ),
        );
    }
}
