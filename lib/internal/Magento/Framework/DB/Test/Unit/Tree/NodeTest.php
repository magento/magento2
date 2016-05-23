<?php
/**
 * \Magento\Framework\DB\Tree\Node test case
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Test\Unit\Tree;

class NodeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array $data
     * @param $expectedException
     * @param $expectedExceptionMessage
     * @dataProvider constructorDataProvider
     */
    public function testConstructorWithInvalidArgumentsThrowsException(
        array $data,
        $expectedException,
        $expectedExceptionMessage
    ) {
        $this->setExpectedException($expectedException, $expectedExceptionMessage);
        new \Magento\Framework\DB\Tree\Node($data['node_data'], $data['keys']);
    }

    /**
     * @param array $data
     * @param string $assertMethod
     * @dataProvider isParentDataProvider
     */
    public function testIsParent(array $data, $assertMethod)
    {
        $model = new \Magento\Framework\DB\Tree\Node($data['node_data'], $data['keys']);
        $this->$assertMethod($model->isParent());
    }

    /**
     * @return array
     */
    public function isParentDataProvider()
    {
        return [
            [
                [
                    'node_data' => [
                        'id' => 'id',
                        'pid' => 'pid',
                        'level' => 'level',
                        'right_key' => 10,
                        'left_key' => 5,
                    ],
                    'keys' => [
                        'id' => 'id',
                        'pid' => 'pid',
                        'level' => 'level',
                        'right' => 'right_key',
                        'left' => 'left_key',
                    ],
                ],
                'assertTrue',
            ],
            [
                [
                    'node_data' => [
                        'id' => 'id',
                        'pid' => 'pid',
                        'level' => 'level',
                        'right_key' => 5,
                        'left_key' => 10,
                    ],
                    'keys' => [
                        'id' => 'id',
                        'pid' => 'pid',
                        'level' => 'level',
                        'right' => 'right_key',
                        'left' => 'left_key',
                    ],
                ],
                'assertFalse'
            ]
        ];
    }

    /**
     * @return array
     */
    public function constructorDataProvider()
    {
        return [
            [
                [
                    'node_data' => null,
                    'keys' => null,
                ],
                '\Magento\Framework\Exception\LocalizedException',
                'Empty array of node information',
            ],
            [
                [
                    'node_data' => null,
                    'keys' => true,
                ],
                '\Magento\Framework\Exception\LocalizedException',
                'Empty array of node information'
            ],
            [
                [
                    'node_data' => true,
                    'keys' => null,
                ],
                '\Magento\Framework\Exception\LocalizedException',
                'Empty keys array'
            ]
        ];
    }
}
