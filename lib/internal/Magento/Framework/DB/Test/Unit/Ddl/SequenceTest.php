<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Test\Unit\Ddl;

use Magento\Framework\DB\Ddl\Sequence;
use Magento\Framework\DB\Ddl\Table;

class SequenceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array $params
     * @param string $engine
     * @param string $expectedQuery
     * @dataProvider createSequenceDdlDataProvider
     */
    public function testGetCreateSequenceDdl(array $params, $engine, $expectedQuery)
    {
        $model = new Sequence($engine);
        $actualQuery = call_user_func_array([$model, 'getCreateSequenceDdl'], $params);

        $cleanString = function ($string) {
            return trim(preg_replace('/\s+/', ' ', $string));
        };

        $this->assertEquals(
            $cleanString($expectedQuery),
            $cleanString($actualQuery)
        );
    }

    public function testDropSequence()
    {
        $this->assertEquals(
            'DROP TABLE someTable',
            (new Sequence())->dropSequence('someTable')
        );
    }

    public function createSequenceDdlDataProvider()
    {
        return [
            [
                [
                    'name' => 'someName'
                ],
                null,
                'CREATE TABLE someName (
                     sequence_value integer UNSIGNED NOT NULL AUTO_INCREMENT,
                     PRIMARY KEY (sequence_value)
                ) AUTO_INCREMENT = 1 ENGINE = INNODB'
            ],
            [
                [
                    'name' => 'someName',
                    'startNumber' => 123,
                    'columnType' => Table::TYPE_BIGINT,
                    'unsigned' => false
                ],
                'someEngine',
                'CREATE TABLE someName (
                     sequence_value bigint NOT NULL AUTO_INCREMENT,
                     PRIMARY KEY (sequence_value)
                ) AUTO_INCREMENT = 123 ENGINE = someEngine'
            ]
        ];
    }
}
