<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\ImportExport\Fixture;

class GeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testIteratorInterface()
    {
        $pattern = [
            'id' => '%s',
            'name' => 'Static',
            'calculated' => function ($index) {
                return $index * 10;
            },
        ];
        $model = new \Magento\TestFramework\ImportExport\Fixture\Generator($pattern, 2);
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
