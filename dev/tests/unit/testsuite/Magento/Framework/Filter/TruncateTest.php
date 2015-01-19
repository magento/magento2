<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
        return [
            '1' => [
                '1234567890',
                [new \Magento\Framework\Stdlib\String(), 5, '...', '', true],
                '12...',
                '34567890',
            ],
            '2' => [
                '123 456 789',
                [new \Magento\Framework\Stdlib\String(), 8, '..', $remainder, false],
                '123..',
                ' 456 789',
            ]
        ];
    }
}
