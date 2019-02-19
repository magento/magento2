<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Model;

use Magento\Setup\Model\Generator;

class GeneratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     *
     * @return void
     */
    public function testIteratorInterface()
    {
        $pattern = [
            'id' => '%s',
            'name' => 'Static',
            'calculated' => function ($index) {
                return $index * 10;
            },
        ];
        $model = new Generator($pattern, 2);
        $rows = [];
        foreach ($model as $row) {
            $rows[] = $row;
        }
        $this->assertEquals(
            [
                ['id' => '1', 'name' => 'Static', 'calculated' => 10],
                ['id' => '2', 'name' => 'Static', 'calculated' => 20],
            ],
            $rows
        );
    }
}
