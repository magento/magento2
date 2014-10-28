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
namespace Magento\Framework\Filter;

class TruncateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $string
     * @param array $args
     * @param string $expected
     * @param string $expectedReminder
     * @dataProvider truncateDataProvider
     */
    public function testTruncate($string, $args, $expected, $expectedReminder)
    {
        list($strLib, $length, $etc, $reminder, $breakWords) = $args;
        $filter = new \Magento\Framework\Filter\Truncate($strLib, $length, $etc, $reminder, $breakWords);
        $this->assertEquals($expected, $filter->filter($string));

        $this->assertEquals($expectedReminder, $reminder);
    }

    /**
     * @return array
     */
    public function truncateDataProvider()
    {
        $remainder = '';
        return array(
            '1' => array(
                '1234567890',
                array(new \Magento\Framework\Stdlib\String(), 5, '...', '', true),
                '12...',
                '34567890'
            ),
            '2' => array(
                '123 456 789',
                array(new \Magento\Framework\Stdlib\String(), 8, '..', $remainder, false),
                '123..',
                ' 456 789'
            )
        );
    }
}
