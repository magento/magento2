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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Stdlib\DateTime\Timezone;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\Timezone\Validator
     */
    protected $_validator;

    /**
     * @dataProvider validateWithTimestampOutOfSystemRangeDataProvider
     * @expectedException \Magento\Framework\Stdlib\DateTime\Timezone\ValidationException
     * @expectedExceptionMessage Transition year is out of system date range.
     */
    public function testValidateWithTimestampOutOfSystemRangeThrowsException($range, $validateArgs)
    {
        $this->_validator = new \Magento\Framework\Stdlib\DateTime\Timezone\Validator($range['min'], $range['max']);
        $this->_validator->validate($validateArgs['timestamp'], $validateArgs['to_date']);
    }

    /**
     * @expectedException \Magento\Framework\Stdlib\DateTime\Timezone\ValidationException
     * @expectedExceptionMessage Transition year is out of specified date range.
     */
    public function testValidateWithTimestampOutOfSpecifiedRangeThrowsException()
    {
        $this->_validator = new \Magento\Framework\Stdlib\DateTime\Timezone\Validator();
        $this->_validator->validate(mktime(1, 2, 3, 4, 5, 2007), mktime(1, 2, 3, 4, 5, 2006));
    }

    /**
     * @return array
     */
    public function validateWithTimestampOutOfSystemRangeDataProvider()
    {
        return array(
            array(array('min' => 2000, 'max' => 2030), array('timestamp' => PHP_INT_MAX, 'to_date' => PHP_INT_MAX)),
            array(array('min' => 2000, 'max' => 2030), array('timestamp' => 0, 'to_date' => PHP_INT_MAX))
        );
    }
}
