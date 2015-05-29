<?php
/***
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\DB\Test\Unit;


use Magento\Framework\DB\ExpressionConverter;

class ExpressionConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider shortenEntityNameDataProvider
     */
    public function testShortenEntityName($in, $prefix, $expectedOut)
    {
        $this->assertSame($expectedOut, ExpressionConverter::shortenEntityName($in, $prefix));
    }

    public function shortenEntityNameDataProvider()
    {
        /** Length of 64 characters, to go over max MySql identifier length */
        $length64 = '________________________________________________________________';
        $length40 = '________________________________________';
        $length20 = '____________________';
        echo $length20;
        return [
            'Short identifier' => [
                'already_short',
                'pre_',
                'already_short'
            ],
            'Hashed identifer' => [
                $length64 . '_cannotBeAbbreviated',
                'pre_',
                'pre_be55448d703c761bf8a322a9993c9ed3'
            ],
            'Abbreviated identifier' => [
                $length40 . '_enterprise_notification_index',
                'pre_',
                '_________________________________________ent_ntfc_idx'
            ],
            'Reduced hash' => [
                $length64 . '_cannotBeAbbreviated',
                $length40,
                '448d703c761bf8a322a9993c'
            ],
        ];
    }
}
