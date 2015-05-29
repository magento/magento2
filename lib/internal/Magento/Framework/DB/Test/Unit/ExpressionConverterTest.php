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
        $this->assertTrue(
            strpos(ExpressionConverter::shortenEntityName($in, $prefix), $expectedOut) === 0,
            'Entity name did not start with expected prefix'
        );
    }

    public function shortenEntityNameDataProvider()
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
            'Abbreviated identifier' => [
                $length40 . 'enterprise_notification_index',
                'pre_',
                $length40 . 'ent_ntfc_idx'
            ],
        ];
    }

    public function testShortenEntityNameReducedHash()
    {
        /** Length of 64 characters, to go over max MySql identifier length */
        $length64 = '________________________________________________________________';
        $longPrefix = 'pre_____________________________________';
        $shortenedName = ExpressionConverter::shortenEntityName($length64 . '_cannotBeAbbreviated', $longPrefix );
        $this->assertNotSame(0, strpos($shortenedName, 'pre'), 'Entity name not supposed to with long prefix');
    }
}
