<?php
/**
 * Collection of various useful functions
 *
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

/**
 * Test case for \Magento\Framework\Encryption\Security
 */
namespace Magento\Framework\Encryption\Test\Unit\Helper;

use Magento\Framework\Encryption\Helper\Security;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class SecurityTest extends TestCase
{
    /**
     * @var Security
     */
    protected $util;

    /**
     * @param  string $expected
     * @param  string $actual
     * @param  bool $result     */
    #[DataProvider('dataProvider')]
    public function testCompareStrings($expected, $actual, $result)
    {
        $this->assertEquals($result, Security::compareStrings($expected, $actual));
    }

    /**
     * @return array
     */
    public static function dataProvider()
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
