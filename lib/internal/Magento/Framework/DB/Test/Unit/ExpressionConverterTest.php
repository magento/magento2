<?php
/***
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\DB\Test\Unit;

use Magento\Framework\DB\ExpressionConverter;
use PHPUnit\Framework\TestCase;

class ExpressionConverterTest extends TestCase
{
    /** Length of 64 characters, to go over max MySql identifier length */
    private const STUB_LENGTH64 = '________________________________________________________________';
    private const STUB_LENGTH40 = '________________________________________';

    /**
     * @dataProvider shortenEntityNameDataProvider
     */
    public function testShortenEntityName($in, $prefix, $expectedOut)
    {
        $resultEntityName = ExpressionConverter::shortenEntityName($in, $prefix);
        $this->assertStringStartsWith($expectedOut, $resultEntityName);
    }

    /**
     * @return array
     */
    public function shortenEntityNameDataProvider()
    {
        return [
            'Short identifier' => [
                'already_short',
                'pre_',
                'pre_already_short'
            ],
            'Hashed identifier' => [
                self::STUB_LENGTH64 . '_cannotBeAbbreviated',
                'pre_',
                'pre_'
            ],
            'Hashed identifier with long prefix' => [
                self::STUB_LENGTH64 . '_cannotBeAbbreviated',
                'pre_' . self::STUB_LENGTH64,
                'be55448d703c761bf8a322a9993c9ed3'
            ],
            'Short hashed identifier with long prefix' => [
                self::STUB_LENGTH64 . '_cannotBeAbbreviated',
                'pre_' . self::STUB_LENGTH40,
                'pre_' . self::STUB_LENGTH40 . 'be55448d'
            ],
            'Abbreviated identifier' => [
                self::STUB_LENGTH40 . 'downloadable_notification_index',
                'pre_',
                'pre_' . self::STUB_LENGTH40 . 'dl_ntfc_idx'
            ]
        ];
    }

    public function testShortenEntityNameReducedHash()
    {
        $longPrefix = substr_replace(self::STUB_LENGTH64, 'pre_', 0, 4);

        $shortenedName = ExpressionConverter::shortenEntityName(
            self::STUB_LENGTH64 . '_cannotBeAbbreviated',
            $longPrefix
        );
        $this->assertStringStartsNotWith('pre_', $shortenedName);
    }
}
