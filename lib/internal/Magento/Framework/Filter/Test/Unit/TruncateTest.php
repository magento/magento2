<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Filter\Test\Unit;

use Magento\Framework\Filter\Truncate;
use Magento\Framework\Stdlib\StringUtils;
use PHPUnit\Framework\TestCase;

class TruncateTest extends TestCase
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
        $filter = new Truncate($strLib, $length, $etc, $reminder, $breakWords);
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
                [new StringUtils(), 5, '...', '', true],
                '12...',
                '34567890',
            ],
            '2' => [
                '123 456 789',
                [new StringUtils(), 8, '..', $remainder, false],
                '123..',
                ' 456 789',
            ]
        ];
    }
}
