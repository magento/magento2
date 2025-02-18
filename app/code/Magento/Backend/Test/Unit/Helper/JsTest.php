<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Helper;

use Magento\Backend\Helper\Js;
use PHPUnit\Framework\TestCase;

/**
 * Class JsTest
 *
 * Testing decoding serialized grid data
 */
class JsTest extends TestCase
{
    /**
     * @var Js
     */
    private $helper;

    /**
     * Set Up
     */
    protected function setUp(): void
    {
        $this->helper = new Js();
    }

    /**
     * Test decoding the serialized input
     *
     * @dataProvider getEncodedDataProvider
     *
     * @param string $encoded
     * @param array $expected
     */
    public function testDecodeGridSerializedInput(string $encoded, array $expected)
    {
        $this->assertEquals($expected, $this->helper->decodeGridSerializedInput($encoded));
    }

    /**
     * Get serialized grid input
     *
     * @return array
     */
    public static function getEncodedDataProvider(): array
    {
        return [
            'Decoding empty serialized string' => [
                '',
                []
            ],
            'Decoding a simplified serialized string' => [
                '1&2&3&4',
                [1, 2, 3, 4]
            ],
            'Decoding encoded serialized string' => [
                '2=dGVzdC1zdHJpbmc=',
                [
                    2 => [
                        'test-string' => ''
                    ]
                ]
            ],
            'Decoding multiple encoded serialized strings' => [
                '2=dGVzdC1zdHJpbmc=&3=bmV3LXN0cmluZw==',
                [
                    2 => [
                        'test-string' => ''
                    ],
                    3 => [
                        'new-string' => ''
                    ]
                ]
            ]
        ];
    }
}
