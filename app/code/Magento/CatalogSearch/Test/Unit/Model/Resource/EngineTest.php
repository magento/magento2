<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Unit\Model\Resource;


use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class EngineTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogSearch\Model\Resource\Engine
     */
    private $target;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connection;

    protected function setUp()
    {
        $this->connection = $this->getMockBuilder('\Magento\Framework\DB\Adapter\AdapterInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getIfNullSql'])
            ->getMockForAbstractClass();
        $resource = $this->getMockBuilder('\Magento\Framework\App\Resource')
            ->disableOriginalConstructor()
            ->setMethods(['getConnection', 'getTableName'])
            ->getMock();
        $resource->expects($this->any())
            ->method('getConnection')
            ->with(\Magento\Framework\App\Resource::DEFAULT_WRITE_RESOURCE)
            ->will($this->returnValue($this->connection));

        $resource->expects($this->any())
            ->method('getTableName')
            ->will($this->returnArgument(0));

        $objectManager = new ObjectManager($this);
        $this->target = $objectManager->getObject(
            '\Magento\CatalogSearch\Model\Resource\Engine',
            [
                'resource' => $resource,
            ]
        );
    }

    /**
     * @dataProvider saveDataProvider
     */
    public function testSave($storeId, $entityIndexes, $expected)
    {
        $dimension = $this->getMockBuilder('\Magento\Framework\Search\Request\Dimension')
            ->disableOriginalConstructor()
            ->setMethods(['getName', 'getValue'])
            ->getMock();
        $dimension->expects($this->any())->method('getName')->willReturn('scope');
        $dimension->expects($this->any())->method('getValue')->willReturn($storeId);
        if ($expected) {
            $this->connection->expects($this->once())
                ->method('insertOnDuplicate')
                ->with(null, $expected, ['data_index'])
                ->willReturnSelf();
        }
        $this->target->saveIndex([$dimension], $entityIndexes);
    }

    public function saveDataProvider()
    {
        return [
            'empty' => [
                null,
                new \ArrayIterator([]),
                []
            ],
            'correctData' => [
                13,
                new \ArrayIterator([
                    28 => [
                        123 => 'Value of 123',
                        845 => 'Value of 845',
                        'options' => 'Some | Index | Value'
                    ]
                ]),
                [
                    [
                        'product_id' => 28,
                        'attribute_id' => 123,
                        'data_index' => 'Value of 123'
                    ],
                    [
                        'product_id' => 28,
                        'attribute_id' => 845,
                        'data_index' => 'Value of 845'
                    ],
                    [
                        'product_id' => 28,
                        'attribute_id' => 0,
                        'data_index' => 'Some | Index | Value'
                   ]
                ]
            ]
        ];
    }
}
