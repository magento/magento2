<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\DB\Test\Unit;

use Magento\Framework\DB\ExpressionConverter;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class ExpressionConverterTest extends TestCase
{
    /**     */
    #[DataProvider('shortenEntityNameDataProvider')]
    public function testShortenEntityName($in, $prefix, $expectedOut)
    {
        $resultEntityName = ExpressionConverter::shortenEntityName($in, $prefix);
        $this->assertStringStartsWith($expectedOut, $resultEntityName);
    }

    /**
     * @return array
     */
    public static function shortenEntityNameDataProvider()
    {
        $length64 = '________________________________________________________________';
        $length40 = '________________________________________';
        return [
            'Short identifier' => [
                'already_short',
                'pre_',
                'already_short'
            ],
            'Hashed identifer' => [
                $length64 . '_cannotBeAbbreviated',
                'pre_',
                'pre_'
            ],
            'Hashed identifer with long prefix' => [
                $length64 . '_cannotBeAbbreviated',
                'pre_' . $length40,
                '8d703c761bf8a322a999'
            ],
            'Abbreviated identifier' => [
                $length40 . 'downloadable_notification_index',
                'pre_',
                $length40 . 'dl_ntfc_idx'
            ]
        ];
    }

    public function testShortenEntityNameReducedHash()
    {
        /** Length of 64 characters, to go over max MySql identifier length */
        $length64 = '________________________________________________________________';
        $longPrefix = 'pre_____________________________________';
        $shortenedName = ExpressionConverter::shortenEntityName($length64 . '_cannotBeAbbreviated', $longPrefix);
        $this->assertStringStartsNotWith('pre', $shortenedName);
    }
}
