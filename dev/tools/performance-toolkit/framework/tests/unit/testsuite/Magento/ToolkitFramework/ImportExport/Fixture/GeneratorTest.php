<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\ToolkitFramework\ImportExport\Fixture;

class GeneratorTest extends \PHPUnit_Framework_TestCase
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
        $model = new \Magento\ToolkitFramework\ImportExport\Fixture\Generator($pattern, 2);
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
