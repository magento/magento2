<?php
/**
 * Collection of various useful functions
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test case for \Magento\Framework\Encryption\Security
 */
namespace Magento\Framework\Encryption\Test\Unit\Helper;

use Magento\Framework\Encryption\Helper\Security;

class SecurityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Security
     */
    protected $util;

    /**
     * @param  string $expected
     * @param  string $actual
     * @param  bool $result
     * @dataProvider dataProvider
     */
    public function testCompareStrings($expected, $actual, $result)
    {
        $this->assertEquals($result, Security::compareStrings($expected, $actual));
    }

    /**
     * @return array
     */
    public function dataProvider()
    {
        return [
            ['a@fzsd434sdfqw24', 'a@fzsd434sdfqw24', true],
            ['a@fzsd4343432432drfsffe2w24', 'a@fzsd434sdfqw24', false],
            ['0x123', '0x123', true],
            [0x123, 0x123, true],
            ['0x123', '0x11', false],
        ];
    }
}
