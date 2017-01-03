<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer\Test\Unit;

class BatchTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Indexer\SaveHandler\Batch
     */
    private $object;

    protected function setUp()
    {
        $this->object = new \Magento\Framework\Indexer\SaveHandler\Batch();
    }

    /**
     * @param array $itemsData
     * @param int $size
     * @param array $expected
     *
     * @dataProvider getItemsDataProvider
     */
    public function testGetItems(array $itemsData, $size, array $expected)
    {
        $items = new \ArrayObject($itemsData);
        $data = $this->object->getItems($items, $size);
        $this->assertSame($expected, iterator_to_array($data));
    }

    /**
     * @return array
     */
    public function getItemsDataProvider()
    {
        return [
            'empty' => [
                [],
                2,
                [],
            ],
            'even, numeric keys' => [
                [1, 2, 3, 4],
                2,
                [
                    [0 => 1, 1 => 2],
                    [2 => 3, 3 => 4],
                ],
            ],
            'odd, numeric keys' => [
                [1, 2, 3, 4, 5],
                2,
                [
                    [0 => 1, 1 => 2],
                    [2 => 3, 3 => 4],
                    [4 => 5],
                ],
            ],
            'even, string keys' => [
                ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4],
                2,
                [
                    ['a' => 1, 'b' => 2],
                    ['c' => 3, 'd' => 4],
                ],
            ],
            'odd, string keys' => [
                ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5],
                2,
                [
                    ['a' => 1, 'b' => 2],
                    ['c' => 3, 'd' => 4],
                    ['e' => 5],
                ],
            ],
        ];
    }
}
